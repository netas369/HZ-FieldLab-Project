<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    protected $fillable = [
        'component_name',
        'normal_max',
        'warning_max',
        'critical_max',
        'failed_max',
        'unit'
    ];

    protected $casts = [
        'normal_max' => 'decimal:4',
        'warning_max' => 'decimal:4',
        'critical_max' => 'decimal:4',
        'failed_max' => 'decimal:4',
    ];

    /**
     * Get status for a value
     */
    public function getStatus(float $value): string
    {
        if ($this->failed_max && $value > $this->failed_max) {
            return 'failed';
        }
        if ($this->critical_max && $value > $this->critical_max) {
            return 'critical';
        }
        if ($this->warning_max && $value > $this->warning_max) {
            return 'warning';
        }
        return 'normal';
    }
}
