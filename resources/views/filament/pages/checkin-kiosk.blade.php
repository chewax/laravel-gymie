<x-filament-panels::page>
    <div class="grid gap-6 max-w-3xl mx-auto w-full">

        {{-- Live occupancy --}}
        <div class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.access.kiosk.occupancy') }}</p>
                <p class="text-4xl font-bold text-gray-950 dark:text-white">
                    {{ $this->occupancy }}<span class="text-lg font-normal text-gray-400"> / {{ $this->capacity > 0 ? $this->capacity : '∞' }}</span>
                </p>
            </div>
            <x-filament::icon icon="heroicon-o-users" class="h-12 w-12 text-primary-500" />
        </div>

        {{-- Scan / enter --}}
        <form wire:submit="submit" class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                {{ __('app.access.kiosk.scan_label') }}
            </label>
            <div class="flex gap-3">
                <input id="code" type="text" wire:model="code" autofocus autocomplete="off"
                    class="flex-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-white shadow-sm focus:ring-primary-500 focus:border-primary-500"
                    placeholder="{{ __('app.access.kiosk.placeholder') }}" />
                <x-filament::button type="submit" icon="heroicon-m-arrow-right-circle">
                    {{ __('app.access.kiosk.check_in_out') }}
                </x-filament::button>
            </div>
        </form>

        {{-- Camera QR scanner (loads html5-qrcode on demand) --}}
        <div
            x-data="{
                scanning: false,
                reader: null,
                error: null,
                handled: false,
                async toggle() { this.scanning ? await this.stop() : await this.start(); },
                async start() {
                    this.error = null;
                    this.handled = false;
                    try {
                        if (! window.Html5Qrcode) { await this.load(); }
                        this.reader = new window.Html5Qrcode('qr-reader');
                        this.scanning = true;
                        await this.reader.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: { width: 240, height: 240 } },
                            (text) => this.onScan(text),
                            () => {}
                        );
                    } catch (e) {
                        this.scanning = false;
                        this.error = @js(__('app.access.kiosk.camera_error'));
                    }
                },
                onScan(text) {
                    if (this.handled) { return; }
                    this.handled = true;
                    this.stop();
                    @this.scan(text);
                },
                async stop() {
                    if (this.reader && this.scanning) {
                        this.scanning = false;
                        try { await this.reader.stop(); this.reader.clear(); } catch (e) {}
                    }
                },
                load() {
                    return new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
                        s.onload = resolve;
                        s.onerror = () => reject(new Error('load failed'));
                        document.head.appendChild(s);
                    });
                }
            }"
            x-on:livewire:navigating.window="stop()"
            class="rounded-xl bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 p-6"
        >
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('app.access.kiosk.camera_scan') }}</p>
                    <p class="text-xs text-gray-500">{{ __('app.access.kiosk.camera_hint') }}</p>
                </div>
                <x-filament::button color="primary" icon="heroicon-m-camera" x-on:click="toggle()">
                    <span x-text="scanning ? @js(__('app.access.kiosk.stop_camera')) : @js(__('app.access.kiosk.scan_with_camera'))"></span>
                </x-filament::button>
            </div>
            <p x-show="error" x-text="error" style="display:none" class="text-sm text-rose-600 mt-3"></p>
            <div id="qr-reader" x-show="scanning" style="display:none" class="mt-4 max-w-sm mx-auto overflow-hidden rounded-lg"></div>
        </div>

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
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-4">{{ __('app.access.kiosk.currently_in') }}</p>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($this->currentlyIn as $attendance)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="font-medium text-gray-950 dark:text-white">{{ $attendance->member?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">
                                #{{ $attendance->member?->code }} · {{ __('app.access.kiosk.in_since', ['time' => $attendance->checked_in_at?->format('H:i')]) }}
                            </p>
                        </div>
                        <x-filament::button size="sm" color="gray"
                            wire:click="quick('{{ $attendance->member?->code }}')">
                            {{ __('app.access.kiosk.check_out') }}
                        </x-filament::button>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-3">{{ __('app.access.kiosk.nobody') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
