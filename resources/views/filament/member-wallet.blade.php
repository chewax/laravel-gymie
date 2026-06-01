@php($wallet = app(\App\Services\Wallet\WalletService::class))
<div class="flex flex-col items-center gap-4 py-4">
    <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-xs">
        Issue a digital membership card. The card carries the member's QR code
        ({{ $member->code }}), so it works at the check-in kiosk.
    </p>

    <div class="flex flex-col gap-3 w-full max-w-xs">
        @if ($wallet->appleEnabled())
            <a href="{{ route('wallet.apple', $member) }}"
               class="flex items-center justify-center gap-2 rounded-lg bg-black text-white px-4 py-3 font-medium hover:opacity-90">
                <x-filament::icon icon="heroicon-m-device-phone-mobile" class="h-5 w-5" />
                Add to Apple Wallet
            </a>
        @endif

        @if ($wallet->googleEnabled())
            <a href="{{ route('wallet.google', $member) }}" target="_blank" rel="noopener"
               class="flex items-center justify-center gap-2 rounded-lg bg-[#4285F4] text-white px-4 py-3 font-medium hover:opacity-90">
                <x-filament::icon icon="heroicon-m-wallet" class="h-5 w-5" />
                Add to Google Wallet
            </a>
        @endif
    </div>

    <p class="text-xs text-gray-400 text-center max-w-xs">
        Tip: open this on the member's phone (or email them the link) so they can add the card directly.
    </p>
</div>
