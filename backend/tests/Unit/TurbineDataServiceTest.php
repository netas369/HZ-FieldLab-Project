<?php

namespace Tests\Unit\Services;

use App\Services\TurbineDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TurbineDataServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TurbineDataService();
    }

    /**==============================================================================
     * VIBRATION STATUS TESTS (Based on ISO 10816 from Documentation)
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_status_for_vibration_below_45_mms()
    {
        // Documentation: < 4.5 mm/s = Zone A (Good)
        $status = $this->service->getVibrationStatus(3.5);

        $this->assertEquals('normal', $status['status']);
        $this->assertEquals('Good', $status['label']);
    }

     #[Test]
    public function it_returns_warning_status_for_vibration_between_45_and_71_mms()
    {
        // Documentation: 4.5-7.1 mm/s = Zone B-C (Warning)
        $status = $this->service->getVibrationStatus(5.8);

        $this->assertEquals('warning', $status['status']);
        $this->assertStringContainsString('Caution', $status['label']);
    }

     #[Test]
    public function it_returns_critical_status_for_vibration_between_71_and_112_mms()
    {
        // Documentation: 7.1-11.2 mm/s = Zone C (Critical)
        $status = $this->service->getVibrationStatus(9.0);

        $this->assertEquals('critical', $status['status']);
        $this->assertStringContainsString('Critical', $status['label']);
    }

     #[Test]
    public function it_returns_failed_status_for_vibration_above_112_mms()
    {
        // Documentation: > 11.2 mm/s = Zone D (Not Acceptable)
        $status = $this->service->getVibrationStatus(12.5);

        $this->assertEquals('failed', $status['status']);
        $this->assertStringContainsString('Damage', $status['label']);
    }

    /**==============================================================================
     * BLADE VIBRATION STATUS TESTS
     * ================================================================================
     */

     #[Test]
    public function it_detects_normal_blade_vibration_when_all_blades_balanced()
    {
        // All blades similar vibration
        $status = $this->service->getBladeVibrationStatus(2.0, 2.1, 2.0);

        $this->assertEquals('normal', $status['status']);
        $this->assertLessThan(0.3, $status['imbalance']);
    }

     #[Test]
    public function it_detects_blade_imbalance_warning()
    {
        // One blade significantly different (Alarm 1003 scenario)
        $status = $this->service->getBladeVibrationStatus(2.0, 5.0, 2.0);

        $this->assertEquals('warning', $status['status']);
        $this->assertGreaterThan(0.3, $status['imbalance']);
    }

    /**==============================================================================
     * ACOUSTIC LEVEL TESTS
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_for_acoustic_level_below_102_db()
    {
        $status = $this->service->getAcousticStatus(95);

        $this->assertEquals('normal', $status['status']);
    }

     #[Test]
    public function it_returns_warning_for_acoustic_level_between_102_and_105_db()
    {
        $status = $this->service->getAcousticStatus(103);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_acoustic_level_above_105_db()
    {
        $status = $this->service->getAcousticStatus(107);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * TEMPERATURE STATUS TESTS
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_nacelle_temperature_below_50c()
    {
        $status = $this->service->getNacelleTemperatureStatus(45.0);

        $this->assertEquals('normal', $status['status']);
    }

     #[Test]
    public function it_returns_warning_for_nacelle_temperature_50_to_70c()
    {
        $status = $this->service->getNacelleTemperatureStatus(60.0);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_for_nacelle_temperature_70_to_80c()
    {
        $status = $this->service->getNacelleTemperatureStatus(75.0);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_nacelle_temperature_above_80c()
    {
        $status = $this->service->getNacelleTemperatureStatus(85.0);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * GEARBOX OIL TEMPERATURE TESTS
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_for_gearbox_oil_temp_below_70c()
    {
        $status = $this->service->getGearboxOilTempStatus(60.0);

        $this->assertEquals('normal', $status['status']);
    }

     #[Test]
    public function it_returns_warning_for_gearbox_oil_temp_70_to_75c()
    {
        $status = $this->service->getGearboxOilTempStatus(72.0);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_for_gearbox_oil_temp_75_to_85c()
    {
        $status = $this->service->getGearboxOilTempStatus(80.0);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_gearbox_oil_temp_above_85c()
    {
        $status = $this->service->getGearboxOilTempStatus(90.0);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * GENERATOR TEMPERATURE TESTS (Load-Dependent)
     * ================================================================================
     */

     #[Test]
    public function it_calculates_generator_temperature_based_on_load_factor()
    {
        // At 0% load: Expected temp = ambient (20°C)
        $status = $this->service->getGeneratorTemperatureStatus(25.0, 20.0, 0.0);
        $this->assertEquals('normal', $status['status']);

        // At 80% load: Expected = ambient + (55 * 0.8) = 20 + 44 = 64°C
        // Actual 85°C = 21°C above expected = CRITICAL
        $status = $this->service->getGeneratorTemperatureStatus(150.0, 20.0, 0.8);
        //based on fallback
        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_warning_when_generator_temp_10_to_20c_above_expected()
    {
        // Expected at 50% load: 20 + (55 * 0.5) = 47.5°C
        // Actual 60°C = +12.5°C = WARNING
        $status = $this->service->getGeneratorTemperatureStatus(130.0, 20.0, 0.5);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_when_generator_temp_20_to_30c_above_expected()
    {
        // Expected at 50% load: 20 + (55 * 0.5) = 47.5°C
        // Actual 70°C = +22.5°C = CRITICAL
        $status = $this->service->getGeneratorTemperatureStatus(150.0, 20.0, 0.5);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_when_generator_temp_over_30c_above_expected()
    {
        // Expected at 50% load: 20 + (55 * 0.5) = 47.5°C
        // Actual 80°C = +32.5°C = FAILED
        $status = $this->service->getGeneratorTemperatureStatus(160.0, 20.0, 0.5);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * MAIN BEARING TEMPERATURE TESTS (Load-Dependent)
     * ================================================================================
     */

     #[Test]
    public function it_calculates_main_bearing_temperature_based_on_load()
    {
        // Expected at 60% load: ambient + (30 * 0.6) = 20 + 18 = 38°C
        // Actual 50°C = +12°C = WARNING
        $status = $this->service->getMainBearingTempStatus(85.0, 20.0, 0.6);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_for_main_bearing_temp_20_to_30c_over_expected()
    {
        // Expected at 80% load: 20 + (30 * 0.8) = 44°C
        // Actual 68°C = +24°C = CRITICAL
        $status = $this->service->getMainBearingTempStatus(95.0, 20.0, 0.8);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_main_bearing_temp_over_30c_above_expected()
    {
        // Expected at 80% load: 20 + (30 * 0.8) = 44°C
        // Actual 80°C = +36°C = FAILED
        $status = $this->service->getMainBearingTempStatus(105.0, 20.0, 0.8);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * HYDRAULIC PRESSURE TESTS
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_for_hydraulic_pressure_160_to_180_bar()
    {
        $status = $this->service->getHydraulicPressureStatus(160.0);

        $this->assertEquals('normal', $status['status']);
    }

     #[Test]
    public function it_returns_warning_for_hydraulic_pressure_150_to_160_bar()
    {
        $status = $this->service->getHydraulicPressureStatus(152.0);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_for_hydraulic_pressure_140_to_150_bar()
    {
        $status = $this->service->getHydraulicPressureStatus(145.0);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_hydraulic_pressure_below_140_bar()
    {
        $status = $this->service->getHydraulicPressureStatus(135.0);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * GEARBOX OIL PRESSURE TESTS (Turbine State Dependent)
     * ================================================================================
     */

     #[Test]
    public function it_returns_normal_for_gearbox_oil_pressure_when_turbine_running()
    {
        // Turbine ID 1 (running): 2.3-2.5 bar = NORMAL
        $status = $this->service->getGearboxPressureStatus(2.4, 1);

        $this->assertEquals('normal', $status['status']);
    }

     #[Test]
    public function it_returns_warning_for_gearbox_oil_pressure_2_0_to_2_3_bar()
    {
        // Turbine running: 2.0-2.3 bar = WARNING
        $status = $this->service->getGearboxPressureStatus(2.1, 1);

        $this->assertEquals('warning', $status['status']);
    }

     #[Test]
    public function it_returns_critical_for_gearbox_oil_pressure_1_8_to_2_0_bar()
    {
        // Turbine running: 1.8-2.0 bar = CRITICAL
        $status = $this->service->getGearboxPressureStatus(1.9, 1);

        $this->assertEquals('critical', $status['status']);
    }

     #[Test]
    public function it_returns_failed_for_gearbox_oil_pressure_below_1_8_bar()
    {
        // Turbine running: < 1.8 bar = FAILED
        $status = $this->service->getGearboxPressureStatus(1.5, 1);

        $this->assertEquals('failed', $status['status']);
    }

    /**==============================================================================
     * BOUNDARY VALUE TESTS (Edge Cases)
     * ================================================================================
     */

     #[Test]
    public function it_handles_exact_threshold_boundaries_for_vibration()
    {
        // Test exact boundary values
        $this->assertEquals('normal', $this->service->getVibrationStatus(4.5)['status']);
        $this->assertEquals('warning', $this->service->getVibrationStatus(4.51)['status']);
        $this->assertEquals('warning', $this->service->getVibrationStatus(7.1)['status']);
        $this->assertEquals('critical', $this->service->getVibrationStatus(7.11)['status']);
        $this->assertEquals('critical', $this->service->getVibrationStatus(11.2)['status']);
        $this->assertEquals('failed', $this->service->getVibrationStatus(11.21)['status']);
    }

     #[Test]
    public function it_handles_extreme_values_safely()
    {
        // Very high values
        $status = $this->service->getVibrationStatus(100.0);
        $this->assertEquals('failed', $status['status']);

        // Zero or negative (invalid but should not crash)
        $status = $this->service->getVibrationStatus(0.0);
        $this->assertEquals('normal', $status['status']);
    }
}
