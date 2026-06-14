<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Applicant extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'school_origin',
        'nisn',
        'whatsapp',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(AttendanceCheckin::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ApplicantServiceRecord::class);
    }
}
