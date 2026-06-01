<?php

namespace App\Services\Wallet;

use App\Models\Member;
use App\Services\CheckinService;

/**
 * Entry point for generating Apple / Google Wallet membership cards.
 * Each card carries the member's `code` as a QR barcode — the same
 * credential the check-in kiosk validates.
 */
class WalletService
{
    public function __construct(
        private ApplePassBuilder $apple,
        private GoogleWalletService $google,
    ) {}

    public function appleEnabled(): bool
    {
        return (bool) config('wallet.apple.enabled');
    }

    public function googleEnabled(): bool
    {
        return (bool) config('wallet.google.enabled');
    }

    /** Raw .pkpass bytes for a member (Apple Wallet). */
    public function applePass(Member $member): string
    {
        return $this->apple->build($this->cardData($member));
    }

    /** "Add to Google Wallet" save URL for a member. */
    public function googleSaveUrl(Member $member): string
    {
        return $this->google->saveUrl($this->cardData($member));
    }

    /**
     * Normalised card fields shared by both wallet drivers.
     *
     * @return array{member_id:int, code:string, name:string, plan:string, valid_until:string}
     */
    public function cardData(Member $member): array
    {
        $subscription = app(CheckinService::class)->activeSubscription($member)
            ?? $member->subscriptions()->latest('end_date')->first();

        return [
            'member_id' => $member->id,
            'code' => (string) $member->code,
            'name' => (string) $member->name,
            'plan' => $subscription?->plan?->name ?? '—',
            'valid_until' => optional($subscription?->end_date)->format('d M Y') ?? '—',
        ];
    }
}
