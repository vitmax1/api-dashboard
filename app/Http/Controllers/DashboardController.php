<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function stats(): JsonResponse
    {
        // Получаем статистику по часам за последние 24 часа
        $hourlyStats = Visit::select(
            DB::raw('strftime("%Y-%m-%d %H:00:00", created_at) as hour'),
            DB::raw('COUNT(DISTINCT ip) as unique_visits')
        )
            ->where('created_at', '>=', now()->subDay())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Получаем статистику по городам
        $cityStats = Visit::select('city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Получаем статистику по устройствам
        $deviceStats = Visit::select('device', DB::raw('COUNT(*) as count'))
            ->groupBy('device')
            ->get();

        return response()->json([
            'hourly' => $hourlyStats,
            'cities' => $cityStats,
            'devices' => $deviceStats,
        ]);
    }
}
