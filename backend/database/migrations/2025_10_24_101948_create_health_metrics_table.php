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
        Schema::create('health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turbine_id')->constrained('turbines')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('bearing_wear_index', 12, 10);
            $table->decimal('oil_quality_index', 12, 10);
            $table->decimal('generator_health_index', 12, 10);
            $table->decimal('overall_health_score', 12, 10);
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['turbine_id', 'reading_timestamp']);
            $table->index('reading_timestamp');
            $table->index('overall_health_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_metrics');
    }
};
