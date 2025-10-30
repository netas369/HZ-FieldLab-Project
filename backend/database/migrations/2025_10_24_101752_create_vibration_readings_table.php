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
        Schema::create('vibration_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turbine_id')->constrained('turbines')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('main_bearing_vibration_rms_mms', 8, 4);
            $table->decimal('main_bearing_vibration_peak_mms', 8, 4);
            $table->decimal('gearbox_vibration_axial_mms', 8, 4);
            $table->decimal('gearbox_vibration_radial_mms', 8, 4);
            $table->decimal('generator_vibration_de_mms', 8, 4);
            $table->decimal('generator_vibration_nde_mms', 8, 4);
            $table->decimal('tower_vibration_fa_mms', 8, 4);
            $table->decimal('tower_vibration_ss_mms', 8, 4);
            $table->decimal('blade1_vibration_mms', 8, 4);
            $table->decimal('blade2_vibration_mms', 8, 4);
            $table->decimal('blade3_vibration_mms', 8, 4);
            $table->decimal('acoustic_level_db', 8, 4);
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
        Schema::dropIfExists('vibration_readings');
    }
};
