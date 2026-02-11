<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VibrationReading extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'main_bearing_vibration_rms_mms',
        'main_bearing_vibration_peak_mms',
        'gearbox_vibration_axial_mms',
        'gearbox_vibration_radial_mms',
        'generator_vibration_de_mms',
        'generator_vibration_nde_mms',
        'tower_vibration_fa_mms',
        'tower_vibration_ss_mms',
        'blade1_vibration_mms',
        'blade2_vibration_mms',
        'blade3_vibration_mms',
        'acoustic_level_db',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'main_bearing_vibration_rms_mms' => 'decimal:4',
        'main_bearing_vibration_peak_mms' => 'decimal:4',
        'gearbox_vibration_axial_mms' => 'decimal:4',
        'gearbox_vibration_radial_mms' => 'decimal:4',
        'generator_vibration_de_mms' => 'decimal:4',
        'generator_vibration_nde_mms' => 'decimal:4',
        'tower_vibration_fa_mms' => 'decimal:4',
        'tower_vibration_ss_mms' => 'decimal:4',
        'blade1_vibration_mms' => 'decimal:4',
        'blade2_vibration_mms' => 'decimal:4',
        'blade3_vibration_mms' => 'decimal:4',
        'acoustic_level_db' => 'decimal:4',
    ];

    public function turbine() {
        return $this->belongsTo(Turbine::class);
    }

    public function treshold() {
        return $this->hasOne(Treshold::class);
    }
}
