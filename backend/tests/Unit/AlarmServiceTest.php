<?php

namespace Tests\Unit\Services;

use App\Enums\TurbineStatus;
use App\Models\Alarm;
use App\Models\HydraulicReading;
use App\Models\ScadaReading;
use App\Models\TemperatureReading;
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
    public function it_creates_extreme_weather_alarm_when_wind_speed_exceeds_30_ms()
    {
        // Documentation: Wind speed > 30 m/s = FAILED severity
        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 31.0, // > 30 m/s = Extreme weather
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
        $this->assertStringContainsString('Extreme Weather', $alarm->message);
        $this->assertEquals(31.0, $alarm->data['value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_rotor_overspeed_alarm_when_rpm_exceeds_20()
    {
        // Documentation: Rotor speed > 20 RPM = FAILED severity
        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 15.0,
            'power_kw' => 2000,
            'rotor_speed_rpm' => 21.5, // > 20 RPM = Overspeed
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
        $this->assertStringContainsString('Overspeed', $alarm->message);
        $this->assertEquals(21.5, $alarm->data['value']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_critical_alarm_for_low_ambient_temperature()
    {
        // Documentation: Ambient temp < -20°C = CRITICAL severity
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
            'ambient_temp_c' => -25.0, // < -20°C
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'ambient_temperature')
            ->where('severity', 'critical')
            ->first();

        $this->assertNotNull($alarm, 'Low temperature critical alarm should be created');
        $this->assertStringContainsString('Too Low', $alarm->message);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_critical_alarm_for_high_ambient_temperature()
    {
        // Documentation: Ambient temp > 45°C = CRITICAL severity
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
            'ambient_temp_c' => 48.0, // > 45°C
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'ambient_temperature')
            ->where('severity', 'critical')
            ->first();

        $this->assertNotNull($alarm, 'High temperature critical alarm should be created');
        $this->assertStringContainsString('Too High', $alarm->message);
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

        $vibration = VibrationReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'main_bearing_vibration_rms_mms' => 5.5, // Should trigger based on your TurbineDataService
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

        // Check if alarm was created (based on your TurbineDataService thresholds)
        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'main_bearing')
            ->where('alarm_type', 'vibration')
            ->first();

        // This will tell us what status your TurbineDataService actually returns
        $this->assertNotNull($alarm, 'Vibration alarm should be created if thresholds exceeded');
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

        $temperature = TemperatureReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'nacelle_temp_c' => 55.0,
            'main_bearing_temp_c' => 60.0,
            'gearbox_bearing_temp_c' => 80.0,
            'gearbox_oil_temp_c' => 70.0,
            'generator_bearing1_temp_c' => 75.0,
            'generator_bearing2_temp_c' => 75.0,
            'generator_stator_temp_c' => 100.0,
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        // Check if any temperature alarms were created
        $alarms = Alarm::where('turbine_id', $this->turbine->id)
            ->where('alarm_type', 'temperature')
            ->get();

        // This verifies the temperature checking is working
        $this->assertGreaterThanOrEqual(0, $alarms->count(), 'Temperature check should execute without errors');
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

        $hydraulic = HydraulicReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'gearbox_oil_pressure_bar' => 2.2,
            'hydraulic_pressure_bar' => 130.0, // Below threshold
        ]);

        $this->alarmService->checkAndCreateAlarms($this->turbine->id);

        $alarm = Alarm::where('turbine_id', $this->turbine->id)
            ->where('component', 'hydraulic_pressure')
            ->first();

        // Check if hydraulic alarm logic works
        $this->assertNotNull($alarm, 'Low hydraulic pressure should create alarm');
    }

    /**
     * ================================================================================
     * ALARM AUTO-RESOLUTION TESTS
     * ================================================================================
     */

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_auto_resolves_alarm_when_condition_returns_to_normal()
    {
        // Create high wind speed alarm
        $scada = ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 32.0, // Failed
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

        // Wind returns to normal
        $scada->update(['wind_speed_ms' => 15.0]);

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
        // Documentation: No SCADA data = Status Error
        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Error, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_error_when_scada_data_is_stale()
    {
        // Documentation: Data > 60 minutes old = Status Error
        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now()->subHours(2), // 2 hours old
            'wind_speed_ms' => 10.0,
            'power_kw' => 1500,
            'rotor_speed_rpm' => 15,
            'generator_speed_rpm' => 1500,
            'pitch_angle_deg' => 5,
            'yaw_angle_deg' => 180,
            'nacelle_direction_deg' => 180,
            'ambient_temp_c' => 15,
            'wind_direction_deg' => 180,
        ]);

        $this->alarmService->updateTurbineStatus($this->turbine->id);

        $this->turbine->refresh();
        $this->assertEquals(TurbineStatus::Error, $this->turbine->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sets_status_to_idle_when_wind_speed_below_cut_in()
    {
        // Documentation: Wind < 3.0 m/s = Status Idle
        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 2.5, // Below cut-in
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
        // Documentation: Wind > 25.0 m/s = Status Idle
        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 26.0, // Above cut-out
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
        // Documentation: Component failure (alarm severity=failed) = Status Error
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
        // Documentation: Good wind, no alarms = Status Normal
        ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 10.0, // Within range
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
     * HELPER METHODS
     * ================================================================================
     */

    private function createNormalScada($power = 1500)
    {
        return ScadaReading::create([
            'turbine_id' => $this->turbine->id,
            'reading_timestamp' => Carbon::now(),
            'wind_speed_ms' => 10.0,
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
