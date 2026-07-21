<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\StockTransactionItem;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $isStaff = auth()->user()->role === 'staff';
        $userId = auth()->id();

        $totalProducts = Product::where('is_active', true)->count();
        $totalStock = Product::where('is_active', true)->sum('stock');

        $stockInToday = StockTransactionItem::query()
            ->join('stock_transactions', 'stock_transaction_items.stock_transaction_id', '=', 'stock_transactions.id')
            ->where('stock_transactions.type', 'in')
            ->where('stock_transactions.status', 'approved')
            ->whereDate('stock_transactions.transaction_date', today())
            ->when($isStaff, function ($query) use ($userId) {
                $query->where('stock_transactions.created_by', $userId);
            })
            ->sum('stock_transaction_items.quantity');

        $stockOutToday = StockTransactionItem::query()
            ->join('stock_transactions', 'stock_transaction_items.stock_transaction_id', '=', 'stock_transactions.id')
            ->where('stock_transactions.type', 'out')
            ->where('stock_transactions.status', 'approved')
            ->whereDate('stock_transactions.transaction_date', today())
            ->when($isStaff, function ($query) use ($userId) {
                $query->where('stock_transactions.created_by', $userId);
            })
            ->sum('stock_transaction_items.quantity');

        $lowStocks = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->limit(5)
            ->get();
        
        $lowStockCount = Product::query()
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $financialSummary = null;

        if (auth()->user()->role === 'admin') {
            $monthSales = StockTransactionItem::query()
                ->join('stock_transactions', 'stock_transaction_items.stock_transaction_id', '=', 'stock_transactions.id')
                ->where('stock_transactions.type', 'out')
                ->where('stock_transactions.outflow_category', 'sale')
                ->where('stock_transactions.status', 'approved')
                ->whereYear('stock_transactions.transaction_date', now()->year)
                ->whereMonth('stock_transactions.transaction_date', now()->month)
                ->selectRaw('COALESCE(SUM(stock_transaction_items.quantity * stock_transaction_items.sale_unit_price), 0) as revenue')
                ->selectRaw('COALESCE(SUM(stock_transaction_items.quantity * stock_transaction_items.unit_price), 0) as cost')
                ->first();

            $monthLosses = StockAdjustment::query()
                ->whereIn('adjustment_type', ['damage_loss', 'opname'])
                ->where('difference', '<', 0)
                ->whereYear('adjusted_at', now()->year)
                ->whereMonth('adjusted_at', now()->month)
                ->sum(DB::raw('ABS(difference) * unit_cost'));

            $revenue = (float) ($monthSales->revenue ?? 0);
            $cost = (float) ($monthSales->cost ?? 0);

            $financialSummary = [
                'revenue' => $revenue,
                'gross_profit' => $revenue - $cost,
                'estimated_profit' => $revenue - $cost - $monthLosses,
            ];
        }

        $pendingCount = StockTransaction::where('status', 'pending')->count();

        $recentTransactionItems = StockTransactionItem::with([
    'product',
    'stockTransaction.creator',
])
    ->whereHas('stockTransaction', function ($query) {
        $query->where('status', 'approved');
    })
    ->when($isStaff, function ($query) use ($userId) {
        $query->whereHas('stockTransaction', function ($transactionQuery) use ($userId) {
            $transactionQuery->where('created_by', $userId);
        });
    })
    ->latest('id')
    ->limit(8)
    ->get()
    ->map(function ($item) {
        return [
            'source' => 'transaction',
            'date' => $item->stockTransaction?->approved_at
                ?? $item->stockTransaction?->updated_at,
            'product_name' => $item->product?->name
                ?? 'Produk tidak ditemukan',
            'code' => $item->stockTransaction?->transaction_code
                ?? '-',
            'actor' => $item->stockTransaction?->creator?->name
                ?? '-',
            'quantity' => (int) $item->quantity,
            'type' => $item->stockTransaction?->type,
        ];
    });

    $recentAdjustments = StockAdjustment::with([
        'product',
        'stockOpname.creator',
        'approver',
    ])
        ->when($isStaff, function ($query) use ($userId) {
            $query->whereHas('stockOpname', function ($opnameQuery) use ($userId) {
                $opnameQuery->where('created_by', $userId);
            });
        })
        ->latest('adjusted_at')
        ->limit(8)
        ->get()
        ->map(function ($adjustment) {
            return [
                'source' => 'adjustment',
                'date' => $adjustment->adjusted_at,
                'product_name' => $adjustment->product?->name
                    ?? 'Produk tidak ditemukan',
                'code' => $adjustment->stockOpname?->opname_code
                    ?? '-',
                'actor' => $adjustment->approver?->name
                    ?? '-',
                'quantity' => (int) $adjustment->difference,
                'type' => 'adjustment',
            ];
        });

    $recentActivities = $recentTransactionItems
        ->concat($recentAdjustments)
        ->sortByDesc('date')
        ->take(6)
        ->values();

        $startDate = now()->subDays(6)->startOfDay();

        $weeklyData = StockTransactionItem::query()
            ->selectRaw('DATE(stock_transactions.transaction_date) as date')
            ->selectRaw("SUM(CASE WHEN stock_transactions.type = 'in' THEN stock_transaction_items.quantity ELSE 0 END) as total_in")
            ->selectRaw("SUM(CASE WHEN stock_transactions.type = 'out' THEN stock_transaction_items.quantity ELSE 0 END) as total_out")
            ->join('stock_transactions', 'stock_transaction_items.stock_transaction_id', '=', 'stock_transactions.id')
            ->where('stock_transactions.status', 'approved')
            ->whereDate('stock_transactions.transaction_date', '>=', $startDate)
            ->when($isStaff, function ($query) use ($userId) {
                $query->where('stock_transactions.created_by', $userId);
            })
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $weeklyLabels = [];
        $weeklyIn = [];
        $weeklyOut = [];
        $indonesianDayNames = [
            'Mon' => 'Sen',
            'Tue' => 'Sel',
            'Wed' => 'Rab',
            'Thu' => 'Kam',
            'Fri' => 'Jum',
            'Sat' => 'Sab',
            'Sun' => 'Min',
        ];

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            $weeklyLabels[] = $indonesianDayNames[$date->format('D')];
            $weeklyIn[] = (int) ($weeklyData[$key]->total_in ?? 0);
            $weeklyOut[] = (int) ($weeklyData[$key]->total_out ?? 0);
        }

        $categories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();

        return view('pages.dashboard.index', compact(
            'totalProducts',
            'totalStock',
            'stockInToday',
            'stockOutToday',
            'lowStocks',
            'pendingCount',
            'recentActivities',
            'weeklyLabels',
            'weeklyIn',
            'weeklyOut',
            'categories',
            'lowStockCount'
            , 'financialSummary'
        ));
    }
}
