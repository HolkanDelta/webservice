<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceLog;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $services = Service::withCount('clients')->get();
        
        $servicesData = $services->map(function ($service) {
            // Last run log
            $lastLog = $service->logs()->latest()->first();
            
            // Last successful run log (status = 'success')
            $lastOkLog = $service->logs()->where('status', 'success')->latest()->first();
            
            // Reliability percentage (success rate of the last 50 runs)
            $recentLogs = $service->logs()->latest()->take(50)->get();
            $totalRecent = $recentLogs->count();
            $successRecent = $recentLogs->where('status', 'success')->count();
            
            $reliability = $totalRecent > 0 ? round(($successRecent / $totalRecent) * 100, 1) : 100.0;
            
            // Average runtime
            $avgRuntime = $service->logs()->avg('runtime_ms') ?? 0;
            
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'base_url' => $service->base_url,
                'recurrence' => $service->recurrence,
                'clients_count' => $service->clients_count,
                'last_run' => $lastLog ? [
                    'status' => $lastLog->status,
                    'message' => $lastLog->message,
                    'created_at' => $lastLog->created_at->toISOString(),
                    'payload' => $lastLog->payload,
                ] : null,
                'last_ok' => $lastOkLog ? $lastOkLog->created_at->toISOString() : null,
                'reliability' => $reliability,
                'avg_runtime' => round($avgRuntime),
            ];
        });

        // Global metrics
        $totalServicesCount = $services->count();
        
        // Operational services = number of services whose last run was success or partial (not failure, and not null unless never run)
        $operationalCount = $servicesData->filter(function ($s) {
            return $s['last_run'] === null || $s['last_run']['status'] !== 'failure';
        })->count();

        // Global success rate (percentage of all logs that are success)
        $totalLogsCount = ServiceLog::count();
        $totalSuccessCount = ServiceLog::where('status', 'success')->count();
        $globalSuccessRate = $totalLogsCount > 0 ? round(($totalSuccessCount / $totalLogsCount) * 100, 1) : 100.0;

        // Global average runtime
        $globalAvgRuntime = round(ServiceLog::avg('runtime_ms') ?? 0);

        // Recent logs feed (global)
        $recentLogs = ServiceLog::with('service')
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'service_name' => $log->service->name,
                    'status' => $log->status,
                    'message' => $log->message,
                    'runtime_ms' => $log->runtime_ms,
                    'created_at' => $log->created_at->toISOString(),
                ];
            });

        return Inertia::render('dashboard', [
            'metrics' => [
                'total_services' => $totalServicesCount,
                'operational_services' => $operationalCount,
                'global_success_rate' => $globalSuccessRate,
                'global_avg_runtime' => $globalAvgRuntime,
            ],
            'services' => $servicesData,
            'recent_logs' => $recentLogs,
        ]);
    }
}
