<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alarm;
use App\Services\AlarmService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlarmController extends Controller
{
    protected AlarmService $alarmService;

    public function __construct(AlarmService $alarmService)
    {
        $this->alarmService = $alarmService;
    }

    /**
     * Update the status of an alarm
     *
     * PATCH /turbines/{turbineId}/alarms/{alarmId}
     */
    public function updateStatus(Request $request, int $turbineId, int $alarmId)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'acknowledged', 'resolved'])],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $alarm = Alarm::where('id', $alarmId)
            ->where('turbine_id', $turbineId)
            ->firstOrFail();

        $userId = $request->user()->id;
        $newStatus = $validated['status'];

        if ($newStatus === 'acknowledged' && $alarm->status === 'active') {
            $alarm = $this->alarmService->acknowledgeAlarm($alarmId, $userId);
        } elseif ($newStatus === 'resolved') {
            $alarm = $this->alarmService->resolveAlarm(
                $alarmId,
                $userId,
                $validated['resolution_notes'] ?? null
            );
        } elseif ($newStatus === 'active' && $alarm->status !== 'active') {
            // Reactivate an acknowledged/resolved alarm
            $alarm->update([
                'status' => 'active',
                'acknowledged_at' => null,
                'acknowledged_by' => null,
                'resolved_at' => null,
                'resolved_by' => null,
                'resolution_notes' => null,
            ]);
        }

        return response()->json([
            'message' => 'Alarm status updated successfully',
            'alarm' => [
                'id' => $alarm->id,
                'turbine_id' => $alarm->turbine_id,
                'alarm_code' => $alarm->alarm_code,
                'alarm_type' => $alarm->alarm_type,
                'component' => $alarm->component,
                'severity' => $alarm->severity,
                'status' => $alarm->status,
                'message' => $alarm->message,
                'detected_at' => $alarm->detected_at,
                'acknowledged_at' => $alarm->acknowledged_at,
                'resolved_at' => $alarm->resolved_at,
                'resolution_notes' => $alarm->resolution_notes,
            ],
        ]);
    }
}
