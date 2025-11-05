<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use App\Models\Turbine;

class TurbineTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = storage_path('app/imports/wind_farm_training_data.csv');

    $handle = fopen($csvPath, 'r');
    $headers = fgetcsv($handle);

    $counter = 0;
    $maxRows = 10;
while (($row = fgetcsv($handle)) !== false && $counter < $maxRows) {
    $record = array_combine($headers, $row);

    $turbineId = $record['turbine_id'];
    if (app()->environment('testing')) {
        $turbineId .= '_' . uniqid();
    }

    \App\Models\Turbine::create([
        'turbine_id' => $turbineId,
    ]);

    $counter++;
}

fclose($handle);
    }

}