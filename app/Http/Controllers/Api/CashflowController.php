<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cashflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashflowController extends Controller
{
    /**
     * GET /api/v1/cashflow
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !in_array($user->role, ['staff', 'owner'])) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $query = Cashflow::query();

            // Filter start date
            if ($request->filled('start_date')) {
                $query->where(
                    'transaction_date',
                    '>=',
                    Carbon::parse($request->start_date)->startOfDay()
                );
            }

            // Filter end date
            if ($request->filled('end_date')) {
                $query->where(
                    'transaction_date',
                    '<=',
                    Carbon::parse($request->end_date)->endOfDay()
                );
            }

            // Filter type
            if ($request->filled('type')) {
                $type = strtolower($request->type);

                if (in_array($type, ['income', 'expense'])) {
                    $query->where('type', $type);
                }
            }

            // Filter category
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Filter reference type
            if ($request->filled('reference_type')) {
                $query->where('reference_type', $request->reference_type);
            }

            // Sorting
            $allowedSort = [
                'transaction_date',
                'amount',
                'type',
                'category'
            ];

            $sortBy = $request->get('sort_by', 'transaction_date');
            $sortOrder = strtolower($request->get('sort_order', 'desc'));

            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            if (!in_array($sortBy, $allowedSort)) {
                $sortBy = 'transaction_date';
            }

            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = (int) $request->get('per_page', 50);

            $cashflows = $query->paginate($perPage);

            return response()->json([
                'message' => 'Cashflow records retrieved successfully',
                'data' => $cashflows->items(),
                'pagination' => [
                    'total' => $cashflows->total(),
                    'per_page' => $cashflows->perPage(),
                    'current_page' => $cashflows->currentPage(),
                    'last_page' => $cashflows->lastPage(),
                    'from' => $cashflows->firstItem(),
                    'to' => $cashflows->lastItem(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve cashflow records',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * GET /api/v1/cashflow/summary
     */
    public function summary(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || !in_array($user->role, ['staff', 'owner'])) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $period = $request->get('period', 'custom');

            switch ($period) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;

                case 'yesterday':
                    $startDate = Carbon::yesterday()->startOfDay();
                    $endDate = Carbon::yesterday()->endOfDay();
                    break;

                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;

                case 'last_week':
                    $startDate = Carbon::now()->subWeek()->startOfWeek();
                    $endDate = Carbon::now()->subWeek()->endOfWeek();
                    break;

                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;

                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;

                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;

                default:
                    $startDate = $request->filled('start_date')
                        ? Carbon::parse($request->start_date)->startOfDay()
                        : Carbon::now()->startOfMonth();

                    $endDate = $request->filled('end_date')
                        ? Carbon::parse($request->end_date)->endOfDay()
                        : Carbon::now()->endOfDay();
            }

            $totalIncome = Cashflow::where('type', 'income')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $totalExpense = Cashflow::where('type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount');

            $netCashflow = $totalIncome - $totalExpense;

            $profitMargin = $totalIncome > 0
                ? round(($netCashflow / $totalIncome) * 100, 2)
                : 0;

            $categoryBreakdown = Cashflow::select(
                'category',
                'type',
                DB::raw('SUM(amount) as total')
            )
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->groupBy('category', 'type')
                ->orderByDesc('total')
                ->get()
                ->groupBy('type');

            $referenceBreakdown = Cashflow::select(
                'reference_type',
                'type',
                DB::raw('SUM(amount) as total')
            )
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->whereNotNull('reference_type')
                ->groupBy('reference_type', 'type')
                ->get();

            return response()->json([
                'message' => 'Cashflow summary retrieved successfully',
                'data' => [
                    'period' => [
                        'type' => $period,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'days' => $startDate->diffInDays($endDate) + 1,
                    ],
                    'summary' => [
                        'total_income' => (int) $totalIncome,
                        'total_expense' => (int) $totalExpense,
                        'net_cashflow' => (int) $netCashflow,
                        'profit_margin' => $profitMargin . '%',
                    ],
                    'breakdown' => [
                        'by_category' => $categoryBreakdown,
                        'by_reference' => $referenceBreakdown,
                    ],
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve cashflow summary',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/cashflow
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'owner') {
                return response()->json([
                    'message' => 'Only owners can create cashflow entries'
                ], 403);
            }

            $validated = $request->validate([
                'transaction_date' => 'required|date',
                'type' => 'required|in:income,expense',
                'category' => 'required|string|max:100',
                'amount' => 'required|numeric|min:0',
                'description' => 'required|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            $cashflow = Cashflow::create([
                'transaction_date' => Carbon::parse($validated['transaction_date']),
                'type' => $validated['type'],
                'category' => $validated['category'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'notes' => $validated['notes'] ?? null,
                'reference_type' => null,
                'reference_id' => null,
            ]);

            return response()->json([
                'message' => 'Cashflow entry created successfully',
                'data' => $cashflow,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to create cashflow entry',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 400);
        }
    }

    /**
     * PUT /api/v1/cashflow/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'owner') {
                return response()->json([
                    'message' => 'Only owners can update cashflow'
                ], 403);
            }

            $cashflow = Cashflow::findOrFail($id);

            if ($cashflow->reference_type) {
                return response()->json([
                    'message' => 'Cannot update auto-generated cashflow entries'
                ], 422);
            }

            $validated = $request->validate([
                'transaction_date' => 'nullable|date',
                'type' => 'nullable|in:income,expense',
                'category' => 'nullable|string|max:100',
                'amount' => 'nullable|numeric|min:0',
                'description' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            if (isset($validated['transaction_date'])) {
                $validated['transaction_date'] = Carbon::parse(
                    $validated['transaction_date']
                );
            }

            $cashflow->update($validated);

            return response()->json([
                'message' => 'Cashflow entry updated successfully',
                'data' => $cashflow,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update cashflow entry',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 400);
        }
    }

    /**
     * DELETE /api/v1/cashflow/{id}
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role !== 'owner') {
                return response()->json([
                    'message' => 'Only owners can delete cashflow'
                ], 403);
            }

            $cashflow = Cashflow::findOrFail($id);

            if ($cashflow->reference_type) {
                return response()->json([
                    'message' => 'Cannot delete auto-generated cashflow entries'
                ], 422);
            }

            $cashflow->delete();

            return response()->json([
                'message' => 'Cashflow entry deleted successfully'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to delete cashflow entry',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }
}