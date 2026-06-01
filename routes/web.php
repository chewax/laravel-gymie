<?php

use App\Http\Controllers\InvoiceDocumentController;
use App\Http\Controllers\WalletController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::middleware([Authenticate::class])
    ->group(function (): void {
        Route::get('/invoices/{invoice}/preview', [InvoiceDocumentController::class, 'preview'])
            ->name('invoices.preview');

        Route::get('/invoices/{invoice}/download', [InvoiceDocumentController::class, 'download'])
            ->name('invoices.download');

        // Wallet membership cards (Apple .pkpass download / Google save link)
        Route::get('/wallet/apple/{member}', [WalletController::class, 'apple'])
            ->name('wallet.apple');

        Route::get('/wallet/google/{member}', [WalletController::class, 'google'])
            ->name('wallet.google');
    });
