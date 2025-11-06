<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScadaReading;
use App\Models\Turbine;
use Illuminate\Http\Request;
use App\Services\TurbineDataService;
use Carbon\Carbon;

class HistoricDataController extends Controller
{
    protected $service;

    public function __construct(TurbineDataService $service)
    {
        $this->service = $service;
    }

     public function LogScadaMonthlyChanges($turbineId)
    {
        $turbine = Turbine::findOrFail($turbineId);

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $readings = ScadaReading::where('turbine_id', $turbine->id)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->orderBy('created_at')
            ->get(['id', 'status_code', 'alarm_code', 'created_at']);

        $previous = null;
        $changes = [];

        foreach ($readings as $reading) {
            if ($previous) {
                $diffs = [];

                if (($reading->status_code == 100 && $previous->status_code == 200) || ($reading->status_code == 200 && $previous->status_code == 100) ) {
                    $previous = $reading;
                    continue;
                } else
                if ($reading->status_code !== $previous->status_code) {
                    $diffs['status_code'] = [
                        'old' => [
                            'code' => $previous->status_code,
                            'severity' => $this->service->getStatusSeverity($previous->status_code),
                            'description' => $this->service->getStatusDescription($previous->status_code),
                        ],
                        'new' => [
                            'code' => $reading->status_code,
                            'severity' => $this->service->getStatusSeverity($reading->status_code),
                            'description' => $this->service->getStatusDescription($reading->status_code),
                        ],
                        'changed_at' => $reading->created_at,
                    ];
                    $diffs['alarm_code'] = [
                        'old' => [
                            'code' => $previous->alarm_code,
                            'severity' => $this->service->getAlarmSeverity($previous->alarm_code),
                            'description' => $this->service->getAlarmDescription($previous->alarm_code),
                        ],
                        'new' => [
                            'code' => $reading->alarm_code,
                            'severity' => $this->service->getAlarmSeverity($reading->alarm_code),
                            'description' => $this->service->getAlarmDescription($reading->alarm_code),
                        ],
                        'changed_at' => $reading->created_at,
                    ];
                }

                if (!empty($diffs)) {
                    $changes[] = [
                        'reading_id' => $reading->id,
                        'turbine_id' => $turbine->id,
                        'changes' => $diffs,
                    ];
                }
            }

            $previous = $reading;
        }

        return response()->json([
            'turbine' => $turbine->id,
            'total_changes' => count($changes),
            'data' => $changes,
        ]);
    }
}
