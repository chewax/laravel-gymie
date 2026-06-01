@php($url = route('wallet.card', $member->wallet_token))
<div class="flex flex-col items-center gap-4 py-4">
    <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-xs">
        {{ __('app.wallet.enroll_help') }}
    </p>

    <div class="bg-white p-4 rounded-lg ring-1 ring-gray-200">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(220)->margin(1)->generate($url) !!}
    </div>

    <a href="{{ $url }}" target="_blank" rel="noopener"
       class="text-xs text-primary-600 dark:text-primary-400 underline break-all text-center max-w-xs">
        {{ $url }}
    </a>

    <p class="text-xs text-gray-400 text-center max-w-xs">
        {{ __('app.wallet.enroll_or_link') }}
    </p>
</div>
