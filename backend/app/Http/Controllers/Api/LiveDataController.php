<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
use App\Models\Turbine;
use App\Models\VibrationReading;
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
            'gearbox_oil_pressure_status' => $this->service->getGearboxPressureStatus($hydraulic_readings->gearbox_oil_pressure_bar, $turbine->id),
           'hydraulic_pressure_bar' => $hydraulic_readings->hydraulic_pressure_bar,
            'hydraulic_pressure_status' => $this->service->getHydraulicPressureStatus($hydraulic_readings->hydraulic_pressure_bar),
        ]);
    }

    public function getTurbineLatestVibrationReadings($turbineId) {
        $turbine = Turbine::findOrFail($turbineId);
        $vibration = VibrationReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        return response()->json([
            'turbine_id' => $turbine->id,
            'latest_reading' => $vibration->reading_timestamp,

            // Main Bearing Vibration
            'main_bearing_vibration_rms' => $vibration->main_bearing_vibration_rms_mms,
            'main_bearing_vibration_peak' => $vibration->main_bearing_vibration_peak_mms,
            'main_bearing_status' => $this->service->getVibrationStatus($vibration->main_bearing_vibration_rms_mms),

            // Gearbox Vibration
            'gearbox_vibration_axial' => $vibration->gearbox_vibration_axial_mms,
            'gearbox_vibration_radial' => $vibration->gearbox_vibration_radial_mms,
            'gearbox_status' => $this->service->getVibrationStatus($vibration->gearbox_vibration_axial_mms),

            // Generator Vibration
            'generator_vibration_de' => $vibration->generator_vibration_de_mms,
            'generator_vibration_nde' => $vibration->generator_vibration_nde_mms,
            'generator_status' => $this->service->getVibrationStatus($vibration->generator_vibration_de_mms),

            // Tower Vibration
            'tower_vibration_fa' => $vibration->tower_vibration_fa_mms,
            'tower_vibration_ss' => $vibration->tower_vibration_ss_mms,
            'tower_status' => $this->service->getVibrationStatus($vibration->tower_vibration_fa_mms),

            // Blade Vibration
            'blade1_vibration' => $vibration->blade1_vibration_mms,
            'blade2_vibration' => $vibration->blade2_vibration_mms,
            'blade3_vibration' => $vibration->blade3_vibration_mms,
            'blade_status' => $this->service->getBladeVibrationStatus(
                $vibration->blade1_vibration_mms,
                $vibration->blade2_vibration_mms,
                $vibration->blade3_vibration_mms
            ),

            // Acoustic Level
            'acoustic_level_db' => $vibration->acoustic_level_db,
            'acoustic_status' => $this->service->getAcousticStatus($vibration->acoustic_level_db),

            // Overall Assessment
            'overall_vibration_status' => $this->service->getOverallVibrationStatus($vibration)
        ]);
    }

    /**
     * Get latest temperature readings for a turbine
     */
    public function getTurbineLatestTemperatureReadings($turbineId) {
        $turbine = Turbine::findOrFail($turbineId);
        $temperature = TemperatureReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        if(!$temperature) {
            return response()->json([
                'error' => 'No temperature data found for this turbine'
            ], 404);
        }

        $scada = ScadaReading::where('turbine_id', $turbine->id)->latest('reading_timestamp')->first();

        // calculate load factor (0-1) based on rated power (2500 kw assumingly)
        $loadFactor = $scada ? min($scada->power_kw / 2500, 1.0) : 0;

        return response()->json([
            'turbine_id' => $turbine->id,
            'latest_reading' => $temperature->reading_timestamp,
            'load_factor' => round($loadFactor, 2),

            // Nacelle Temperature
            'nacelle_temp' => $temperature->nacelle_temp_c,
            'nacelle_status' => $this->service->getNacelleTemperatureStatus($temperature->nacelle_temp_c),

            // Gearbox Temperatures
            'gearbox_bearing_temp' => $temperature->gearbox_bearing_temp_c,
            'gearbox_bearing_status' => $this->service->getGearboxBearingTempStatus(
                $temperature->gearbox_bearing_temp_c,
                $temperature->gearbox_oil_temp_c,
                $loadFactor
            ),
            'gearbox_oil_temp' => $temperature->gearbox_oil_temp_c,
            'gearbox_oil_status' => $this->service->getGearboxOilTempStatus($temperature->gearbox_oil_temp_c),

            // Generator Temperatures
            'generator_bearing1_temp' => $temperature->generator_bearing1_temp_c,
            'generator_bearing2_temp' => $temperature->generator_bearing2_temp_c,
            'generator_stator_temp' => $temperature->generator_stator_temp_c,
            'generator_status' => $this->service->getGeneratorTemperatureStatus(
                $temperature->generator_stator_temp_c,
                $temperature->gearbox_oil_temp_c, // ambient proxy
                $loadFactor
            ),

            // Main Bearing Temperature
            'main_bearing_temp' => $temperature->main_bearing_temp_c,
            'main_bearing_status' => $this->service->getMainBearingTempStatus(
                $temperature->main_bearing_temp_c,
                $temperature->gearbox_oil_temp_c,
                $loadFactor
            ),

            // Overall Temperature Assessment
            'overall_temperature_status' => $this->service->getOverallTemperatureStatus($temperature, $loadFactor)
        ]);
    }
}
