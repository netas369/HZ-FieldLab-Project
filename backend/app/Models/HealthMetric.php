<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    protected $fillable = [
        'turbine_id',
        'reading_timestamp',
        'bearing_wear_index',
        'oil_quality_index',
        'generator_health_index',
        'overall_health_score',
    ];

    protected $casts = [
        'reading_timestamp' => 'datetime',
        'bearing_wear_index' => 'decimal:10',
        'oil_quality_index' => 'decimal:10',
        'generator_health_index' => 'decimal:10',
        'overall_health_score' => 'decimal:10',
    ];

    public function turbine() {
        return $this->belongsTo(Turbine::class);
    }
}
