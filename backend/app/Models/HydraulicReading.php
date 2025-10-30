<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HydraulicReading extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'gearbox_oil_pressure_bar',
        'hydraulic_pressure_bar',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'gearbox_oil_pressure_bar' => 'decimal:4',
        'hydraulic_pressure_bar' => 'decimal:4',
    ];

    public function turbine() {
        return $this->belongsTo(Turbine::class);
    }
}
