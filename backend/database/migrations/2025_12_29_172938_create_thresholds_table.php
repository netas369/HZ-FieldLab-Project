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
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('component_name'); // 'main_bearing_vibration_rms', 'gearbox_oil_temp', etc.
            $table->decimal('normal_max', 10, 4)->nullable();
            $table->decimal('warning_max', 10, 4)->nullable();
            $table->decimal('critical_max', 10, 4)->nullable();
            $table->decimal('failed_max', 10, 4)->nullable();
            $table->string('unit', 20)->nullable(); // 'mm/s', '°C', 'bar'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thresholds');
    }
};
