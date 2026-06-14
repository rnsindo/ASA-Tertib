<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueSession extends Model
{
    protected $fillable = [
        'name',
        'session_date',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QueueSessionQrCode::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(AttendanceCheckin::class);
    }

    public function quotas(): HasMany
    {
        return $this->hasMany(ServiceDailyQuota::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CounterDailyAllocation::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }
}
