<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransaction::with([
            'supplier',
            'creator',
            'approver',
            'items.product',
        ])
            ->where('status', 'approved')
            ->latest('transaction_date')
            ->latest('id');

        if ($request->filled('type') && in_array($request->type, ['in', 'out'])) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $periodMode = $request->get('period_mode', 'all');

        if ($periodMode === 'day' && $request->filled('period_day')) {
            $query->whereDate('transaction_date', $request->period_day);
        }

        if ($periodMode === 'month' && $request->filled('period_month')) {
            [$year, $month] = explode('-', $request->period_month);

            $query->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month);
        }

        if ($periodMode === 'year' && $request->filled('period_year')) {
            $query->whereYear('transaction_date', $request->period_year);
        }

        if ($periodMode === 'range') {
            if ($request->filled('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }
        }

        $reportTransactions = (clone $query)->get();

        $incomingItems = $reportTransactions
            ->where('type', 'in')
            ->sum(fn ($transaction) => $transaction->items->sum('quantity'));

        $outgoingItems = $reportTransactions
            ->where('type', 'out')
            ->sum(fn ($transaction) => $transaction->items->sum('quantity'));

        $totalValueIn = $reportTransactions
            ->where('type', 'in')
            ->sum(fn ($transaction) => $transaction->items->sum(
                fn ($item) => $item->quantity * ($item->unit_price ?? 0)
            ));

        $totalValueOut = $reportTransactions
            ->where('type', 'out')
            ->sum(fn ($transaction) => $transaction->items->sum(
                fn ($item) => $item->quantity * ($item->unit_price ?? 0)
            ));

        $summary = [
            'transactions' => $reportTransactions->count(),
            'incoming_items' => $incomingItems,
            'outgoing_items' => $outgoingItems,
            'total_value_in' => $totalValueIn,
            'total_value_out' => $totalValueOut,
        ];

        $transactions = $query->paginate(10)->withQueryString();

        return view('pages.reports.index', compact(
            'transactions',
            'summary',
            'periodMode'
        ));
    }
    public function exportPdf(Request $request)
{
    $query = StockTransaction::with([
        'supplier',
        'creator',
        'approver',
        'items.product',
    ])
        ->where('status', 'approved')
        ->latest('transaction_date')
        ->latest('id');

    if ($request->filled('type') && in_array($request->type, ['in', 'out'])) {
        $query->where('type', $request->type);
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('transaction_code', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%")
                ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('name', 'like', "%{$search}%");
                });
        });
    }

    $periodMode = $request->get('period_mode', 'all');

    if ($periodMode === 'day' && $request->filled('period_day')) {
        $query->whereDate('transaction_date', $request->period_day);
    }

    if ($periodMode === 'month' && $request->filled('period_month')) {
        [$year, $month] = explode('-', $request->period_month);

        $query->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);
    }

    if ($periodMode === 'year' && $request->filled('period_year')) {
        $query->whereYear('transaction_date', $request->period_year);
    }

    if ($periodMode === 'range') {
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }
    }

    $transactions = $query->get();

    $summary = [
        'transactions' => $transactions->count(),
        'incoming_items' => $transactions
            ->where('type', 'in')
            ->sum(fn ($transaction) => $transaction->items->sum('quantity')),
        'outgoing_items' => $transactions
            ->where('type', 'out')
            ->sum(fn ($transaction) => $transaction->items->sum('quantity')),
    ];

    $periodLabel = match ($periodMode) {
        'day' => 'Harian: ' . ($request->period_day ?? '-'),
        'month' => 'Bulanan: ' . ($request->period_month ?? now()->format('Y-m')),
        'year' => 'Tahunan: ' . ($request->period_year ?? now()->year),
        'range' => 'Periode: ' . ($request->start_date ?? '-') . ' s/d ' . ($request->end_date ?? '-'),
        default => 'Semua Waktu',
    };

    $pdf = Pdf::loadView('pages.reports.pdf', compact(
        'transactions',
        'summary',
        'periodLabel'
    ))->setPaper('a4', 'portrait');

    return $pdf->download('laporan-inventaris-' . now()->format('Ymd-His') . '.pdf');
}
}