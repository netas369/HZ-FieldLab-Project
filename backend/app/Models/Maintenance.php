<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'turbine_id',
        'alarm_id',
        'assigned_to',
        'created_by',
        'title',
        'description',
        'priority',
        'status',
        'scheduled_date',
        'due_date',
        'started_at',
        'completed_at',
        'notes',
        'estimated_duration_minutes',
        'actual_duration_minutes',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'due_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function turbine()
    {
        return $this->belongsTo(Turbine::class);
    }

    public function alarm()
    {
        return $this->belongsTo(Alarm::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('status', '!=', 'canceled')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForTurbine($query, int $turbineId)
    {
        return $query->where('turbine_id', $turbineId);
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'canceled']);
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(?string $notes = null): void
    {
        $duration = $this->started_at
            ? $this->started_at->diffInMinutes(now())
            : null;

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_duration_minutes' => $duration,
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
