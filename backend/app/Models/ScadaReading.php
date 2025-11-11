<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScadaReading extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'wind_speed_ms',
        'power_kw',
        'rotor_speed_rpm',
        'generator_speed_rpm',
        'pitch_angle_deg',
        'yaw_angle_deg',
        'nacelle_direction_deg',
        'ambient_temp_c',
        'wind_direction_deg',
        'status_code',
        'alarm_code',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'wind_speed_ms' => 'decimal:4',
        'power_kw' => 'decimal:4',
        'rotor_speed_rpm' => 'decimal:4',
        'generator_speed_rpm' => 'decimal:4',
        'pitch_angle_deg' => 'decimal:4',
        'yaw_angle_deg' => 'decimal:4',
        'nacelle_direction_deg' => 'decimal:4',
        'ambient_temp_c' => 'decimal:4',
        'wind_direction_deg' => 'decimal:4',
    ];

    public function turbine() {
        return $this->belongsTo(Turbine::class);
    }


}
