<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    use HasFactory;

    protected $fillable = [
        'turbine_id',
        'alarm_type',
        'component',
        'severity',
        'status',
        'message',
        'data',
        'detected_at',
        'acknowledged_at',
        'resolved_at',
        'resolution_notes'
    ];

    protected $casts = [
        'data' => 'array',
        'detected_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function turbine()
    {
        return $this->belongsTo(Turbine::class);
    }

    /**
     * Scope for active alarms
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific severity
     */
    public function scopeSeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
}
