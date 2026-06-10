<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon; // Pastikan ini ditambahkan

class AnalyticsController extends Controller
{
    public function getSummary()
    {
        try {
            // 1. Data Ringkasan Kartu
            $totalTransactions = Transaction::count() + Order::count();
            
            $revenuePos = Transaction::where('payment_status', 'lunas')->sum('final_amount');
            $revenueOnline = Order::whereIn('status', ['selesai', 'dikirim'])->sum('total_amount');
            $totalRevenue = $revenuePos + $revenueOnline;

            $todayTransactions = Transaction::whereDate('created_at', today())->count() 
                               + Order::whereDate('created_at', today())->count();

            // 2. Data Grafik (7 Hari Terakhir)
            $chartLabels = [];
            $chartData = [];

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $chartLabels[] = $date->format('d M'); // Format misal: "01 Jun"

                $dailyPos = Transaction::whereDate('created_at', $date->format('Y-m-d'))
                              ->where('payment_status', 'lunas')->sum('final_amount');
                $dailyOnline = Order::whereDate('created_at', $date->format('Y-m-d'))
                               ->whereIn('status', ['selesai', 'dikirim'])->sum('total_amount');

                $chartData[] = $dailyPos + $dailyOnline;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'totalTransactions' => $totalTransactions,
                    'totalRevenue' => $totalRevenue,
                    'todayTransactions' => $todayTransactions,
                    'chart' => [
                        'labels' => $chartLabels,
                        'series' => $chartData
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}