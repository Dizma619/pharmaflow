<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction; // Pastikan nama Model Transaksi lu sudah benar
use Carbon\Carbon;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        try {
            // Ambil parameter tanggal dari frontend, default ke hari ini jika tidak dikirim
            $dateInput = $request->query('date');
            $date = $dateInput ? Carbon::parse($dateInput)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

            // Ambil semua transaksi pada tanggal tersebut beserta itemnya (jika ada relasi)
            // Note: Sesuaikan 'items' dengan nama fungsi relasi di Model Transaction lu
            $transactions = Transaction::whereDate('created_at', $date)
                ->with(['items']) 
                ->get();

            // Hitung total revenue dan total transaksi dengan aman
            // Note: Ganti 'total_price' dengan nama kolom nominal total di tabel transaksi lu
            $total_revenue = (int) $transactions->sum('total_price'); 
            $total_transactions = $transactions->count();

            return response()->json([
                'success' => true,
                'message' => 'Laporan harian berhasil dimuat',
                'data' => [
                    'date' => $date,
                    'total_revenue' => $total_revenue,
                    'total_transactions' => $total_transactions,
                    'transactions' => $transactions
                ]
            ], 200);

        } catch (\Exception $e) {
            // Mengembalikan pesan error asli ke console frontend agar mudah didebug jika ada kolom yang ga pas
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error di backend: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}