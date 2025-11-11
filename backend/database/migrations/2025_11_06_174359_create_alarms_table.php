<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turbine_id')->constrained()->onDelete('cascade');
            $table->string('alarm_type'); // 'hydraulic', 'vibration', 'temperature', 'scada'
            $table->string('component'); // 'gearbox_oil_pressure', 'main_bearing_temp'....
            $table->string('severity'); // 'warning', 'critical', 'failed'....
            $table->string('status')->default('active'); // 'active', 'acknowledged', 'resolved'.....
            $table->text('message');
            $table->json('data')->nullable(); // Store the actual reading values...
            $table->timestamp('detected_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['turbine_id', 'status', 'severity']);
            $table->index('detected_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('alarms');
    }
};
