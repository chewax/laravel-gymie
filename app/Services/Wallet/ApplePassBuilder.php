<?php

namespace App\Services\Wallet;

use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Builds and signs an Apple Wallet pass (.pkpass) for a gym membership.
 *
 * A .pkpass is a ZIP containing pass.json + images + manifest.json (SHA-1 of
 * every file) + a detached PKCS#7 signature of the manifest, signed with the
 * Pass Type ID certificate and chained to Apple's WWDR certificate.
 */
class ApplePassBuilder
{
    /**
     * @param  array{member_id:int, code:string, name:string, plan:string, valid_until:string}  $card
     * @return string  Raw .pkpass bytes.
     */
    public function build(array $card): string
    {
        $config = config('wallet.apple');

        $certPath = storage_path('app/'.$config['certificate']);
        $wwdrPath = storage_path('app/'.$config['wwdr']);

        if (! is_file($certPath) || ! is_file($wwdrPath)) {
            throw new RuntimeException('Apple Wallet certificate or WWDR file is missing. See docs/WALLET.md.');
        }

        $work = $this->tempDir();

        try {
            // 1. pass.json
            file_put_contents($work.'/pass.json', json_encode($this->passDefinition($card, $config), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            // 2. required images (generated so no asset files are needed)
            $this->writeIcon($work.'/icon.png', 29);
            $this->writeIcon($work.'/icon@2x.png', 58);
            $this->writeIcon($work.'/logo.png', 50);
            $this->writeIcon($work.'/logo@2x.png', 100);

            // 3. manifest.json (sha1 of each file)
            $manifest = [];
            foreach (glob($work.'/*') as $file) {
                $manifest[basename($file)] = sha1_file($file);
            }
            file_put_contents($work.'/manifest.json', json_encode($manifest, JSON_UNESCAPED_SLASHES));

            // 4. signature (detached PKCS#7, DER)
            $this->sign($work.'/manifest.json', $work.'/signature', $certPath, (string) $config['certificate_password'], $wwdrPath);

            // 5. zip everything into the .pkpass
            return $this->zip($work);
        } finally {
            $this->rmrf($work);
        }
    }

    /** @return array<string, mixed> */
    private function passDefinition(array $card, array $config): array
    {
        return [
            'formatVersion' => 1,
            'passTypeIdentifier' => $config['pass_type_id'],
            'teamIdentifier' => $config['team_id'],
            'organizationName' => $config['organization'],
            'description' => $config['description'],
            'serialNumber' => 'member-'.$card['member_id'],
            'logoText' => $config['organization'],
            'backgroundColor' => $config['background_color'],
            'foregroundColor' => $config['foreground_color'],
            'labelColor' => $config['label_color'],
            'barcodes' => [[
                'format' => 'PKBarcodeFormatQR',
                'message' => $card['code'],
                'messageEncoding' => 'iso-8859-1',
                'altText' => $card['code'],
            ]],
            'storeCard' => [
                'primaryFields' => [
                    ['key' => 'name', 'label' => 'MEMBER', 'value' => $card['name']],
                ],
                'secondaryFields' => [
                    ['key' => 'plan', 'label' => 'PLAN', 'value' => $card['plan']],
                    ['key' => 'valid', 'label' => 'VALID UNTIL', 'value' => $card['valid_until']],
                ],
                'auxiliaryFields' => [
                    ['key' => 'member', 'label' => 'MEMBER #', 'value' => $card['code']],
                ],
            ],
        ];
    }

    private function sign(string $manifest, string $output, string $p12, string $password, string $wwdr): void
    {
        if (! openssl_pkcs12_read((string) file_get_contents($p12), $certs, $password)) {
            throw new RuntimeException('Could not read the Apple .p12 certificate (wrong password?).');
        }

        $certFile = tempnam(sys_get_temp_dir(), 'cert');
        $keyFile = tempnam(sys_get_temp_dir(), 'key');
        file_put_contents($certFile, $certs['cert']);
        file_put_contents($keyFile, $certs['pkey']);

        try {
            $process = new Process([
                'openssl', 'smime', '-binary', '-sign',
                '-certfile', $wwdr,
                '-signer', $certFile,
                '-inkey', $keyFile,
                '-in', $manifest,
                '-out', $output,
                '-outform', 'DER',
            ]);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($output)) {
                throw new RuntimeException('Failed to sign the pass: '.$process->getErrorOutput());
            }
        } finally {
            @unlink($certFile);
            @unlink($keyFile);
        }
    }

    private function zip(string $dir): string
    {
        $path = $dir.'/pass.pkpass';
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the .pkpass archive.');
        }

        foreach (glob($dir.'/*') as $file) {
            if (basename($file) !== 'pass.pkpass') {
                $zip->addFile($file, basename($file));
            }
        }
        $zip->close();

        $bytes = (string) file_get_contents($path);
        @unlink($path);

        return $bytes;
    }

    private function writeIcon(string $path, int $size): void
    {
        // Solid brand-coloured square — satisfies Apple's required image set.
        $img = imagecreatetruecolor($size, $size);
        $color = imagecolorallocate($img, 21, 117, 115); // matches the panel primary
        imagefilledrectangle($img, 0, 0, $size, $size, $color);
        imagepng($img, $path);
        imagedestroy($img);
    }

    private function tempDir(): string
    {
        $dir = sys_get_temp_dir().'/pkpass_'.bin2hex(random_bytes(6));
        mkdir($dir, 0700, true);

        return $dir;
    }

    private function rmrf(string $dir): void
    {
        foreach (glob($dir.'/*') ?: [] as $file) {
            is_dir($file) ? $this->rmrf($file) : @unlink($file);
        }
        @rmdir($dir);
    }
}
