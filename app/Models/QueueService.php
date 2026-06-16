<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueService extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'sort_order',
        'enforce_call_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enforce_call_order' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function counters(): HasMany
    {
        return $this->hasMany(ServiceCounter::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(QueueServiceDependency::class);
    }

    public function dependentServices(): HasMany
    {
        return $this->hasMany(QueueServiceDependency::class, 'required_queue_service_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function dailyQuotas(): HasMany
    {
        return $this->hasMany(ServiceDailyQuota::class);
    }

    public function dailyAllocations(): HasMany
    {
        return $this->hasMany(CounterDailyAllocation::class);
    }

    public function applicantRecords(): HasMany
    {
        return $this->hasMany(ApplicantServiceRecord::class);
    }
}
