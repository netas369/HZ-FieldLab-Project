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
        Schema::table('turbines', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')
                ->default(100)
                ->comment('100=Normal, 200=Idle, 300=Maintenance, 400=Error, 500=Grid Fault');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turbines', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
