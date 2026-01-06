<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use App\Models\Alarm;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function deleteAllData(Request $request)
    {
        DB::beginTransaction();

        try {
            // Delete in correct order to avoid foreign key constraints
            Alarm::query()->delete();
            Maintenance::query()->delete();

            // This will cascade delete all readings
            Turbine::query()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All data deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
