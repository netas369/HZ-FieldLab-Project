<?php

namespace App\Constants;

/**
 * Wind Turbine Alarm Codes
 *
 * Based on ISO 10816, VDI 3834, IEC 61400 standards and wind turbine documentation
 *
 * Code Structure:
 * 1xxx = WARNING level alarms (Yellow) - Monitor, plan maintenance
 * 2xxx = CRITICAL level alarms (Orange) - Urgent action required
 * 3xxx = FAILED level alarms (Red) - Emergency shutdown/component failure
 * 5xxx = EXTERNAL/GRID alarms (Blue/Orange) - External factors
 *
 * Code Categories by Component:
 * x00x = SCADA/Environmental
 * x01x = Main Bearing
 * x02x = Gearbox
 * x03x = Generator
 * x04x = Tower/Nacelle
 * x05x = Blade System
 * x06x = Hydraulic System
 * x07x = Temperature System
 */
class AlarmCodes
{
    // ============================================
    // WARNING LEVEL (1xxx) - Yellow
    // Monitor condition, plan maintenance
    // ============================================

    // SCADA/Environmental (10xx)
    const WARNING_WIND_SPEED_ELEVATED = 1001;           // Wind approaching limits
    const WARNING_AMBIENT_TEMP_LOW = 1002;              // Temperature dropping
    const WARNING_AMBIENT_TEMP_HIGH = 1003;             // Temperature rising
    const WARNING_POWER_CURVE_DEVIATION = 1004;         // Performance deviation

    // Main Bearing (101x)
    const WARNING_MAIN_BEARING_VIBRATION = 1010;        // ISO 10816 Zone B (4.5-7.1 mm/s)
    const WARNING_MAIN_BEARING_TEMP = 1011;             // +10-20°C above expected
    const WARNING_MAIN_BEARING_WEAR = 1012;             // Early wear detected

    // Gearbox (102x)
    const WARNING_GEARBOX_VIBRATION = 1020;             // ISO 10816 Zone B
    const WARNING_GEARBOX_OIL_TEMP = 1021;              // 70-75°C
    const WARNING_GEARBOX_OIL_PRESSURE = 1022;          // 2.0-2.3 bar
    const WARNING_GEARBOX_BEARING_TEMP = 1023;          // +10-20°C above expected
    const WARNING_GEARBOX_OIL_QUALITY = 1024;           // Oil degradation detected

    // Generator (103x)
    const WARNING_GENERATOR_VIBRATION = 1030;           // ISO 10816 Zone B
    const WARNING_GENERATOR_STATOR_TEMP = 1031;         // +10-20°C above expected (100-110°C)
    const WARNING_GENERATOR_BEARING1_TEMP = 1032;       // +10-20°C above expected
    const WARNING_GENERATOR_BEARING2_TEMP = 1033;       // +10-20°C above expected
    const WARNING_GENERATOR_OVERHEATING = 1034;         // General overheating warning

    // Tower/Nacelle (104x)
    const WARNING_TOWER_VIBRATION = 1040;               // ISO 10816 Zone B
    const WARNING_NACELLE_TEMP = 1041;                  // 50-70°C
    const WARNING_YAW_SYSTEM = 1042;                    // Yaw response degraded

    // Blade System (105x)
    const WARNING_BLADE_IMBALANCE = 1050;               // Blade vibration imbalance >30%
    const WARNING_BLADE_PITCH_DEVIATION = 1051;         // Pitch angle inconsistency
    const WARNING_ACOUSTIC_LEVEL = 1052;                // 102-105 dB

    // Hydraulic System (106x)
    const WARNING_HYDRAULIC_PRESSURE = 1060;            // 150-160 bar
    const WARNING_HYDRAULIC_TEMP = 1061;                // Temperature elevated

    // ============================================
    // CRITICAL LEVEL (2xxx) - Orange
    // Urgent maintenance required, may limit operation
    // ============================================

    // SCADA/Environmental (20xx)
    const CRITICAL_AMBIENT_TEMP_VERY_LOW = 2001;        // <-20°C
    const CRITICAL_AMBIENT_TEMP_VERY_HIGH = 2002;       // >45°C

    // Main Bearing (201x)
    const CRITICAL_MAIN_BEARING_VIBRATION = 2010;       // ISO 10816 Zone C (7.1-11.2 mm/s)
    const CRITICAL_MAIN_BEARING_TEMP = 2011;            // +20-30°C above expected
    const CRITICAL_MAIN_BEARING_FAILURE_IMMINENT = 2012; // Urgent maintenance required

    // Gearbox (202x)
    const CRITICAL_GEARBOX_VIBRATION = 2020;            // ISO 10816 Zone C
    const CRITICAL_GEARBOX_OIL_TEMP = 2021;             // 75-85°C
    const CRITICAL_GEARBOX_OIL_PRESSURE = 2022;         // 1.8-2.0 bar
    const CRITICAL_GEARBOX_BEARING_TEMP = 2023;         // +20-30°C above expected
    const CRITICAL_GEARBOX_OIL_CONTAMINATION = 2024;    // Severe oil contamination

    // Generator (203x)
    const CRITICAL_GENERATOR_VIBRATION = 2030;          // ISO 10816 Zone C
    const CRITICAL_GENERATOR_STATOR_TEMP = 2031;        // +20-30°C above expected (110-115°C)
    const CRITICAL_GENERATOR_BEARING1_TEMP = 2032;      // +20-30°C above expected
    const CRITICAL_GENERATOR_BEARING2_TEMP = 2033;      // +20-30°C above expected
    const CRITICAL_GENERATOR_POWER_CURTAILMENT = 2034;  // Must reduce to 60-80% power

    // Tower/Nacelle (204x)
    const CRITICAL_TOWER_VIBRATION = 2040;              // ISO 10816 Zone C
    const CRITICAL_NACELLE_TEMP = 2041;                 // 70-80°C
    const CRITICAL_YAW_SYSTEM_FAILURE = 2042;           // Yaw system critical

    // Blade System (205x)
    const CRITICAL_BLADE_SYSTEM = 2050;                 // Blade system critical
    const CRITICAL_BLADE_STRUCTURAL = 2051;             // Structural integrity concern

    // Hydraulic System (206x)
    const CRITICAL_HYDRAULIC_PRESSURE = 2060;           // 140-150 bar
    const CRITICAL_PITCH_SYSTEM_DEGRADED = 2061;        // Pitch response severely limited

    // ============================================
    // FAILED LEVEL (3xxx) - Red
    // Emergency shutdown, component replacement required
    // ============================================

    // SCADA/Environmental (30xx)
    const FAILED_EXTREME_WEATHER = 3001;                // Wind >30 m/s
    const FAILED_ROTOR_OVERSPEED = 3002;                // >20 RPM
    const FAILED_COMMUNICATION_LOSS = 3003;             // SCADA communication lost

    // Main Bearing (301x)
    const FAILED_MAIN_BEARING_VIBRATION = 3010;         // ISO 10816 Zone D (>11.2 mm/s)
    const FAILED_MAIN_BEARING_TEMP = 3011;              // >30°C above expected
    const FAILED_MAIN_BEARING_SEIZED = 3012;            // Bearing failure - Emergency shutdown

    // Gearbox (302x)
    const FAILED_GEARBOX_VIBRATION = 3020;              // ISO 10816 Zone D
    const FAILED_GEARBOX_OIL_TEMP = 3021;               // >85°C - Oil degradation
    const FAILED_GEARBOX_OIL_PRESSURE = 3022;           // <1.8 bar - Lubrication failure
    const FAILED_GEARBOX_BEARING_TEMP = 3023;           // >30°C above expected
    const FAILED_GEARBOX_COMPONENT = 3024;              // Component replacement required

    // Generator (303x)
    const FAILED_GENERATOR_VIBRATION = 3030;            // ISO 10816 Zone D
    const FAILED_GENERATOR_STATOR_TEMP = 3031;          // >30°C above expected (>115°C)
    const FAILED_GENERATOR_BEARING1_FAILURE = 3032;     // Bearing failed
    const FAILED_GENERATOR_BEARING2_FAILURE = 3033;     // Bearing failed
    const FAILED_GENERATOR_OVERHEATED = 3034;           // Component failure - replacement required

    // Tower/Nacelle (304x)
    const FAILED_TOWER_VIBRATION = 3040;                // ISO 10816 Zone D
    const FAILED_NACELLE_TEMP = 3041;                   // >80°C - Fire risk
    const FAILED_STRUCTURAL_INTEGRITY = 3042;           // Structural failure detected

    // Blade System (305x)
    const FAILED_PITCH_SYSTEM = 3050;                   // Pitch system failure - emergency shutdown
    const FAILED_BLADE_DAMAGE = 3051;                   // Blade damage detected

    // Hydraulic System (306x)
    const FAILED_HYDRAULIC_PRESSURE = 3060;             // <140 bar - System failure
    const FAILED_HYDRAULIC_LEAK = 3061;                 // Critical hydraulic leak

    // ============================================
    // EXTERNAL/GRID LEVEL (5xxx) - Blue/Orange
    // External factors, not turbine component issues
    // ============================================

    const EXTERNAL_GRID_FAULT = 5001;                   // Grid connection issue
    const EXTERNAL_GRID_FREQUENCY = 5002;               // Grid frequency outside limits
    const EXTERNAL_GRID_VOLTAGE = 5003;                 // Grid voltage outside limits
    const EXTERNAL_WEATHER_STANDBY = 5004;              // Weather standby mode

    /**
     * Get alarm details by code
     */
    public static function getAlarmDetails(int $code): array
    {
        $alarms = self::getAllAlarmCodes();

        if (!isset($alarms[$code])) {
            return [
                'code' => $code,
                'severity' => 'unknown',
                'name' => 'Unknown Alarm Code',
                'description' => 'Unknown alarm code',
                'action' => 'Investigate immediately',
                'component' => 'unknown'
            ];
        }

        return $alarms[$code];
    }

    /**
     * Get all alarm codes with full details
     */
    public static function getAllAlarmCodes(): array
    {
        return [
            // WARNING (1xxx)
            self::WARNING_MAIN_BEARING_VIBRATION => [
                'code' => self::WARNING_MAIN_BEARING_VIBRATION,
                'severity' => 'warning',
                'name' => 'Main Bearing Vibration Warning',
                'description' => 'Vibration 4.5-7.1 mm/s (ISO 10816 Zone B)',
                'action' => 'Monitor closely, plan maintenance inspection',
                'component' => 'main_bearing',
                'standard' => 'ISO 10816-21'
            ],
            self::WARNING_MAIN_BEARING_TEMP => [
                'code' => self::WARNING_MAIN_BEARING_TEMP,
                'severity' => 'warning',
                'name' => 'Main Bearing Temperature Elevated',
                'description' => 'Temperature 10-20°C above expected',
                'action' => 'Check lubrication system',
                'component' => 'main_bearing'
            ],
            self::WARNING_GEARBOX_VIBRATION => [
                'code' => self::WARNING_GEARBOX_VIBRATION,
                'severity' => 'warning',
                'name' => 'Gearbox Vibration Warning',
                'description' => 'Vibration 4.5-7.1 mm/s (ISO 10816 Zone B)',
                'action' => 'Schedule gearbox inspection',
                'component' => 'gearbox',
                'standard' => 'ISO 10816-21'
            ],
            self::WARNING_GEARBOX_OIL_TEMP => [
                'code' => self::WARNING_GEARBOX_OIL_TEMP,
                'severity' => 'warning',
                'name' => 'Gearbox Oil Temperature High',
                'description' => 'Oil temperature 70-75°C',
                'action' => 'Check cooling system',
                'component' => 'gearbox'
            ],
            self::WARNING_GEARBOX_OIL_PRESSURE => [
                'code' => self::WARNING_GEARBOX_OIL_PRESSURE,
                'severity' => 'warning',
                'name' => 'Gearbox Oil Pressure Low',
                'description' => 'Oil pressure 2.0-2.3 bar',
                'action' => 'Monitor lubrication system',
                'component' => 'gearbox'
            ],
            self::WARNING_GENERATOR_STATOR_TEMP => [
                'code' => self::WARNING_GENERATOR_STATOR_TEMP,
                'severity' => 'warning',
                'name' => 'Generator Stator Temperature Elevated',
                'description' => 'Stator temperature 10-20°C above expected',
                'action' => 'Monitor generator, check cooling',
                'component' => 'generator_stator'
            ],
            self::WARNING_BLADE_IMBALANCE => [
                'code' => self::WARNING_BLADE_IMBALANCE,
                'severity' => 'warning',
                'name' => 'Blade Imbalance Detected',
                'description' => 'Blade vibration imbalance >30%',
                'action' => 'Schedule blade inspection and balancing',
                'component' => 'blades',
                'standard' => 'DIN ISO 1940-1 G16'
            ],
            self::WARNING_ACOUSTIC_LEVEL => [
                'code' => self::WARNING_ACOUSTIC_LEVEL,
                'severity' => 'warning',
                'name' => 'Elevated Acoustic Level',
                'description' => 'Noise level 102-105 dB',
                'action' => 'Investigate noise source',
                'component' => 'acoustic'
            ],
            self::WARNING_HYDRAULIC_PRESSURE => [
                'code' => self::WARNING_HYDRAULIC_PRESSURE,
                'severity' => 'warning',
                'name' => 'Hydraulic Pressure Below Normal',
                'description' => 'Pressure 150-160 bar',
                'action' => 'Check hydraulic system',
                'component' => 'hydraulic_pressure'
            ],
            self::WARNING_NACELLE_TEMP => [
                'code' => self::WARNING_NACELLE_TEMP,
                'severity' => 'warning',
                'name' => 'Nacelle Temperature Elevated',
                'description' => 'Temperature 50-70°C',
                'action' => 'Monitor nacelle cooling',
                'component' => 'nacelle'
            ],

            // CRITICAL (2xxx)
            self::CRITICAL_AMBIENT_TEMP_VERY_LOW => [
                'code' => self::CRITICAL_AMBIENT_TEMP_VERY_LOW,
                'severity' => 'critical',
                'name' => 'Ambient Temperature Very Low',
                'description' => 'Temperature below -20°C',
                'action' => 'Cold weather operation mode, check heating systems',
                'component' => 'ambient_temperature'
            ],
            self::CRITICAL_AMBIENT_TEMP_VERY_HIGH => [
                'code' => self::CRITICAL_AMBIENT_TEMP_VERY_HIGH,
                'severity' => 'critical',
                'name' => 'Ambient Temperature Very High',
                'description' => 'Temperature above 45°C',
                'action' => 'Implement power curtailment if needed',
                'component' => 'ambient_temperature'
            ],
            self::CRITICAL_MAIN_BEARING_VIBRATION => [
                'code' => self::CRITICAL_MAIN_BEARING_VIBRATION,
                'severity' => 'critical',
                'name' => 'Main Bearing Vibration Critical',
                'description' => 'Vibration 7.1-11.2 mm/s (ISO 10816 Zone C)',
                'action' => 'URGENT: Schedule immediate maintenance',
                'component' => 'main_bearing',
                'standard' => 'ISO 10816-21'
            ],
            self::CRITICAL_MAIN_BEARING_TEMP => [
                'code' => self::CRITICAL_MAIN_BEARING_TEMP,
                'severity' => 'critical',
                'name' => 'Main Bearing Temperature Critical',
                'description' => 'Temperature 20-30°C above expected',
                'action' => 'URGENT: Check lubrication immediately',
                'component' => 'main_bearing'
            ],
            self::CRITICAL_GEARBOX_VIBRATION => [
                'code' => self::CRITICAL_GEARBOX_VIBRATION,
                'severity' => 'critical',
                'name' => 'Gearbox Vibration Critical',
                'description' => 'Vibration 7.1-11.2 mm/s (ISO 10816 Zone C)',
                'action' => 'URGENT: Immediate gearbox inspection required',
                'component' => 'gearbox',
                'standard' => 'ISO 10816-21'
            ],
            self::CRITICAL_GEARBOX_OIL_TEMP => [
                'code' => self::CRITICAL_GEARBOX_OIL_TEMP,
                'severity' => 'critical',
                'name' => 'Gearbox Oil Temperature Critical',
                'description' => 'Oil temperature 75-85°C',
                'action' => 'URGENT: Check cooling system immediately',
                'component' => 'gearbox'
            ],
            self::CRITICAL_GEARBOX_OIL_PRESSURE => [
                'code' => self::CRITICAL_GEARBOX_OIL_PRESSURE,
                'severity' => 'critical',
                'name' => 'Gearbox Oil Pressure Critical',
                'description' => 'Oil pressure 1.8-2.0 bar',
                'action' => 'URGENT: Lubrication system failure imminent',
                'component' => 'gearbox'
            ],
            self::CRITICAL_GENERATOR_STATOR_TEMP => [
                'code' => self::CRITICAL_GENERATOR_STATOR_TEMP,
                'severity' => 'critical',
                'name' => 'Generator Stator Temperature Critical',
                'description' => 'Stator temperature 20-30°C above expected (110-115°C)',
                'action' => 'Implement power curtailment to 60-80%',
                'component' => 'generator_stator'
            ],
            self::CRITICAL_HYDRAULIC_PRESSURE => [
                'code' => self::CRITICAL_HYDRAULIC_PRESSURE,
                'severity' => 'critical',
                'name' => 'Hydraulic Pressure Critical',
                'description' => 'Pressure 140-150 bar',
                'action' => 'URGENT: Pitch system compromised',
                'component' => 'hydraulic_pressure'
            ],
            self::CRITICAL_NACELLE_TEMP => [
                'code' => self::CRITICAL_NACELLE_TEMP,
                'severity' => 'critical',
                'name' => 'Nacelle Temperature Critical',
                'description' => 'Temperature 70-80°C',
                'action' => 'Check cooling system immediately',
                'component' => 'nacelle'
            ],

            // FAILED (3xxx)
            self::FAILED_EXTREME_WEATHER => [
                'code' => self::FAILED_EXTREME_WEATHER,
                'severity' => 'failed',
                'name' => 'Extreme Weather Shutdown',
                'description' => 'Wind speed >30 m/s',
                'action' => 'EMERGENCY SHUTDOWN - Storm conditions',
                'component' => 'wind_speed',
                'standard' => 'IEC 61400-1'
            ],
            self::FAILED_ROTOR_OVERSPEED => [
                'code' => self::FAILED_ROTOR_OVERSPEED,
                'severity' => 'failed',
                'name' => 'Rotor Overspeed',
                'description' => 'Rotor speed >20 RPM',
                'action' => 'EMERGENCY SHUTDOWN - Overspeed condition',
                'component' => 'rotor_speed'
            ],
            self::FAILED_MAIN_BEARING_VIBRATION => [
                'code' => self::FAILED_MAIN_BEARING_VIBRATION,
                'severity' => 'failed',
                'name' => 'Main Bearing Vibration Failed',
                'description' => 'Vibration >11.2 mm/s (ISO 10816 Zone D)',
                'action' => 'EMERGENCY SHUTDOWN - Bearing failure imminent',
                'component' => 'main_bearing',
                'standard' => 'ISO 10816-21'
            ],
            self::FAILED_MAIN_BEARING_TEMP => [
                'code' => self::FAILED_MAIN_BEARING_TEMP,
                'severity' => 'failed',
                'name' => 'Main Bearing Temperature Failed',
                'description' => 'Temperature >30°C above expected',
                'action' => 'EMERGENCY SHUTDOWN - Bearing damage risk',
                'component' => 'main_bearing'
            ],
            self::FAILED_GEARBOX_VIBRATION => [
                'code' => self::FAILED_GEARBOX_VIBRATION,
                'severity' => 'failed',
                'name' => 'Gearbox Vibration Failed',
                'description' => 'Vibration >11.2 mm/s (ISO 10816 Zone D)',
                'action' => 'EMERGENCY SHUTDOWN - Component replacement required',
                'component' => 'gearbox',
                'standard' => 'ISO 10816-21'
            ],
            self::FAILED_GEARBOX_OIL_TEMP => [
                'code' => self::FAILED_GEARBOX_OIL_TEMP,
                'severity' => 'failed',
                'name' => 'Gearbox Oil Temperature Failed',
                'description' => 'Oil temperature >85°C',
                'action' => 'EMERGENCY SHUTDOWN - Oil degradation risk',
                'component' => 'gearbox'
            ],
            self::FAILED_GEARBOX_OIL_PRESSURE => [
                'code' => self::FAILED_GEARBOX_OIL_PRESSURE,
                'severity' => 'failed',
                'name' => 'Gearbox Oil Pressure Failed',
                'description' => 'Oil pressure <1.8 bar',
                'action' => 'EMERGENCY SHUTDOWN - Lubrication system failure',
                'component' => 'gearbox'
            ],
            self::FAILED_GENERATOR_STATOR_TEMP => [
                'code' => self::FAILED_GENERATOR_STATOR_TEMP,
                'severity' => 'failed',
                'name' => 'Generator Stator Temperature Failed',
                'description' => 'Stator temperature >30°C above expected (>115°C)',
                'action' => 'EMERGENCY SHUTDOWN - Component failure risk',
                'component' => 'generator_stator'
            ],
            self::FAILED_PITCH_SYSTEM => [
                'code' => self::FAILED_PITCH_SYSTEM,
                'severity' => 'failed',
                'name' => 'Pitch System Failure',
                'description' => 'Hydraulic pressure <140 bar',
                'action' => 'EMERGENCY SHUTDOWN - Pitch control lost',
                'component' => 'hydraulic_pressure'
            ],
            self::FAILED_HYDRAULIC_PRESSURE => [
                'code' => self::FAILED_HYDRAULIC_PRESSURE,
                'severity' => 'failed',
                'name' => 'Hydraulic Pressure System Failed',
                'description' => 'Pressure <140 bar - System failure',
                'action' => 'EMERGENCY SHUTDOWN - Pitch system failure',
                'component' => 'hydraulic_pressure'
            ],
            self::FAILED_NACELLE_TEMP => [
                'code' => self::FAILED_NACELLE_TEMP,
                'severity' => 'failed',
                'name' => 'Nacelle Temperature Failed',
                'description' => 'Temperature >80°C',
                'action' => 'EMERGENCY SHUTDOWN - Fire risk',
                'component' => 'nacelle'
            ],

            // EXTERNAL (5xxx)
            self::EXTERNAL_GRID_FAULT => [
                'code' => self::EXTERNAL_GRID_FAULT,
                'severity' => 'external',
                'name' => 'Grid Fault',
                'description' => 'Grid connection issue',
                'action' => 'Wait for grid restoration',
                'component' => 'grid'
            ],
        ];
    }

    /**
     * Map component and severity to appropriate alarm code
     */
    public static function getCodeForComponent(string $component, string $severity, ?float $value = null): ?int
    {
        // Map component and severity to alarm codes
        $mapping = [
            'wind_speed' => [
                'failed' => self::FAILED_EXTREME_WEATHER,
            ],
            'rotor_speed' => [
                'failed' => self::FAILED_ROTOR_OVERSPEED,
            ],
            'ambient_temperature' => [
                'critical' => self::CRITICAL_AMBIENT_TEMP_VERY_LOW, // Will be determined by value
            ],
            'main_bearing' => [
                'warning' => self::WARNING_MAIN_BEARING_VIBRATION,
                'critical' => self::CRITICAL_MAIN_BEARING_VIBRATION,
                'failed' => self::FAILED_MAIN_BEARING_VIBRATION,
            ],
            'gearbox' => [
                'warning' => self::WARNING_GEARBOX_VIBRATION,
                'critical' => self::CRITICAL_GEARBOX_VIBRATION,
                'failed' => self::FAILED_GEARBOX_VIBRATION,
            ],
            'gearbox_oil' => [
                'warning' => self::WARNING_GEARBOX_OIL_TEMP,
                'critical' => self::CRITICAL_GEARBOX_OIL_TEMP,
                'failed' => self::FAILED_GEARBOX_OIL_TEMP,
            ],
            'gearbox_oil_pressure' => [
                'warning' => self::WARNING_GEARBOX_OIL_PRESSURE,
                'critical' => self::CRITICAL_GEARBOX_OIL_PRESSURE,
                'failed' => self::FAILED_GEARBOX_OIL_PRESSURE,
            ],
            'generator_stator' => [
                'warning' => self::WARNING_GENERATOR_STATOR_TEMP,
                'critical' => self::CRITICAL_GENERATOR_STATOR_TEMP,
                'failed' => self::FAILED_GENERATOR_STATOR_TEMP,
            ],
            'blades' => [
                'warning' => self::WARNING_BLADE_IMBALANCE,
            ],
            'acoustic' => [
                'warning' => self::WARNING_ACOUSTIC_LEVEL,
            ],
            'hydraulic_pressure' => [
                'warning' => self::WARNING_HYDRAULIC_PRESSURE,
                'critical' => self::CRITICAL_HYDRAULIC_PRESSURE,
                'failed' => self::FAILED_HYDRAULIC_PRESSURE,
            ],
            'nacelle' => [
                'warning' => self::WARNING_NACELLE_TEMP,
                'critical' => self::CRITICAL_NACELLE_TEMP,
                'failed' => self::FAILED_NACELLE_TEMP,
            ],
        ];

        return $mapping[$component][$severity] ?? null;
    }
}
