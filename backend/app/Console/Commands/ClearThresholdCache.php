<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Threshold;

class ClearThresholdCache extends Command
{
    protected $signature = 'threshold:clear-cache';
    protected $description = 'Clear threshold cache';

    public function handle()
    {
        // Get all threshold component names
        $components = Threshold::pluck('component_name')->unique();

        $cleared = 0;
        foreach ($components as $component) {
            $key = "threshold_{$component}";
            if (Cache::forget($key)) {
                $cleared++;
            }
        }

        $this->info("✅ Cleared {$cleared} threshold cache keys!");

        return 0;
    }
}
