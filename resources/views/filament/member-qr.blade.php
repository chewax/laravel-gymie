<div class="flex flex-col items-center gap-4 py-4">
    <div class="bg-white p-4 rounded-lg ring-1 ring-gray-200">
        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(220)->margin(1)->generate($member->code) !!}
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">Member check-in code</p>
    <p class="text-2xl font-bold tracking-[0.3em] text-gray-950 dark:text-white">{{ $member->code }}</p>
    <p class="text-xs text-gray-400 text-center max-w-xs">
        Show this at the front desk, or print it for a membership card. The kiosk also accepts the code typed manually.
    </p>
</div>
