<?php

namespace Tests\Unit\Services;

use App\Enums\TurbineStatus;
use App\Models\Alarm;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
use App\Models\Threshold;
use App\Models\Turbine;
use App\Models\VibrationReading;
use App\Services\AlarmService;
use App\Services\TurbineDataService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlarmServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $alarmService;
    protected $turbineDataService;
    protected $turbine;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed thresholds before running tests
        $this->seed(\Database\Seeders\ThresholdSeeder::class);

        $this->turbineDataService = new TurbineDataService();
        $this->alarmService = new AlarmService($this->turbineDataService);

        // Create a test turbine
        $this->turbine = Turbine::create([
            'turbine_id' => 'TEST-001',
            'status' => TurbineStatus::Normal,
        ]);
    }

    /**
     * ================================================================================
     * SCADA ALARM TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_extreme_weather_alarm_when_wind_speed_exceeds_threshold()
    {
        // Get actual threshold from database
        $threshold = Threshold::where('component_name', 'wind_speed')->first();
        $failedValue = $threshold->failed_max + 1; // Just above failed threshold

        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $failedValue,
            'power_kw' => 0,
            'rotor_speed_rpm' => 0,
            'generator_speed_rpm' => 0,
            'pitch_angle_deg' => 90,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'wind_speed')
            ->where('severity', 'failed')
            ->first();

        $this->assertNotNull($alarm, 'Extreme weather alarm should be created');
        $this->assertEquals('scada', $alarm->alarm_type);
        $this->assertStringContainsString('Wind Speed', $alarm->message);
        $this->assertEquals($failedValue, $alarm->data['value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_rotor_overspeed_alarm_when_rpm_exceeds_threshold()
    {
        // Get actual threshold from database
        $threshold = Threshold::where('component_name', 'rotor_speed')->first();
        $failedValue = $threshold->failed_max + 1;

        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 15.0,
            'power_kw' => 2000,
            'rotor_speed_rpm' => $failedValue,
            'generator_speed_rpm' => 1800,
            'pitch_angle_deg' => 5,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'rotor_speed')
            ->where('severity', 'failed')
            ->first();

        $this->assertNotNull($alarm, 'Rotor overspeed alarm should be created');
        $this->assertEquals('scada', $alarm->alarm_type);
        $this->assertEquals($failedValue, $alarm->data['value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_critical_alarm_for_low_ambient_temperature()
    {
        // Get actual threshold from database
        $threshold = Threshold::where('component_name', 'ambient_temperature')->first();
        $criticalValue = $threshold->critical_min - 1; // Just below critical threshold

        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 10.0,
            'power_kw' => 0,
            'rotor_speed_rpm' => 0,
            'generator_speed_rpm' => 0,
            'pitch_angle_deg' => 90,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => $criticalValue,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'ambient_temperature')
            ->where('severity', 'critical')
            ->first();

        $this->assertNotNull($alarm, 'Low temperature critical alarm should be created');
        $this->assertStringContainsString('Temperature', $alarm->message);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_critical_alarm_for_high_ambient_temperature()
    {
        // Get actual threshold from database
        $threshold = Threshold::where('component_name', 'ambient_temperature')->first();
        $criticalValue = $threshold->critical_max + 1; // Just above critical threshold

        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 10.0,
            'power_kw' => 1500,
            'rotor_speed_rpm' => 15,
            'generator_speed_rpm' => 1500,
            'pitch_angle_deg' => 5,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => $criticalValue,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'ambient_temperature')
            ->where('severity', 'critical')
            ->first();

        $this->assertNotNull($alarm, 'High temperature critical alarm should be created');
        $this->assertStringContainsString('Temperature', $alarm->message);
    }

    /**
     * ================================================================================
     * VIBRATION ALARM TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_alarm_when_vibration_exceeds_thresholds()
    {
        $this->createNormalScada();

        // Get actual threshold from database
        $threshold = Threshold::where('component_name', 'main_bearing_vibration_rms')->first();
        $criticalValue = $threshold->critical_max + 0.5; // Above critical

        $vibration = VibrationReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'main_bearing_vibration_rms_mms' => $criticalValue,
            'main_bearing_vibration_peak_mms' => 8.0,
            'gearbox_vibration_axial_mms' => 2.0,
            'gearbox_vibration_radial_mms' => 2.0,
            'generator_vibration_de_mms' => 2.0,
            'generator_vibration_nde_mms' => 2.0,
            'tower_vibration_fa_mms' => 1.0,
            'tower_vibration_ss_mms' => 1.0,
            'blade1_vibration_mms' => 1.0,
            'blade2_vibration_mms' => 1.0,
            'blade3_vibration_mms' => 1.0,
            'acoustic_level_db' => 95,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'main_bearing')
            ->where('alarm_type', 'vibration')
            ->first();

        $this->assertNotNull($alarm, 'Vibration alarm should be created when threshold exceeded');
        $this->assertContains($alarm->severity, ['warning', 'critical', 'failed']);
    }

    /**
     * ================================================================================
     * TEMPERATURE ALARM TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_temperature_alarms_with_load_factor()
    {
        $scada = $this->createNormalScada(2000); // 80% load

        // Get threshold for gearbox bearing temperature
        $threshold = Threshold::where('component_name', 'gearbox_bearing_temp')->first();
        $criticalValue = $threshold->critical_max + 5; // Above critical

        $temperature = TemperatureReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'nacelle_temp_c' => 55.0,
            'main_bearing_temp_c' => 60.0,
            'gearbox_bearing_temp_c' => $criticalValue,
            'gearbox_oil_temp_c' => 70.0,
            'generator_bearing1_temp_c' => 75.0,
            'generator_bearing2_temp_c' => 75.0,
            'generator_stator_temp_c' => 100.0,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('alarm_type', 'temperature')
            ->where('component', 'gearbox_bearing')
            ->first();

        $this->assertNotNull($alarm, 'Temperature alarm should be created when threshold exceeded');
    }

    /**
     * ================================================================================
     * HYDRAULIC ALARM TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_checks_hydraulic_pressure_alarms()
    {
        $this->createNormalScada();

        // Get actual thresholds from database
        $threshold = Threshold::where('component_name', 'hydraulic_pressure')->first();
        $criticalValue = $threshold->critical_min - 5; // Below critical minimum

        $hydraulic = HydraulicReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'gearbox_oil_pressure_bar' => 2.2,
            'hydraulic_pressure_bar' => $criticalValue,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'hydraulic_pressure')
            ->first();

        $this->assertNotNull($alarm, 'Low hydraulic pressure should create alarm');
        $this->assertEquals('critical', $alarm->severity);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_detects_overpressure_in_hydraulic_system()
    {
        $this->createNormalScada();

        // Get actual thresholds from database
        $threshold = Threshold::where('component_name', 'hydraulic_pressure')->first();
        $overpressureValue = $threshold->critical_max + 10; // Above critical maximum

        $hydraulic = HydraulicReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'gearbox_oil_pressure_bar' => 2.5,
            'hydraulic_pressure_bar' => $overpressureValue,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'hydraulic_pressure')
            ->where('severity', 'critical')
            ->first();

        $this->assertNotNull($alarm, 'Overpressure should create critical alarm');
    }

    /**
     * ================================================================================
     * ALARM AUTO-RESOLUTION TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_auto_resolves_alarm_when_condition_returns_to_normal()
    {
        // Get threshold
        $threshold = Threshold::where('component_name', 'wind_speed')->first();
        $failedValue = $threshold->failed_max + 2;

        // Create high wind speed alarm
        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $failedValue,
            'power_kw' => 0,
            'rotor_speed_rpm' => 0,
            'generator_speed_rpm' => 0,
            'pitch_angle_deg' => 90,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'wind_speed')
            ->first();

        $this->assertEquals('active', $alarm->status);

        // Wind returns to normal range
        $normalValue = ($threshold->normal_min + $threshold->normal_max) / 2;
        $scada->update(['wind_speed_ms' => $normalValue]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm->refresh();
        $this->assertEquals('resolved', $alarm->status);
        $this->assertNotNull($alarm->resolved_at);
        $this->assertStringContainsString('Auto-resolved', $alarm->resolution_notes);
    }

    /**
     * ================================================================================
     * TURBINE STATUS UPDATE TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_error_when_no_scada_data_exists()
    {
        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Error, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_idle_when_wind_speed_below_cut_in()
    {
        // Get wind speed threshold
        $threshold = Threshold::where('component_name', 'wind_speed')->first();
        $belowCutIn = $threshold->failed_min - 0.5; // Below minimum operational wind

        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $belowCutIn,
            'power_kw' => 0,
            'rotor_speed_rpm' => 0,
            'generator_speed_rpm' => 0,
            'pitch_angle_deg' => 90,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Idle, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_idle_when_wind_speed_above_cut_out()
    {
        // Get wind speed threshold
        $threshold = Threshold::where('component_name', 'wind_speed')->first();
        $aboveCutOut = $threshold->warning_max + 5; // Above safe operating wind

        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $aboveCutOut,
            'power_kw' => 0,
            'rotor_speed_rpm' => 0,
            'generator_speed_rpm' => 0,
            'pitch_angle_deg' => 90,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Idle, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_error_when_component_failure_exists()
    {
        $this->createNormalScada();

        // Create a failed component alarm
        Alarm::create([
            'turbine_id' => $this->turbine->id,
            'alarm_type' => 'vibration',
            'component' => 'main_bearing',
            'severity' => 'failed',
            'status' => 'active',
            'message' => 'Main Bearing Failed',
            'data' => ['value' => 15.0],
            'detected_at' => Carbon::now(),
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Error, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_normal_when_all_conditions_are_good()
    {
        // Get normal ranges from thresholds
        $windThreshold = Threshold::where('component_name', 'wind_speed')->first();
        $normalWind = ($windThreshold->normal_min + $windThreshold->normal_max) / 2;

        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $normalWind,
            'power_kw' => 1800,
            'rotor_speed_rpm' => 15,
            'generator_speed_rpm' => 1600,
            'pitch_angle_deg' => 5,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Normal, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_distinguishes_component_failures_from_environmental_failures()
    {
        $this->createNormalScada();

        // Create environmental failure (wind)
        Alarm::create([
            'turbine_id' => $this->turbine->id,
            'alarm_type' => 'scada',
            'component' => 'wind_speed',
            'severity' => 'failed',
            'status' => 'active',
            'message' => 'Extreme Weather',
            'data' => ['value' => 35.0],
            'detected_at' => Carbon::now(),
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        // Environmental failure should set status to Idle
        $this->assertEquals(TurbineStatus::Idle, $this->turbine->status);
    }

    /**
     * ================================================================================
     * THRESHOLD BOUNDARY TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_correctly_handles_values_at_exact_threshold_boundaries()
    {
        // Test pressure at exact boundary (using <=)
        $threshold = Threshold::where('component_name', 'hydraulic_pressure')->first();

        $this->createNormalScada();

        // Test at exact critical_min boundary
        $hydraulic = HydraulicReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'gearbox_oil_pressure_bar' => 2.5,
            'hydraulic_pressure_bar' => $threshold->critical_min, // Exactly at boundary
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'hydraulic_pressure')
            ->first();

        $this->assertNotNull($alarm, 'Alarm should trigger at exact threshold boundary');
        $this->assertEquals('critical', $alarm->severity);
    }

    /**
     * ================================================================================
     * HELPER METHODS
     * ================================================================================
     */

    private function createNormalScada($power = 1500)
    {
        // Get normal ranges from thresholds
        $windThreshold = Threshold::where('component_name', 'wind_speed')->first();
        $normalWind = ($windThreshold->normal_min + $windThreshold->normal_max) / 2;

        return ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => $normalWind,
            'power_kw' => $power,
            'rotor_speed_rpm' => 15,
            'generator_speed_rpm' => 1500,
            'pitch_angle_deg' => 5,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);
    }
}
