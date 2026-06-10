<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\Cashflow;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
// 1. DASHBOARD SUMMARY
    public function summary(Request $request) {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            // Sales & Financial Data
            $totalRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
            $totalOrders = Transaction::whereBetween('created_at', [$startDate, $endDate])->count();
            $totalExpenses = Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
            
            $profit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? round(($profit / $totalRevenue) * 100, 1) . '%' : '0%';

            // Inventory Data
            $totalMedicines = Medicine::count();
            $lowStockCount = Stock::where('quantity', '<=', 10)->count(); // Asumsi stok menipis jika <= 10
            $expiredCount = Stock::where('expired_date', '<', now())->where('quantity', '>', 0)->count();

            // Orders Data
            $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate]);

            return response()->json([
                'success' => true,
                'data' => [
                    'sales' => [
                        'total_revenue' => (float) $totalRevenue, 
                        'total_orders' => $totalOrders
                    ],
                    'financial' => [
                        'total_expenses' => (float) $totalExpenses,
                        'profit' => (float) $profit,
                        'profit_margin' => $profitMargin
                    ],
                    'inventory' => [
                        'total_medicines' => $totalMedicines,
                        'low_stock_count' => $lowStockCount,
                        'expired_count' => $expiredCount
                    ],
                    'orders' => [
                        'pending' => (clone $ordersQuery)->where('status', 'pending')->count(),
                        'in_process' => (clone $ordersQuery)->where('status', 'diproses')->count(),
                        'in_transit' => (clone $ordersQuery)->where('status', 'dikirim')->count(),
                        'completed' => (clone $ordersQuery)->where('status', 'selesai')->count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. SALES REPORT (Digunakan untuk laman SalesReport.vue)
    public function salesReport(Request $request)
    {
        try {
            $period = $request->get('period', 'daily');
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

            if ($period === 'daily') {
                $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')
                    ->groupBy('date')->orderBy('date', 'asc')->get();
            } else {
                $data = $query->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')
                    ->groupBy('month', 'year')->get();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_sales' => (float) $data->sum('total_sales'),
                        'transaction_count' => (int) $data->sum('transaction_count'),
                        'total_discount' => (float) $data->sum('total_discount'),
                        'average' => $data->sum('transaction_count') > 0 ? ($data->sum('total_sales') / $data->sum('transaction_count')) : 0
                    ],
                    'chart_data' => $data,
                    'top_products' => [], // Tambahkan logika top_products jika perlu
                    'by_payment_status' => []
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Profit Report
     */
public function profitReport(Request $request)
{
    try {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // 1. Ambil data transaksi (Revenue)
        $transactions = \App\Models\Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(final_amount) as total_revenue')
            ->groupBy('date')
            ->get();

        // 2. Ambil data pembelian (Expenses) - Pastikan model Purchase ada
        // Jika belum ada model Purchase, gunakan DB::table('purchases')
        $expenses = \App\Models\Purchase::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_cost')
            ->groupBy('date')
            ->get();

        // 3. Gabungkan data
        $details = [];
        $allDates = collect($transactions->pluck('date'))->merge($expenses->pluck('date'))->unique()->sort();

        foreach ($allDates as $date) {
            $rev = $transactions->where('date', $date)->first()->total_revenue ?? 0;
            $cost = $expenses->where('date', $date)->first()->total_cost ?? 0;
            
            $details[] = [
                'date' => $date,
                'revenue' => (float)$rev,
                'cost' => (float)$cost,
                'profit' => (float)($rev - $cost),
                'margin' => $rev > 0 ? round((($rev - $cost) / $rev) * 100, 2) : 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenue' => (float)array_sum(array_column($details, 'revenue')),
                    'total_expenses' => (float)array_sum(array_column($details, 'cost')),
                    'total_profit' => (float)array_sum(array_column($details, 'profit')),
                    'profit_margin' => array_sum(array_column($details, 'revenue')) > 0 
                        ? round((array_sum(array_column($details, 'profit')) / array_sum(array_column($details, 'revenue'))) * 100, 2) 
                        : 0
                ],
                'details' => $details
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * Inventory Report
     */
    public function inventoryReport(Request $request)
    {
        try {
            $warehouseId = $request->get('warehouse_id');

            $query = Stock::with('medicine', 'warehouse');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $stocks = $query->get();

            // Low stock items
            $lowStockItems = $stocks->filter(function ($stock) {
                return $stock->quantity <= $stock->medicine->stock_minimum;
            })->sortBy('quantity');

            // Expired items
            $expiredItems = $stocks->filter(function ($stock) {
                return $stock->expired_date && $stock->expired_date->isPast() && $stock->quantity > 0;
            });

            // Stock by category
            $stockByCategory = $stocks->groupBy('medicine.category_id')
                ->map(function ($categoryStocks) {
                    return [
                        'category' => $categoryStocks[0]->medicine->category->name,
                        'total_quantity' => $categoryStocks->sum('quantity'),
                        'total_value' => $categoryStocks->sum(function ($stock) {
                            return $stock->quantity * $stock->medicine->base_price;
                        })
                    ];
                })
                ->sortByDesc('total_value')
                ->values();

            // Stock value
            $totalStockValue = $stocks->sum(function ($stock) {
                return $stock->quantity * $stock->medicine->base_price;
            });

            return response()->json([
                'message' => 'Inventory report berhasil diambil',
                'data' => [
                    'summary' => [
                        'total_items' => $stocks->count(),
                        'total_quantity' => $stocks->sum('quantity'),
                        'total_value' => (float) $totalStockValue,
                        'low_stock_count' => $lowStockItems->count(),
                        'expired_count' => $expiredItems->count(),
                    ],
                    'low_stock_items' => $lowStockItems->values(),
                    'expired_items' => $expiredItems->values(),
                    'by_category' => $stockByCategory,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Financial Report (Cashflow)
     */
    public function financialReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            // Income
            $income = Cashflow::where('type', 'income')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            // Expenses
            $expenses = Cashflow::where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')
                ->get();

            $totalIncome = $income->sum('total');
            $totalExpenses = $expenses->sum('total');
            $netIncome = $totalIncome - $totalExpenses;

            // Daily cashflow
            $dailyCashflow = Cashflow::whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('transaction_date, type, SUM(amount) as total')
                ->groupBy('transaction_date', 'type')
                ->orderBy('transaction_date', 'asc')
                ->get();

            return response()->json([
                'message' => 'Financial report berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'summary' => [
                        'total_income' => (float) $totalIncome,
                        'total_expenses' => (float) $totalExpenses,
                        'net_income' => (float) $netIncome,
                    ],
                    'income_by_category' => $income,
                    'expenses_by_category' => $expenses,
                    'daily_cashflow' => $dailyCashflow,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cashflow Report
     */
public function cashflowReport(Request $request)
{
    try {
        $start = $request->get('start_date', now()->startOfMonth()->toDateString());
        $end = $request->get('end_date', now()->toDateString());

        // 1. Ambil data dari tabel 'cashflows' sesuai screenshot Anda
        $cashflows = DB::table('cashflows')
            ->whereBetween('transaction_date', [$start, $end])
            ->orderBy('transaction_date', 'asc')
            ->get();

        // 2. Hitung saldo awal (total income - total expense) sebelum tanggal mulai
        $openingIncome = DB::table('cashflows')
            ->where('transaction_date', '<', $start)
            ->where('type', 'income')
            ->sum('amount');

        $openingExpense = DB::table('cashflows')
            ->where('transaction_date', '<', $start)
            ->where('type', 'expense')
            ->sum('amount');

        $openingBalance = (float)($openingIncome - $openingExpense);

        // 3. Kalkulasi saldo berjalan (Running Balance)
        $runningBalance = $openingBalance;
        foreach ($cashflows as $item) {
            $amount = (float)$item->amount;
            if ($item->type === 'income') {
                $runningBalance += $amount;
            } else {
                $runningBalance -= $amount;
            }
            $item->balance = $runningBalance; // Menyuntikkan saldo per baris
        }

        return response()->json([
            'success' => true,
            'data' => [
                'opening_balance' => $openingBalance,
                'closing_balance' => $runningBalance,
                'cashflows' => $cashflows
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Error di Controller: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Product Performance Report
     */
    public function productPerformance(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());
            $limit = $request->get('limit', 10);

            // Top selling products
            $topProducts = \App\Models\TransactionItem::whereIn(
                'transaction_id',
                Transaction::whereBetween('created_at', [$startDate, $endDate])
                    ->pluck('id')
            )
                ->with('medicine')
                ->get()
                ->groupBy('medicine_id')
                ->map(function ($items) {
                    return [
                        'medicine' => $items[0]->medicine,
                        'total_quantity' => $items->sum('quantity'),
                        'total_revenue' => $items->sum('subtotal'),
                        'transaction_count' => $items->count(),
                    ];
                })
                ->sortByDesc('total_revenue')
                ->values()
                ->take($limit);

            // Slowest moving products
            $slowProducts = Medicine::where('status', 'aktif')
                ->with('transactionItems')
                ->get()
                ->filter(function ($medicine) {
                    return $medicine->transactionItems->count() < 5;
                })
                ->map(function ($medicine) {
                    return [
                        'medicine' => $medicine,
                        'sold_quantity' => $medicine->transactionItems->sum('quantity'),
                        'transaction_count' => $medicine->transactionItems->count(),
                    ];
                })
                ->sortBy('transaction_count')
                ->values()
                ->take($limit);

            return response()->json([
                'message' => 'Product performance report berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'top_products' => $topProducts,
                    'slow_products' => $slowProducts,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analytics (Advanced metrics)
     */
    public function analytics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            // Customer metrics
            $newCustomers = \App\Models\User::where('role', 'customer')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $repeatCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
                ->distinct()
                ->count('customer_id');

            $customerLifetimeValue = Order::with('customer')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($orders) {
                    return [
                        'customer' => $orders[0]->customer,
                        'total_spent' => $orders->sum('total_amount'),
                        'order_count' => $orders->count(),
                    ];
                })
                ->sortByDesc('total_spent')
                ->values()
                ->take(10);

            // Product turnover
            $productTurnover = Medicine::where('status', 'aktif')
                ->with('transactionItems', 'stocks')
                ->get()
                ->map(function ($medicine) {
                    $totalSold = $medicine->transactionItems->sum('quantity');
                    $currentStock = $medicine->stocks->sum('quantity');
                    $turnoverRate = $totalSold > 0 ? ($currentStock / ($totalSold / 30)) : 0; // days
                    return [
                        'medicine' => $medicine,
                        'total_sold' => $totalSold,
                        'current_stock' => $currentStock,
                        'turnover_days' => round($turnoverRate, 2),
                    ];
                })
                ->sortBy('turnover_days')
                ->values()
                ->take(10);

            return response()->json([
                'message' => 'Analytics berhasil diambil',
                'data' => [
                    'range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ],
                    'customer_metrics' => [
                        'new_customers' => $newCustomers,
                        'repeat_customers' => $repeatCustomers,
                    ],
                    'customer_lifetime_value' => $customerLifetimeValue,
                    'product_turnover' => $productTurnover,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * OWNER KPI
     */
    public function kpi(Request $request)
    {
        try {

            $startDate =
                $request->get(
                    'start_date',
                    now()->startOfMonth()
                );

            $endDate =
                $request->get(
                    'end_date',
                    now()
                );

            $totalRevenue =
                Transaction::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )->sum('final_amount');

            $totalExpense =
                Purchase::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )->sum('total_amount');

            $profit =
                $totalRevenue -
                $totalExpense;

            $transactionCount =
                Transaction::whereBetween(
                    'created_at',
                    [$startDate, $endDate]
                )->count();

            $lowStock =
                Stock::lowStock()
                ->count();

            $expiredMedicine =
                Stock::expired()
                ->count();

            $pendingOrder =
                Order::where(
                    'status',
                    'pending'
                )->count();

            return response()->json([

                'success' => true,

                'data' => [

                    'revenue' =>
                    $totalRevenue,

                    'expense' =>
                    $totalExpense,

                    'profit' =>
                    $profit,

                    'transaction_count' =>
                    $transactionCount,

                    'low_stock' =>
                    $lowStock,

                    'expired_medicine' =>
                    $expiredMedicine,

                    'pending_order' =>
                    $pendingOrder
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * DASHBOARD CHARTS
     */
    public function charts(Request $request)
    {
        try {

            $days =
                $request->get(
                    'days',
                    7
                );

            $data =
                Transaction::selectRaw(
                    'DATE(created_at) as date,
                SUM(final_amount) as revenue,
                COUNT(*) as transactions'
                )
                ->whereDate(
                    'created_at',
                    '>=',
                    now()->subDays($days)
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([

                'success' => true,

                'data' =>
                $data

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * TOP MEDICINES
     */
    public function topMedicines()
    {
        try {

            $data =
                \App\Models\TransactionItem::select(
                    'medicine_id',
                    DB::raw(
                        'SUM(quantity)
                    as total_sold'
                    ),
                    DB::raw(
                        'SUM(subtotal)
                    as revenue'
                    )
                )
                ->with('medicine')
                ->groupBy(
                    'medicine_id'
                )
                ->orderByDesc(
                    'total_sold'
                )
                ->take(10)
                ->get();

            return response()->json([

                'success' => true,

                'data' =>
                $data

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * WARNING SYSTEM
     */
    public function warnings()
    {
        try {

            $lowStock =
                Stock::with('medicine')
                ->lowStock()
                ->get();

            $expired =
                Stock::with('medicine')
                ->expired()
                ->get();

            $expiringSoon =
                Stock::with('medicine')
                ->expiringSoon()
                ->get();

            return response()->json([

                'success' => true,

                'data' => [

                    'low_stock' =>
                    $lowStock,

                    'expired' =>
                    $expired,

                    'expiring_soon' =>
                    $expiringSoon
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }

    /**
     * OWNER ANALYTICS
     */
    public function analyticsOwner()
    {
        try {

            $bestStaff =
                Transaction::select(
                    'kasir_id',
                    DB::raw(
                        'COUNT(*) as total_transaction'
                    ),
                    DB::raw(
                        'SUM(final_amount)
                    as revenue'
                    )
                )
                ->with('kasir')
                ->groupBy(
                    'kasir_id'
                )
                ->orderByDesc(
                    'revenue'
                )
                ->take(5)
                ->get();

            $averageTransaction =
                Transaction::avg(
                    'final_amount'
                );

            $busyHours =
                Transaction::selectRaw(
                    'HOUR(created_at)
                as hour,
                COUNT(*) as total'
                )
                ->groupBy('hour')
                ->orderByDesc(
                    'total'
                )
                ->take(5)
                ->get();

            return response()->json([

                'success' => true,

                'data' => [

                    'best_staff' =>
                    $bestStaff,

                    'average_transaction' =>
                    $averageTransaction,

                    'busy_hours' =>
                    $busyHours
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
}
