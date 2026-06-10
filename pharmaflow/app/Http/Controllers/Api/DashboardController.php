<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Transaction, Order, Purchase, Medicine, Stock, Cashflow, User, TransactionItem, Attendance, Employee};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * 1. DASHBOARD SUMMARY
     */
    public function summary(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $totalSales = Transaction::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
            $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'dibatalkan')->count();
            $totalRevenue = Order::whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'dibatalkan')->sum('total_amount') + $totalSales;

            $totalMedicines = Medicine::where('status', 'aktif')->count();
            $totalStock = Stock::sum('quantity');
            $lowStockCount = Medicine::whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE medicine_id = medicines.id) <= stock_minimum')->count();
            $expiredCount = Stock::whereDate('expired_date', '<=', now())->where('quantity', '>', 0)->count();

            $totalExpenses = Cashflow::where('type', 'expense')->whereBetween('transaction_date', [$startDate, $endDate])->sum('amount');
            $profit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

            $cashTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'lunas')->sum('final_amount');
            $creditTransactions = Transaction::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'kredit')->sum('final_amount');

            return response()->json([
                'message' => 'Dashboard summary berhasil diambil',
                'data' => [
                    'period' => ['start_date' => $startDate, 'end_date' => $endDate],
                    'sales' => ['total_sales' => (float) $totalSales, 'total_revenue' => (float) $totalRevenue, 'cash_transactions' => (float) $cashTransactions, 'credit_transactions' => (float) $creditTransactions],
                    'inventory' => ['total_medicines' => $totalMedicines, 'total_stock' => $totalStock, 'low_stock_count' => $lowStockCount, 'expired_count' => $expiredCount],
                    'financial' => ['total_expenses' => (float) $totalExpenses, 'profit' => (float) $profit, 'profit_margin' => round($profitMargin, 2) . '%'],
                    'orders' => ['pending' => Order::where('status', 'pending')->count(), 'in_process' => Order::where('status', 'diproses')->count(), 'in_transit' => Order::where('status', 'dikirim')->count()]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2. SALES REPORT
     */
    public function salesReport(Request $request)
    {
        try {
            $period = $request->get('period', 'daily');
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $query = Transaction::whereBetween('created_at', [$startDate, $endDate]);

            if ($period === 'daily') {
                $data = $query->selectRaw('DATE(created_at) as date, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')->groupBy('date')->orderBy('date', 'asc')->get();
            } elseif ($period === 'weekly') {
                $data = $query->selectRaw('WEEK(created_at) as week, YEAR(created_at) as year, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')->groupBy('week', 'year')->orderBy('year', 'asc')->orderBy('week', 'asc')->get();
            } else {
                $data = $query->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as transaction_count, SUM(final_amount) as total_sales, SUM(discount_amount) as total_discount')->groupBy('month', 'year')->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
            }

            return response()->json([
                'message' => 'Sales report berhasil diambil',
                'data' => [
                    'period' => $period,
                    'chart_data' => $data,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3. PROFIT REPORT
     */
    public function profitReport(Request $request)
    {
        try {
            $period = $request->get('period', 'daily');
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $revenueQuery = Transaction::whereBetween('created_at', [$startDate, $endDate]);
            $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'dibatalkan');
            $expenseQuery = Purchase::whereBetween('created_at', [$startDate, $endDate]);

            $totalRevenue = $revenueQuery->sum('final_amount') + $ordersQuery->sum('total_amount');
            $totalExpenses = $expenseQuery->sum('total_amount');
            $totalProfit = $totalRevenue - $totalExpenses;
            $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

            return response()->json([
                'message' => 'Profit report berhasil diambil',
                'data' => [
                    'summary' => [
                        'total_revenue' => (float) $totalRevenue,
                        'total_expenses' => (float) $totalExpenses,
                        'total_profit' => (float) $totalProfit,
                        'profit_margin' => round($profitMargin, 2) . '%',
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 4. INVENTORY REPORT
     */
    public function inventoryReport(Request $request)
    {
        try {
            $stocks = Stock::with('medicine', 'warehouse')->get();
            $lowStockItems = $stocks->filter(fn($s) => $s->quantity <= $s->medicine->stock_minimum);

            return response()->json([
                'message' => 'Inventory report berhasil diambil',
                'data' => [
                    'total_items' => $stocks->count(),
                    'low_stock_count' => $lowStockItems->count(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    /**
     * 5. FINANCIAL REPORT
     */
    public function financialReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $income = Cashflow::where('type', 'income')->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')->groupBy('category')->get();
            $expenses = Cashflow::where('type', 'expense')->whereBetween('transaction_date', [$startDate, $endDate])
                ->selectRaw('category, SUM(amount) as total')->groupBy('category')->get();

            return response()->json([
                'message' => 'Financial report berhasil diambil',
                'data' => [
                    'summary' => [
                        'total_income' => (float) $income->sum('total'),
                        'total_expenses' => (float) $expenses->sum('total'),
                        'net_income' => (float) ($income->sum('total') - $expenses->sum('total')),
                    ],
                    'income_by_category' => $income,
                    'expenses_by_category' => $expenses,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 6. CASHFLOW REPORT
     */
    public function cashflowReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $cashflows = Cashflow::whereBetween('transaction_date', [$startDate, $endDate])->orderBy('transaction_date', 'asc')->get();
            
            // Logic perhitungan saldo sederhana
            $balance = 0; 
            $cashflows->map(function ($cf) use (&$balance) {
                $balance += ($cf->type === 'income') ? $cf->amount : -$cf->amount;
                $cf->running_balance = $balance;
                return $cf;
            });

            return response()->json([
                'message' => 'Cashflow report berhasil diambil',
                'data' => [
                    'closing_balance' => (float) $balance,
                    'cashflows' => $cashflows,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 7. PRODUCT PERFORMANCE
     */
    public function productPerformance(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());
            $limit = $request->get('limit', 10);

            $topProducts = TransactionItem::whereIn('transaction_id', Transaction::whereBetween('created_at', [$startDate, $endDate])->pluck('id'))
                ->with('medicine')->get()->groupBy('medicine_id')->map(function ($items) {
                    return [
                        'medicine' => $items[0]->medicine,
                        'total_revenue' => $items->sum('subtotal'),
                    ];
                })->sortByDesc('total_revenue')->take($limit)->values();

            return response()->json([
                'message' => 'Product performance berhasil diambil',
                'data' => ['top_products' => $topProducts]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 8. ANALYTICS
     */
    public function analytics(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->subMonth()->toDateString());
            $endDate = $request->get('end_date', now()->toDateString());

            $newCustomers = User::where('role', 'customer')->whereBetween('created_at', [$startDate, $endDate])->count();
            
            return response()->json([
                'message' => 'Analytics berhasil diambil',
                'data' => ['new_customers' => $newCustomers]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    /**
     * 9. OWNER KPI
     */
    public function kpi(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now());

            $totalRevenue = Transaction::whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
            $totalExpense = Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'revenue' => (float) $totalRevenue,
                    'expense' => (float) $totalExpense,
                    'profit' => (float) ($totalRevenue - $totalExpense),
                    'transaction_count' => Transaction::whereBetween('created_at', [$startDate, $endDate])->count(),
                    'low_stock' => Stock::lowStock()->count(),
                    'expired_medicine' => Stock::expired()->count(),
                    'pending_order' => Order::where('status', 'pending')->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 10. DASHBOARD CHARTS
     */
    public function charts(Request $request)
    {
        try {
            $days = $request->get('days', 7);
            $data = Transaction::selectRaw('DATE(created_at) as date, SUM(final_amount) as revenue, COUNT(*) as transactions')
                ->whereDate('created_at', '>=', now()->subDays($days))
                ->groupBy('date')->orderBy('date')->get();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 11. TOP MEDICINES
     */
    public function topMedicines()
    {
        try {
            $data = TransactionItem::select('medicine_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
                ->with('medicine')->groupBy('medicine_id')->orderByDesc('total_sold')->take(10)->get();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 12. WARNING SYSTEM
     */
    public function warnings()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'low_stock' => Stock::with('medicine')->lowStock()->get(),
                    'expired' => Stock::with('medicine')->expired()->get(),
                    'expiring_soon' => Stock::with('medicine')->expiringSoon()->get()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 13. OWNER ANALYTICS
     */
    public function analyticsOwner()
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'best_staff' => Transaction::select('kasir_id', DB::raw('COUNT(*) as total_transaction'), DB::raw('SUM(final_amount) as revenue'))
                        ->with('kasir')->groupBy('kasir_id')->orderByDesc('revenue')->take(5)->get(),
                    'average_transaction' => Transaction::avg('final_amount'),
                    'busy_hours' => Transaction::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
                        ->groupBy('hour')->orderByDesc('total')->take(5)->get()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}