<?php

namespace App\Filament\Pages;

use App\Services\CheckinService;
use Filament\Pages\Page;

/**
 * Front-desk / tablet check-in kiosk. Accepts a scanned QR (keyboard-wedge
 * scanners type the code + Enter) or a manually typed member code/email,
 * then validates membership and records check-in / check-out.
 */
class CheckinKiosk extends Page
{
    protected string $view = 'filament.pages.checkin-kiosk';

    public string $code = '';

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public static function getNavigationLabel(): string
    {
        return 'Check-in kiosk';
    }

    public function getTitle(): string
    {
        return 'Check-in';
    }

    /** Manual entry / keyboard-wedge scanner (form submit). */
    public function submit(): void
    {
        $this->process($this->code, 'manual');
    }

    /** Decoded value from the device-camera QR scanner. */
    public function scan(string $code): void
    {
        $this->process($code, 'qr');
    }

    /** Re-run for a given code (used by the "Check out" buttons). */
    public function quick(string $code): void
    {
        $this->process($code, 'manual');
    }

    private function process(string $code, string $method): void
    {
        $result = app(CheckinService::class)->handle($code, $method, auth()->id());

        $this->result = [
            'ok' => $result['ok'],
            'type' => $result['type'],
            'title' => $result['title'],
            'detail' => $result['detail'],
        ];

        $this->code = '';
    }

    public function getCurrentlyInProperty()
    {
        return app(CheckinService::class)->currentlyIn();
    }

    public function getOccupancyProperty(): int
    {
        return app(CheckinService::class)->occupancy();
    }

    public function getCapacityProperty(): int
    {
        return app(CheckinService::class)->capacity();
    }
}
