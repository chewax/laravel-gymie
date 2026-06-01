<x-filament-panels::page>
    <div class="grid gap-6 max-w-3xl mx-auto w-full">

        {{-- Live occupancy --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">In the gym now</p>
                <p class="text-4xl font-bold text-gray-950 dark:text-white">
                    {{ $this->occupancy }}<span class="text-lg font-normal text-gray-400"> / {{ $this->capacity > 0 ? $this->capacity : '∞' }}</span>
                </p>
            </div>
            <x-filament::icon icon="heroicon-o-users" class="h-12 w-12 text-primary-500" />
        </div>

        {{-- Scan / enter --}}
        <form wire:submit="submit" class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                Scan member QR or enter code / email
            </label>
            <div class="flex gap-3">
                <input id="code" type="text" wire:model="code" autofocus autocomplete="off"
                    class="flex-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm focus:ring-primary-500 focus:border-primary-500"
                    placeholder="e.g. 0001" />
                <x-filament::button type="submit" icon="heroicon-m-arrow-right-circle">
                    Check in / out
                </x-filament::button>
            </div>
        </form>

        {{-- Result banner --}}
        @if ($result)
            <div @class([
                'rounded-xl p-6 text-white shadow-lg',
                'bg-emerald-600' => $result['ok'] && $result['type'] === 'checkin',
                'bg-blue-600' => $result['ok'] && $result['type'] === 'checkout',
                'bg-rose-600' => ! $result['ok'],
            ])>
                <p class="text-2xl font-bold">{{ $result['title'] }}</p>
                <p class="opacity-90 mt-1">{{ $result['detail'] }}</p>
            </div>
        @endif

        {{-- Currently checked in --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-4">Currently checked in</p>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($this->currentlyIn as $attendance)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">{{ $attendance->member?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">
                                #{{ $attendance->member?->code }} · in since {{ $attendance->checked_in_at?->format('H:i') }}
                            </p>
                        </div>
                        <x-filament::button size="sm" color="gray"
                            wire:click="quick('{{ $attendance->member?->code }}')">
                            Check out
                        </x-filament::button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-3">Nobody in the gym right now.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
