<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('thresholds', function (Blueprint $table) {
            // Group all min values together, then all max values
            // This makes the schema easier to understand
            $table->decimal('normal_min', 10, 4)->nullable()->after('normal_max');
            $table->decimal('warning_min', 10, 4)->nullable()->after('normal_min');
            $table->decimal('critical_min', 10, 4)->nullable()->after('warning_min');
            $table->decimal('failed_min', 10, 4)->nullable()->after('critical_min');
        });
    }

    public function down()
    {
        Schema::table('thresholds', function (Blueprint $table) {
            $table->dropColumn(['normal_min', 'warning_min', 'critical_min', 'failed_min']);
        });
    }
};
