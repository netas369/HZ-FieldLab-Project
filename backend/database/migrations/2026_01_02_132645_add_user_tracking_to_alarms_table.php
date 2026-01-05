<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->unsignedBigInteger('acknowledged_by')->nullable()->after('acknowledged_at');
            $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at');

            $table->foreign('acknowledged_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->dropForeign(['acknowledged_by']);
            $table->dropForeign(['resolved_by']);
            $table->dropColumn(['acknowledged_by', 'resolved_by']);
        });
    }
};
