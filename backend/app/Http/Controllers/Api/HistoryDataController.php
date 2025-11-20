<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScadaReading;
use App\Models\Turbine;
use Illuminate\Http\Request;

class HistoryDataController extends Controller
{
    public function loadHistoricalDataBetweenTwoPeriods(Request $request) {

        $validated = $request->validate([
           'start_date' => 'required|date',
           'end_date' => 'required|date',
           'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $scadaData = $this->buildScadaDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
           'success' => true,
            'turbine' => $turbine,
            'scada_data' => $scadaData,
        ]);
    }

    public function buildScadaDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $scadaData = ScadaReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        return [
            'scada_data' => $scadaData,
        ];
    }
}
