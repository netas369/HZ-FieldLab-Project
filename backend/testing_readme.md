# Wind Turbine Alarm System - Test Suite

This test suite validates that your alarm detection system correctly implements the thresholds documented in **Data_documentation_1_3__1_.docx**.

## 📁 Test Files

1. **AlarmServiceTest.php** - Tests alarm creation, resolution, and status updates
2. **TurbineDataServiceTest.php** - Tests threshold calculations and status determination

## 🚀 Installation

### 1. Copy test files to your Laravel project:

```bash
# Copy to your tests directory
cp AlarmServiceTest.php your-project/tests/Unit/Services/
cp TurbineDataServiceTest.php your-project/tests/Unit/Services/
```

### 2. Make sure PHPUnit is installed:

```bash
composer require --dev phpunit/phpunit
```

### 3. Ensure your `phpunit.xml` is configured properly:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

## 🧪 Running the Tests

### Run all tests:
```bash
php artisan test
```

### Run only alarm service tests:
```bash
php artisan test --filter=AlarmServiceTest
```

### Run only threshold tests:
```bash
php artisan test --filter=TurbineDataServiceTest
```

### Run a specific test:
```bash
php artisan test --filter=it_creates_extreme_weather_alarm_when_wind_speed_exceeds_30_ms
```

### Verbose output:
```bash
php artisan test -v
```

## 📊 Test Coverage

### SCADA Alarms (Documentation Section B.2)
- ✅ Extreme weather shutdown (wind > 30 m/s) → FAILED
- ✅ Rotor overspeed (> 20 RPM) → FAILED
- ✅ Ambient temperature too low (< -20°C) → CRITICAL
- ✅ Ambient temperature too high (> 45°C) → CRITICAL

### Vibration Alarms (ISO 10816)
- ✅ Main bearing warning (4.5-7.1 mm/s) → WARNING
- ✅ Gearbox critical (7.1-11.2 mm/s) → CRITICAL
- ✅ Generator failed (> 11.2 mm/s) → FAILED
- ✅ Blade imbalance detection → WARNING

### Temperature Alarms
- ✅ Nacelle temperature (50-70°C) → WARNING
- ✅ Generator stator overheating → CRITICAL
- ✅ Load-dependent temperature calculations
- ✅ Main bearing temperature thresholds

### Hydraulic Alarms
- ✅ Hydraulic pressure critical (< 140 bar) → FAILED
- ✅ Gearbox oil pressure warning (2.0-2.3 bar) → WARNING
- ✅ State-dependent pressure checks

### Alarm System Functions
- ✅ Automatic alarm creation
- ✅ Automatic alarm resolution
- ✅ Severity escalation
- ✅ Status code updates (100, 200, 300, 400, 500)

### Status Update Logic (Documentation Section A)
- ✅ Status 400 when no SCADA data
- ✅ Status 400 when data is stale (> 60 min)
- ✅ Status 200 when wind < 3.0 m/s (Idle - Low Wind)
- ✅ Status 200 when wind > 25.0 m/s (Idle - High Wind)
- ✅ Status 400 when component failure exists
- ✅ Status 100 when all conditions normal

## 📝 Test Results Interpretation

### Expected Output:
```
PASS  Tests\Unit\Services\AlarmServiceTest
✓ it creates extreme weather alarm when wind speed exceeds 30 ms
✓ it creates rotor overspeed alarm when rpm exceeds 20
✓ it creates critical alarm for low ambient temperature
✓ it creates critical alarm for high ambient temperature
✓ it creates warning alarm for elevated main bearing vibration
✓ it creates critical alarm for high gearbox vibration
✓ it creates failed alarm for excessive generator vibration
✓ it creates warning alarm for elevated nacelle temperature
✓ it creates critical alarm for high generator stator temperature
✓ it creates failed alarm for low hydraulic pressure
✓ it creates warning alarm for low gearbox oil pressure
✓ it auto resolves alarm when condition returns to normal
✓ it sets status to error when no scada data exists
✓ it sets status to error when scada data is stale
✓ it sets status to idle when wind speed below cut in
✓ it sets status to idle when wind speed above cut out
✓ it sets status to error when component failure exists
✓ it sets status to normal when all conditions are good

PASS  Tests\Unit\Services\TurbineDataServiceTest
✓ it returns normal status for vibration below 45 mms
✓ it returns warning status for vibration between 45 and 71 mms
✓ it returns critical status for vibration between 71 and 112 mms
✓ it returns failed status for vibration above 112 mms
... (and more)

Tests:  42 passed
Time:   2.34s
```

## 🐛 What To Do If Tests Fail

### 1. Check your thresholds in TurbineDataService
The tests verify exact thresholds from the documentation:
- Vibration: 4.5, 7.1, 11.2 mm/s
- Temperature: Load-factor dependent formulas
- Pressure: 140, 150, 160, 180 bar ranges

### 2. Verify AlarmService logic
- Are alarms being created correctly?
- Is auto-resolution working?
- Are severity levels matching documentation?

### 3. Check database migrations
Make sure all tables exist with correct columns:
```bash
php artisan migrate:fresh
```

### 4. Review test output
Look for specific error messages:
```bash
php artisan test --filter=AlarmServiceTest -v
```

## 📖 Documentation Reference

These tests are based on:
- **Section A**: Status Codes (100-500)
- **Section B**: Alarm Codes (1000-5000)
    - B.2: Warning Alarms (1001-1005)
    - B.3: Critical Alarms (2001-2004)
    - B.4: Failed Component Alarms (3001-3004)
- ISO 10816 vibration standards
- Load-dependent temperature formulas

## 🔧 Troubleshooting

### Database Issues
```bash
# Reset test database
php artisan migrate:fresh --env=testing
```

### Class Not Found Errors
```bash
# Regenerate autoload files
composer dump-autoload
```

### Test Database Permissions
Make sure SQLite is enabled in `php.ini`:
```ini
extension=pdo_sqlite
extension=sqlite3
```

## ✅ Success Criteria

All tests should pass (green) if your implementation correctly follows the documentation thresholds.

**Total Tests**: 42
**Expected Result**: 42 passed

## 📞 Need Help?

If tests fail and you can't determine why:
1. Check the test output for specific failures
2. Review the corresponding section in the documentation
3. Verify your TurbineDataService threshold values
4. Check AlarmService alarm creation logic

## 🎯 Next Steps

After all tests pass:
1. ✅ Your alarm system correctly implements documentation thresholds
2. ✅ You can confidently deploy to production
3. ✅ Consider adding integration tests for API endpoints
4. ✅ Add tests for edge cases specific to your use case

---

**Pro Tip**: Run tests before every commit to ensure you haven't broken anything:
```bash
# Add to git pre-commit hook
php artisan test --filter=AlarmServiceTest
```
