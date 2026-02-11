<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Threshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

class ThresholdController extends Controller
{
    /**
     * Get all thresholds
     */
    public function index()
    {
        try {
            $thresholds = Threshold::orderBy('component_name')->get();

            return response()->json([
                'success' => true,
                'thresholds' => $thresholds,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single threshold
     */
    public function show($id)
    {
        try {
            $threshold = Threshold::findOrFail($id);

            return response()->json([
                'success' => true,
                'threshold' => $threshold,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a threshold
     */
    public function update(Request $request, $id)
    {
        try {
            $threshold = Threshold::findOrFail($id);

            $validated = $request->validate([
                'normal_max' => 'nullable|numeric',
                'normal_min' => 'nullable|numeric',
                'warning_max' => 'nullable|numeric',
                'warning_min' => 'nullable|numeric',
                'critical_max' => 'nullable|numeric',
                'critical_min' => 'nullable|numeric',
                'failed_max' => 'nullable|numeric',
                'failed_min' => 'nullable|numeric',
            ]);

            $threshold->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Threshold updated successfully',
                'threshold' => $threshold->fresh(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset threshold to default values
     */
    public function reset($id)
    {
        try {
            // Re-run seeder
            Artisan::call('db:seed', ['--class' => 'ThresholdSeeder', '--force' => true]);

            $threshold = Threshold::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Threshold reset to default values',
                'threshold' => $threshold,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test a value against a threshold
     */
    public function testValue(Request $request, $id)
    {
        try {
            $threshold = Threshold::findOrFail($id);

            $validated = $request->validate([
                'value' => 'required|numeric',
            ]);

            $value = $validated['value'];
            $status = $threshold->getStatus($value);

            return response()->json([
                'success' => true,
                'value' => $value,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get thresholds by type
     */
    public function byType($type)
    {
        try {
            $thresholds = Threshold::where('component_name', 'LIKE', "%{$type}%")->get();

            return response()->json([
                'success' => true,
                'type' => $type,
                'thresholds' => $thresholds,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
