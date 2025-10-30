<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemperatureReading extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'nacelle_temp_c',
        'gearbox_bearing_temp_c',
        'gearbox_oil_temp_c',
        'generator_bearing1_temp_c',
        'generator_bearing2_temp_c',
        'generator_stator_temp_c',
        'main_bearing_temp_c',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'nacelle_temp_c' => 'decimal:4',
        'gearbox_bearing_temp_c' => 'decimal:4',
        'gearbox_oil_temp_c' => 'decimal:4',
        'generator_bearing1_temp_c' => 'decimal:4',
        'generator_bearing2_temp_c' => 'decimal:4',
        'generator_stator_temp_c' => 'decimal:4',
        'main_bearing_temp_c' => 'decimal:4',
    ];

    public function turbine()
    {
        return $this->belongsTo(Turbine::class);
    }
}
