<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scada_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turbine_id')->constrained('turbines')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('wind_speed_ms', 8, 4);
            $table->decimal('power_kw', 10, 4);
            $table->decimal('rotor_speed_rpm', 8, 4);
            $table->decimal('generator_speed_rpm', 10, 4);
            $table->decimal('pitch_angle_deg', 8, 4);
            $table->decimal('yaw_angle_deg', 8, 4);
            $table->decimal('nacelle_direction_deg', 8, 4);
            $table->decimal('ambient_temp_c', 8, 4);
            $table->decimal('wind_direction_deg', 8, 4);
            $table->integer('status_code');
            $table->integer('alarm_code');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['turbine_id', 'reading_timestamp']);
            $table->index('reading_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scada_readings');
    }
};
