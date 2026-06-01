<?php

namespace App\Filament\Pages;

use App\Services\CheckinService;
use BackedEnum;
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

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationLabel(): string
    {
        return 'Check-in kiosk';
    }

    public function getTitle(): string
    {
        return 'Check-in';
    }

    public function submit(): void
    {
        $result = app(CheckinService::class)->handle($this->code, 'manual', auth()->id());

        $this->result = [
            'ok' => $result['ok'],
            'type' => $result['type'],
            'title' => $result['title'],
            'detail' => $result['detail'],
        ];

        $this->code = '';
    }

    /** Re-run a scan for a given code (used by the "Check out" buttons). */
    public function quick(string $code): void
    {
        $this->code = $code;
        $this->submit();
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
