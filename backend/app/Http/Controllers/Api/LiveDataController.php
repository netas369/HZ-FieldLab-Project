<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\Turbine;
use App\Services\TurbineDataService;
use Illuminate\Http\Request;

class LiveDataController extends Controller
{
    protected $service;

    public function __construct(TurbineDataService $service)
    {
        $this->service = $service;
    }

    public function getTurbineLatestScadaData($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);
        $scadaData = ScadaReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        return response()->json([
            'turbine_id' => $turbine->id,
            'latest_reading' => $scadaData->reading_timestamp,
            'wind_speed_ms' => $scadaData->wind_speed_ms,
            'power_kw' => $scadaData->power_kw,
            'rotor_speed_rpm' => $scadaData->rotor_speed_rpm,
            'generator_speed_rpm' => $scadaData->generator_speed_rpm,
            'pitch_angle_deg' => $scadaData->pitch_angle_deg,
            'yaw_angle_deg' => $scadaData->yaw_angle_deg,
            'nacelle_direction_deg' => $scadaData->nacelle_direction_deg,
            'ambient_temp_c' => $scadaData->ambient_temp_c,
            'wind_direction_deg' => $scadaData->wind_direction_deg,
            'status_code' => $scadaData->status_code,
            'status_severity' => $this->service->getStatusSeverity($scadaData->status_code),
            'status_description' => $this->service->getStatusDescription($scadaData->status_code),
            'alarm_code' => $scadaData->alarm_code,
            'alarm_description' => $this->service->getAlarmDescription($scadaData->alarm_code),
            'alarm_severity' => $this->service->getAlarmSeverity($scadaData->alarm_code),
        ]);
    }

    public function getTurbineLatestHydraulicData($turbineId) {
        $turbine = Turbine::findOrFail($turbineId);
        $hydraulic_readings = HydraulicReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        return response()->json([
           'turbine_id' => $turbine->id,
           'latest_reading' => $hydraulic_readings->reading_timestamp,
           'gearbox_oil_pressure_bar' => $hydraulic_readings->gearbox_oil_pressure_bar,
            'gearbox_oil_pressure_status' => $this->getGearboxPressureStatus($hydraulic_readings->gearbox_oil_pressure_bar, $turbine->id),
           'hydraulic_pressure_bar' => $hydraulic_readings->hydraulic_pressure_bar,
            'hydraulic_pressure_status' => $this->getHydraulicPressureStatus($hydraulic_readings->hydraulic_pressure_bar),
        ]);
    }
}
