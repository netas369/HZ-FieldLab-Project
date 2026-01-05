<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Alarm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    /**
     * List all maintenance tasks with filters
     * GET /maintenances
     */
    public function index(Request $request)
    {
        $query = Maintenance::with(['turbine', 'alarm', 'assignee', 'creator']);

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('turbine_id')) {
            $query->where('turbine_id', $request->turbine_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Include overdue check
        if ($request->boolean('include_overdue')) {
            $query->get()->each(function ($maintenance) {
                if ($maintenance->isOverdue() && $maintenance->status !== 'overdue') {
                    $maintenance->update(['status' => 'overdue']);
                }
            });
        }

        $maintenances = $query->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($maintenances);
    }

    /**
     * Get a single maintenance task
     * GET /maintenances/{id}
     */
    public function show(int $id)
    {
        $maintenance = Maintenance::with(['turbine', 'alarm', 'assignee', 'creator'])
            ->findOrFail($id);

        return response()->json($maintenance);
    }

    /**
     * Create a new maintenance task
     * POST /maintenances
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'turbine_id' => ['required', 'exists:turbines,id'],
            'alarm_id' => ['nullable', 'exists:alarms,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['nullable', Rule::in(['scheduled', 'in_progress', 'completed', 'canceled', 'overdue'])],
            'scheduled_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $maintenance = Maintenance::create($validated);

        // If created from an alarm, update the alarm status
        if ($maintenance->alarm_id) {
            Alarm::where('id', $maintenance->alarm_id)->update([
                'status' => 'acknowledged',
                'acknowledged_at' => now(),
                'acknowledged_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'message' => 'Maintenance task created successfully',
            'maintenance' => $maintenance->load(['turbine', 'alarm', 'assignee', 'creator']),
        ], 201);
    }

    /**
     * Update a maintenance task
     * PUT /maintenances/{id}
     */
    public function update(Request $request, int $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        $validated = $request->validate([
            'turbine_id' => ['sometimes', 'exists:turbines,id'],
            'alarm_id' => ['nullable', 'exists:alarms,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'status' => ['nullable', Rule::in(['scheduled', 'in_progress', 'completed', 'canceled', 'overdue'])],
            'scheduled_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'actual_duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        // Auto-set timestamps based on status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && !$maintenance->started_at) {
                $validated['started_at'] = now();
            }
            if ($validated['status'] === 'completed' && !$maintenance->completed_at) {
                $validated['completed_at'] = now();
                // Calculate actual duration if started
                if ($maintenance->started_at && !isset($validated['actual_duration_minutes'])) {
                    $validated['actual_duration_minutes'] = $maintenance->started_at->diffInMinutes(now());
                }
            }
        }

        $maintenance->update($validated);

        // If completed and linked to alarm, resolve the alarm
        if ($maintenance->status === 'completed' && $maintenance->alarm_id) {
            Alarm::where('id', $maintenance->alarm_id)->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
                'resolution_notes' => "Resolved via maintenance task #{$maintenance->id}",
            ]);
        }

        return response()->json([
            'message' => 'Maintenance task updated successfully',
            'maintenance' => $maintenance->load(['turbine', 'alarm', 'assignee', 'creator']),
        ]);
    }

    /**
     * Delete a maintenance task
     * DELETE /maintenances/{id}
     */
    public function destroy(int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance task deleted successfully',
        ]);
    }

    /**
     * Get maintenance tasks for a specific turbine
     * GET /turbines/{turbineId}/maintenances
     */
    public function forTurbine(Request $request, int $turbineId)
    {
        $query = Maintenance::with(['alarm', 'assignee', 'creator'])
            ->where('turbine_id', $turbineId);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->orderBy('due_date', 'asc')->get();

        return response()->json([
            'turbine_id' => $turbineId,
            'total' => $maintenances->count(),
            'maintenances' => $maintenances,
        ]);
    }

    /**
     * Create maintenance from an alarm
     * POST /alarms/{alarmId}/maintenance
     */
    public function createFromAlarm(Request $request, int $alarmId)
    {
        $alarm = Alarm::with('turbine')->findOrFail($alarmId);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'scheduled_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        // Auto-generate title from alarm if not provided
        $title = $validated['title'] ?? "Maintenance for: {$alarm->message}";

        // Map alarm severity to priority
        $priority = $validated['priority'] ?? match($alarm->severity) {
            'failed' => 'urgent',
            'critical' => 'high',
            'warning' => 'medium',
            default => 'low',
        };

        $maintenance = Maintenance::create([
            'turbine_id' => $alarm->turbine_id,
            'alarm_id' => $alarm->id,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'title' => $title,
            'description' => $validated['description'] ?? "Created from alarm: {$alarm->component} - {$alarm->alarm_type}",
            'priority' => $priority,
            'status' => 'scheduled',
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'] ?? null,
        ]);

        // Acknowledge the alarm
        $alarm->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Maintenance task created from alarm',
            'maintenance' => $maintenance->load(['turbine', 'alarm', 'assignee', 'creator']),
        ], 201);
    }

    /**
     * Get my assigned maintenance tasks
     * GET /maintenances/my-tasks
     */
    public function myTasks(Request $request)
    {
        $maintenances = Maintenance::with(['turbine', 'alarm', 'creator'])
            ->where('assigned_to', $request->user()->id)
            ->whereNotIn('status', ['completed', 'canceled'])
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'total' => $maintenances->count(),
            'maintenances' => $maintenances,
        ]);
    }
}
