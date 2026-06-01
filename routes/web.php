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
    });

// Public, token-based wallet enrollment (member scans their personal QR).
Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/wallet/card/{token}', [WalletController::class, 'landing'])
        ->name('wallet.card');

    Route::get('/wallet/card/{token}/apple', [WalletController::class, 'apple'])
        ->name('wallet.card.apple');

    Route::get('/wallet/card/{token}/google', [WalletController::class, 'google'])
        ->name('wallet.card.google');
});
