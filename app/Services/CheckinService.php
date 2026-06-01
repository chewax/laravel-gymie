<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Subscription;

/**
 * Gym access-control logic: resolve a member from a scanned/typed credential,
 * validate their right to enter, and record check-in / check-out + occupancy.
 */
class CheckinService
{
    /** Max simultaneous members allowed (0 = unlimited). */
    public function capacity(): int
    {
        return (int) config('access.capacity', 0);
    }

    /** Members currently inside (checked in today, not yet checked out). */
    public function currentlyIn()
    {
        return Attendance::query()->open()->today()
            ->with('member')
            ->latest('checked_in_at')
            ->get();
    }

    public function occupancy(): int
    {
        return Attendance::query()->open()->today()->count();
    }

    /** Find a member by their code, email or contact number. */
    public function resolveMember(string $credential): ?Member
    {
        $credential = trim($credential);

        if ($credential === '') {
            return null;
        }

        return Member::query()
            ->where('code', $credential)
            ->orWhere('email', $credential)
            ->orWhere('contact', $credential)
            ->first();
    }

    /** The subscription (if any) that currently grants this member access. */
    public function activeSubscription(Member $member): ?Subscription
    {
        $today = today();

        return $member->subscriptions()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', ['expired', 'renewed'])
            ->latest('end_date')
            ->first();
    }

    /**
     * Process a scan. Returns a result array:
     * ['ok' => bool, 'type' => 'checkin|checkout|deny', 'title' => string,
     *  'detail' => string, 'member' => Member|null].
     */
    public function handle(string $credential, string $method = 'manual', ?int $userId = null): array
    {
        $member = $this->resolveMember($credential);

        if (! $member) {
            return $this->deny(__('app.access.result.no_member', ['credential' => trim($credential)]));
        }

        // An open visit today means this scan is a check-OUT.
        $open = Attendance::query()->open()->today()
            ->where('member_id', $member->id)
            ->first();

        if ($open) {
            $open->update(['checked_out_at' => now()]);

            return [
                'ok' => true,
                'type' => 'checkout',
                'title' => __('app.access.result.goodbye', ['name' => $member->name]),
                'detail' => __('app.access.result.checked_out_at', ['time' => now()->format('H:i')]),
                'member' => $member,
            ];
        }

        $status = $member->status instanceof \BackedEnum ? $member->status->value : $member->status;
        if ($status === 'inactive') {
            return $this->deny(__('app.access.result.inactive'), $member);
        }

        $subscription = $this->activeSubscription($member);
        if (! $subscription) {
            return $this->deny(__('app.access.result.no_subscription'), $member);
        }

        $capacity = $this->capacity();
        if ($capacity > 0 && $this->occupancy() >= $capacity) {
            return $this->deny(__('app.access.result.at_capacity', ['capacity' => $capacity]), $member);
        }

        Attendance::create([
            'member_id' => $member->id,
            'subscription_id' => $subscription->id,
            'checked_in_at' => now(),
            'method' => $method,
            'recorded_by' => $userId,
        ]);

        return [
            'ok' => true,
            'type' => 'checkin',
            'title' => __('app.access.result.welcome', ['name' => $member->name]),
            'detail' => __('app.access.result.valid_until', ['date' => optional($subscription->end_date)->format('d M Y')]),
            'member' => $member,
        ];
    }

    private function deny(string $detail, ?Member $member = null): array
    {
        return [
            'ok' => false,
            'type' => 'deny',
            'title' => __('app.access.result.denied'),
            'detail' => $detail,
            'member' => $member,
        ];
    }
}
