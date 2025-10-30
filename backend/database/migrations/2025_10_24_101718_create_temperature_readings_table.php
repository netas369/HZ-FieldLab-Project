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
        Schema::create('temperature_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turbine_id')->constrained('turbines')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('nacelle_temp_c', 8, 4);
            $table->decimal('gearbox_bearing_temp_c', 8, 4);
            $table->decimal('gearbox_oil_temp_c', 8, 4);
            $table->decimal('generator_bearing1_temp_c', 8, 4);
            $table->decimal('generator_bearing2_temp_c', 8, 4);
            $table->decimal('generator_stator_temp_c', 8, 4);
            $table->decimal('main_bearing_temp_c', 8, 4);
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
        Schema::dropIfExists('temperature_readings');
    }
};
