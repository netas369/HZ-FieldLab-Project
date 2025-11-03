<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Turbine;
use Illuminate\Http\Request;

class TurbineController extends Controller
{
    /**
     * Get all turbines to load them on the home page
     */
    public function all_turbines()
    {
        $turbine  = Turbine::all();

        return response()->json($turbine);
    }
}
