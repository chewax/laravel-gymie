<?php

namespace App\Services\Wallet;

use Firebase\JWT\JWT;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google Wallet membership cards via the "Add to Google Wallet" JWT flow.
 *
 * The Generic *class* is created once (see GoogleWalletSetupCommand); each
 * member gets a Generic *object* embedded in a signed save-JWT. Google creates
 * the object when the user taps the button.
 */
class GoogleWalletService
{
    private const SAVE_URL = 'https://pay.google.com/gp/v/save/';

    private const API = 'https://walletobjects.googleapis.com/walletobjects/v1';

    private const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    /** Signed "Add to Google Wallet" link for a member. */
    public function saveUrl(array $card): string
    {
        $config = config('wallet.google');
        $sa = $this->serviceAccount();

        $object = $this->object($card, $config);

        $claims = [
            'iss' => $sa['client_email'],
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'origins' => array_filter([$config['origin']]),
            'payload' => [
                'genericObjects' => [$object],
            ],
        ];

        $jwt = JWT::encode($claims, $sa['private_key'], 'RS256');

        return self::SAVE_URL.$jwt;
    }

    /** Create or update the Generic class (run once via artisan). */
    public function upsertClass(): string
    {
        $config = config('wallet.google');
        $classId = $this->classId($config);

        $class = [
            'id' => $classId,
            'issuerName' => $config['program_name'],
            'reviewStatus' => 'UNDER_REVIEW',
        ];

        $http = Http::withToken($this->accessToken())->acceptJson();

        if ($http->get(self::API.'/genericClass/'.$classId)->successful()) {
            $http->put(self::API.'/genericClass/'.$classId, $class);

            return "updated {$classId}";
        }

        $create = $http->post(self::API.'/genericClass', $class);

        if ($create->failed()) {
            throw new RuntimeException('Failed to create class: '.$create->body());
        }

        return "created {$classId}";
    }

    /** @return array<string, mixed> */
    private function object(array $card, array $config): array
    {
        $objectId = $config['issuer_id'].'.member-'.$card['member_id'];

        return [
            'id' => $objectId,
            'classId' => $this->classId($config),
            'state' => 'ACTIVE',
            'hexBackgroundColor' => $config['background_color'],
            'cardTitle' => $this->localized($config['program_name']),
            'header' => $this->localized($card['name']),
            'barcode' => [
                'type' => 'QR_CODE',
                'value' => $card['code'],
                'alternateText' => $card['code'],
            ],
            'textModulesData' => [
                ['id' => 'plan', 'header' => 'Plan', 'body' => $card['plan']],
                ['id' => 'valid', 'header' => 'Valid until', 'body' => $card['valid_until']],
                ['id' => 'member', 'header' => 'Member #', 'body' => $card['code']],
            ],
        ];
    }

    /** @return array{value: array{language: string, value: string}} */
    private function localized(string $value): array
    {
        return ['defaultValue' => ['language' => 'en', 'value' => $value]];
    }

    private function classId(array $config): string
    {
        return $config['issuer_id'].'.'.$config['class_suffix'];
    }

    private function accessToken(): string
    {
        $credentials = new ServiceAccountCredentials(self::SCOPE, $this->serviceAccountPath());
        $token = $credentials->fetchAuthToken();

        return $token['access_token'] ?? throw new RuntimeException('Could not obtain a Google access token.');
    }

    /** @return array{client_email: string, private_key: string} */
    private function serviceAccount(): array
    {
        $data = json_decode((string) file_get_contents($this->serviceAccountPath()), true);

        if (! isset($data['client_email'], $data['private_key'])) {
            throw new RuntimeException('Invalid Google service-account JSON. See docs/WALLET.md.');
        }

        return $data;
    }

    private function serviceAccountPath(): string
    {
        $path = storage_path('app/'.config('wallet.google.service_account'));

        if (! is_file($path)) {
            throw new RuntimeException('Google service-account key is missing. See docs/WALLET.md.');
        }

        return $path;
    }
}
