<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turbine extends Model
{
    protected $fillable = ['turbine_id'];

    public function ScadaReadings() {
        return $this->hasMany(ScadaReading::class);
    }

    public function TemperatureReading() {
        return $this->hasMany(TemperatureReading::class);
    }

    public function VibrationReading() {
        return $this->hasMany(VibrationReading::class);
    }

    public function HealthMetric() {
        return $this->hasMany(HealthMetric::class);
    }


}
