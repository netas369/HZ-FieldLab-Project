<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function deleteAllData(Request $request)
    {
        Turbine::query()->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
