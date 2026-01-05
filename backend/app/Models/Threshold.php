<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    protected $fillable = [
        'component_name',
        'normal_max', 'normal_min',
        'warning_max', 'warning_min',
        'critical_max', 'critical_min',
        'failed_max', 'failed_min',
        'unit',
    ];

    /**
     * Get status for a given value
     * Handles both "too high" and "too low" scenarios
     *
     * Priority order:
     * 1. Check if value is TOO HIGH (failed_max → critical_max → warning_max) - SKIP FOR PRESSURE
     * 2. Check if value is TOO LOW (failed_min → critical_min → warning_min)
     * 3. Check if within NORMAL range (between normal_min and normal_max)
     *
     * @param float|null $value The sensor reading to evaluate
     * @return string Status: 'normal', 'warning', 'critical', 'failed', or 'unknown'
     */
    public function getStatus($value)
    {
        if ($value === null) {
            return 'unknown';
        }

        // ============================================
        // STEP 1: Check TOO HIGH (exceeds maximum)
        // Check for ALL components (including pressure - overpressure is dangerous!)
        // ============================================
        if ($this->failed_max !== null && $this->failed_max < 900) {
            if ($value > $this->failed_max) {
                return 'failed';
            }
        }
        if ($this->critical_max !== null && $value > $this->critical_max) {
            return 'critical';
        }
        if ($this->warning_max !== null && $value > $this->warning_max) {
            return 'warning';
        }

        // ============================================
        // STEP 2: Check TOO LOW (below minimum)
        // Use <= to include exact boundary values
        // ============================================
        if ($this->failed_min !== null && $this->failed_min > 0.01) {
            if ($value <= $this->failed_min) {
                return 'failed';
            }
        }
        if ($this->critical_min !== null && $value <= $this->critical_min) {
            return 'critical';
        }
        if ($this->warning_min !== null && $value <= $this->warning_min) {
            return 'warning';
        }

        // ============================================
        // STEP 3: Check if within NORMAL range
        // ============================================
        $withinMaxRange = $this->normal_max === null || $value <= $this->normal_max;
        $withinMinRange = $this->normal_min === null || $value >= $this->normal_min;

        if ($withinMaxRange && $withinMinRange) {
            return 'normal';
        }

        // If we get here, value is in a gap between thresholds (edge case)
        return 'unknown';
    }

    /**
     * Validate threshold ranges
     * Ensures data integrity before saving
     *
     * @return array Array of error messages (empty if valid)
     */
    public function validateThresholds()
    {
        $errors = [];

        // ============================================
        // Validate MAX thresholds (ascending order)
        // normal_max < warning_max < critical_max < failed_max
        // ============================================
        if ($this->normal_max !== null && $this->warning_max !== null) {
            if ($this->normal_max >= $this->warning_max) {
                $errors[] = "normal_max ({$this->normal_max}) must be less than warning_max ({$this->warning_max})";
            }
        }
        if ($this->warning_max !== null && $this->critical_max !== null) {
            if ($this->warning_max >= $this->critical_max) {
                $errors[] = "warning_max ({$this->warning_max}) must be less than critical_max ({$this->critical_max})";
            }
        }
        if ($this->critical_max !== null && $this->failed_max !== null) {
            if ($this->critical_max >= $this->failed_max) {
                $errors[] = "critical_max ({$this->critical_max}) must be less than failed_max ({$this->failed_max})";
            }
        }

        // ============================================
        // Validate MIN thresholds (descending order)
        // normal_min > warning_min > critical_min > failed_min
        // ============================================
        if ($this->normal_min !== null && $this->warning_min !== null) {
            if ($this->normal_min <= $this->warning_min) {
                $errors[] = "normal_min ({$this->normal_min}) must be greater than warning_min ({$this->warning_min})";
            }
        }
        if ($this->warning_min !== null && $this->critical_min !== null) {
            if ($this->warning_min <= $this->critical_min) {
                $errors[] = "warning_min ({$this->warning_min}) must be greater than critical_min ({$this->critical_min})";
            }
        }
        if ($this->critical_min !== null && $this->failed_min !== null) {
            if ($this->critical_min <= $this->failed_min) {
                $errors[] = "critical_min ({$this->critical_min}) must be greater than failed_min ({$this->failed_min})";
            }
        }

        // ============================================
        // Validate NORMAL range consistency
        // ============================================
        if ($this->normal_min !== null && $this->normal_max !== null) {
            if ($this->normal_min >= $this->normal_max) {
                $errors[] = "normal_min ({$this->normal_min}) must be less than normal_max ({$this->normal_max})";
            }
        }

        return $errors;
    }

    /**
     * Lifecycle hook - validate before saving
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($threshold) {
            $errors = $threshold->validateThresholds();
            if (!empty($errors)) {
                throw new \InvalidArgumentException(
                    "Threshold validation failed for {$threshold->component_name}: " .
                    implode('; ', $errors)
                );
            }
        });
    }

    /**
     * Helper: Is this a "too low is bad" component (like pressure)?
     *
     * @return bool True if low values are the primary concern
     */
    public function isPrimaryMinConcern()
    {
        return in_array($this->component_name, [
            'gearbox_oil_pressure',
            'hydraulic_pressure',
        ]);
    }

    /**
     * Helper: Is this a "both directions" component (like grid)?
     *
     * @return bool True if both high and low values are concerning
     */
    public function isBidirectionalConcern()
    {
        return in_array($this->component_name, [
            'grid_voltage',
            'grid_frequency',
            'ambient_temperature',
            'wind_speed',
            'rotor_speed',
        ]);
    }

    /**
     * Get human-readable threshold ranges
     *
     * @return array Associative array of threshold ranges
     */
    public function getThresholdRanges()
    {
        return [
            'normal' => [
                'min' => $this->normal_min,
                'max' => $this->normal_max,
                'range' => $this->formatRange($this->normal_min, $this->normal_max)
            ],
            'warning' => [
                'min' => $this->warning_min,
                'max' => $this->warning_max,
                'range' => $this->formatRange($this->warning_min, $this->warning_max)
            ],
            'critical' => [
                'min' => $this->critical_min,
                'max' => $this->critical_max,
                'range' => $this->formatRange($this->critical_min, $this->critical_max)
            ],
            'failed' => [
                'min' => $this->failed_min,
                'max' => $this->failed_max,
                'range' => $this->formatRange($this->failed_min, $this->failed_max)
            ],
        ];
    }

    /**
     * Format a threshold range as a human-readable string
     *
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @return string Formatted range string
     */
    private function formatRange($min, $max)
    {
        if ($min !== null && $max !== null) {
            return "{$min} - {$max} {$this->unit}";
        } elseif ($min !== null) {
            return ">= {$min} {$this->unit}";
        } elseif ($max !== null) {
            return "<= {$max} {$this->unit}";
        }
        return "No limit";
    }

    /**
     * Get a visual representation of where a value falls in the threshold ranges
     * Useful for debugging and UI display
     *
     * @param float $value The value to visualize
     * @return string ASCII visualization of value position
     */
    public function visualizeValue($value)
    {
        $status = $this->getStatus($value);
        $ranges = $this->getThresholdRanges();

        $output = "Component: {$this->component_name}\n";
        $output .= "Value: {$value} {$this->unit}\n";
        $output .= "Status: " . strtoupper($status) . "\n\n";

        $output .= "Threshold Ranges:\n";
        foreach (['normal', 'warning', 'critical', 'failed'] as $level) {
            $indicator = ($status === $level) ? '>>> ' : '    ';
            $output .= "{$indicator}" . ucfirst($level) . ": {$ranges[$level]['range']}\n";
        }

        return $output;
    }

    /**
     * Get the "safety margin" - how close the value is to the next worse threshold
     * Returns percentage (0-100) where 100 = perfectly safe, 0 = at threshold
     *
     * @param float $value The sensor reading
     * @return array ['margin_percent' => float, 'next_threshold' => string]
     */
    public function getSafetyMargin($value)
    {
        if ($value === null) {
            return ['margin_percent' => null, 'next_threshold' => 'unknown'];
        }

        $status = $this->getStatus($value);

        // Already in bad state
        if ($status === 'failed') {
            return ['margin_percent' => 0, 'next_threshold' => 'failed'];
        }

        // Determine next worse threshold
        $nextThreshold = match($status) {
            'normal' => 'warning',
            'warning' => 'critical',
            'critical' => 'failed',
            default => 'unknown'
        };

        // Calculate margin
        // For "high is bad" components
        if ($this->warning_max !== null && !$this->isPrimaryMinConcern()) {
            $threshold = match($status) {
                'normal' => $this->warning_max,
                'warning' => $this->critical_max,
                'critical' => $this->failed_max,
                default => null
            };

            if ($threshold !== null) {
                $margin = (($threshold - $value) / $threshold) * 100;
                return [
                    'margin_percent' => max(0, min(100, $margin)),
                    'next_threshold' => $nextThreshold
                ];
            }
        }

        // For "low is bad" components (pressure)
        if ($this->warning_min !== null) {
            $threshold = match($status) {
                'normal' => $this->warning_min,
                'warning' => $this->critical_min,
                'critical' => $this->failed_min,
                default => null
            };

            if ($threshold !== null && $threshold > 0) {
                $margin = (($value - $threshold) / $value) * 100;
                return [
                    'margin_percent' => max(0, min(100, $margin)),
                    'next_threshold' => $nextThreshold
                ];
            }
        }

        return ['margin_percent' => null, 'next_threshold' => 'unknown'];
    }

    /**
     * Scope: Get all thresholds for a specific measurement type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type 'vibration', 'temperature', 'pressure', 'grid', 'environmental'
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        $patterns = [
            'vibration' => '%vibration%',
            'temperature' => '%temp%',
            'pressure' => '%pressure%',
            'grid' => 'grid_%',
            'environmental' => ['wind_speed', 'ambient_temperature', 'rotor_speed'],
        ];

        if ($type === 'environmental') {
            return $query->whereIn('component_name', $patterns[$type]);
        }

        return $query->where('component_name', 'LIKE', $patterns[$type] ?? '%');
    }
}
