<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'member_id',
        'subscription_id',
        'checked_in_at',
        'checked_out_at',
        'method',
        'recorded_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Visits that have not been checked out yet. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('checked_out_at');
    }

    /** Visits whose check-in happened today. */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('checked_in_at', today());
    }
}
