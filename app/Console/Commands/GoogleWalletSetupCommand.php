<?php

namespace App\Console\Commands;

use App\Services\Wallet\GoogleWalletService;
use Illuminate\Console\Command;

class GoogleWalletSetupCommand extends Command
{
    protected $signature = 'wallet:google-setup';

    protected $description = 'Create or update the Google Wallet membership class (run once after configuring credentials)';

    public function handle(GoogleWalletService $google): int
    {
        if (! config('wallet.google.enabled')) {
            $this->error('Google Wallet is disabled. Set WALLET_GOOGLE_ENABLED=true and configure credentials first.');

            return self::FAILURE;
        }

        $this->info('Google Wallet class: '.$google->upsertClass());

        return self::SUCCESS;
    }
}
