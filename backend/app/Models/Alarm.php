<?php

namespace App\Models;

use App\Constants\AlarmCodes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    use HasFactory;

    protected $fillable = [
        'turbine_id',
        'alarm_type',
        'component',
        'alarm_code',
        'severity',
        'status',
        'message',
        'data',
        'detected_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
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

    // ✅ ADD THIS METHOD
    public function getAlarmDetails()
    {
        if ($this->alarm_code) {
            return AlarmCodes::getAlarmDetails($this->alarm_code);
        }
        return null;
    }

    // ✅ ADD THIS METHOD
    public function getStandardReference()
    {
        $details = $this->getAlarmDetails();
        return $details['standard'] ?? null;
    }
}
