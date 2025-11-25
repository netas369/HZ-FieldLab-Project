<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
use App\Models\Turbine;
use App\Models\VibrationReading;
use Illuminate\Http\Request;

class HistoryDataController extends Controller
{
    public function loadAllHistoricalDataBetweenTwoPeriods(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $result = [];

        $turbineData = [
            'id' => $turbine->id,
            'turbine_id' => $turbine->turbine_id,
            'status' => $turbine->status,
            'scada' => $this->buildScadaDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'vibration' => $this->buildVibrationDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'hydraulic' => $this->buildHydraulicDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
            'temperature' => $this->buildTemperatureDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']),
        ];

        $result[] = $turbineData;

        return response()->json($result);
    }

    public function loadScadaDataBetweenTwoPeriods(Request $request) {

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

    public function loadHydraulicDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $hydraulicData = $this->buildHydraulicDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'hydraulic_data' => $hydraulicData,
        ]);
    }

    public function loadVibrationDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $vibrationData = $this->buildVibrationDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'vibration_data' => $vibrationData,
        ]);
    }

    public function loadTemperatureDataBetweenTwoPeriods(Request $request) {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'turbine_id' => 'required',
        ]);

        $turbine = Turbine::where('turbine_id', $validated['turbine_id'])->first();
        $temperatureData = $this->buildTemperatureDataBetweenTwoPeriods($turbine->id, $validated['start_date'], $validated['end_date']);

        return response()->json([
            'success' => true,
            'turbine' => $turbine,
            'temperature_data' => $temperatureData,
        ]);
    }

    private function buildTemperatureDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $temperatureData = TemperatureReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->get();

        return [
            'temperature_data' => $temperatureData,
        ];
    }

    private function buildVibrationDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $vibrationData = VibrationReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->get();

        return [
            'vibration_data' => $vibrationData,
        ];
    }

    private function buildHydraulicDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $hydraulicData = HydraulicReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        return [
            'hydraulic_data' => $hydraulicData,
        ];
    }

    private function buildScadaDataBetweenTwoPeriods($turbineId, $startDate, $endDate) {
        $scadaData = ScadaReading::where('turbine_id', $turbineId)
            ->whereBetween('reading_timestamp', [$startDate, $endDate])
            ->orderBy('reading_timestamp', 'asc')
            ->get();

        return [
            'scada_data' => $scadaData,
        ];
    }


}
