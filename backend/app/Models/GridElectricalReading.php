<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GridElectricalReading extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'grid_voltage_v',
        'grid_current_a',
        'grid_frequency_hz',
        'grid_power_factor',
        'reactive_power_kvar',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'grid_voltage_v' => 'decimal:4',
        'grid_current_a' => 'decimal:4',
        'grid_frequency_hz' => 'decimal:4',
        'grid_power_factor' => 'decimal:4',
        'reactive_power_kvar' => 'decimal:4',
    ];

    public function turbine() {
        return $this->belongsTo(Turbine::class);
    }
}

