<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\InventoryCostResolver;

class ReportController extends Controller
{
    public function __construct(
        private readonly InventoryCostResolver $inventoryCostResolver
    ) {
    }

    public function index(Request $request)
    {
        $transactionQuery = $this->transactionQuery($request);
        $adjustmentQuery = $this->adjustmentQuery($request);

        $reportTransactions = (clone $transactionQuery)->get();
        $adjustments = (clone $adjustmentQuery)->get();

        $summary = $this->makeSummary(
            $reportTransactions,
            $adjustments
        );

        $transactions = $transactionQuery
            ->paginate(10)
            ->withQueryString();

        return view('pages.reports.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'periodMode' => $this->periodMode($request),
        ]);
    }

    public function exportPdf(Request $request)
    {
        $transactionQuery = $this->transactionQuery($request);
        $adjustmentQuery = $this->adjustmentQuery($request);

        $transactions = $transactionQuery->get();
        $adjustments = $adjustmentQuery->get();

        $summary = $this->makeSummary($transactions, $adjustments);
        $periodLabel = $this->periodLabel($request);

        $pdf = Pdf::loadView('pages.reports.pdf', [
            'transactions' => $transactions,
            'summary' => $summary,
            'periodLabel' => $periodLabel,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            'laporan-inventaris-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    private function transactionQuery(Request $request): Builder
    {
        $query = StockTransaction::with([
            'supplier',
            'creator',
            'approver',
            'items.product',
        ])->where('status', 'approved');

        if (
            $request->filled('type')
            && in_array($request->type, ['in', 'out'], true)
        ) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('transaction_code', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function (Builder $supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $this->applyPeriodFilter(
            $query,
            $request,
            'transaction_date'
        )
            ->latest('transaction_date')
            ->latest('id');
    }

    private function adjustmentQuery(Request $request): Builder
    {
        return $this->applyPeriodFilter(
            StockAdjustment::query(),
            $request,
            'adjusted_at'
        );
    }

    private function applyPeriodFilter(
        Builder $query,
        Request $request,
        string $dateColumn
    ): Builder {
        $periodMode = $this->periodMode($request);

        if ($periodMode === 'day' && $request->filled('period_day')) {
            return $query->whereDate($dateColumn, $request->period_day);
        }

        if (
            $periodMode === 'month'
            && preg_match('/^\d{4}-\d{2}$/', (string) $request->period_month)
        ) {
            [$year, $month] = explode('-', $request->period_month);

            return $query
                ->whereYear($dateColumn, (int) $year)
                ->whereMonth($dateColumn, (int) $month);
        }

        if ($periodMode === 'year' && $request->filled('period_year')) {
            return $query->whereYear($dateColumn, $request->period_year);
        }

        if ($periodMode === 'range') {
            if ($request->filled('start_date')) {
                $query->whereDate(
                    $dateColumn,
                    '>=',
                    $request->start_date
                );
            }

            if ($request->filled('end_date')) {
                $query->whereDate(
                    $dateColumn,
                    '<=',
                    $request->end_date
                );
            }
        }

        return $query;
    }

    private function makeSummary(
        Collection $transactions,
        Collection $adjustments
    ): array {
        $incomingTransactions = $transactions
            ->where('type', 'in');

        $outgoingTransactions = $transactions
            ->where('type', 'out');

        // Hanya barang keluar yang tercatat sebagai penjualan yang dihitung.
        // Barang keluar lama tanpa harga jual tidak dipaksakan menjadi penjualan.
        $salesItems = $outgoingTransactions
            ->where('outflow_category', 'sale')
            ->flatMap(fn (StockTransaction $transaction) => $transaction->items);

        $salesRevenue = (float) $salesItems->sum(
            fn ($item) => (int) $item->quantity * (float) ($item->sale_unit_price ?? 0)
        );

        /*
        |------------------------------------------------------------------
        | HPP Penjualan
        |------------------------------------------------------------------
        |
        | Harga beli master Produk tidak dipakai. Perhitungan selalu
        | mengambil harga Barang Masuk yang telah disetujui sebelum
        | transaksi keluar tersebut disetujui. Dengan demikian, laporan
        | lama yang pernah menyimpan harga master Produk tetap dikoreksi.
        |
        */
        $salesCost = (float) $outgoingTransactions
            ->where('outflow_category', 'sale')
            ->sum(function (StockTransaction $transaction) {
                $asOf = $transaction->approved_at
                    ?? $transaction->created_at;

                return $transaction->items->sum(function ($item) use ($asOf) {
                    if ($item->product === null) {
                        return 0;
                    }

                    return (int) $item->quantity
                        * $this->inventoryCostResolver->resolve(
                            $item->product,
                            $asOf
                        );
                });
            });

        $damageLoss = (float) $adjustments
            ->filter(fn (StockAdjustment $item) => $item->adjustment_type === 'damage_loss')
            ->sum(
                fn (StockAdjustment $item) => abs((int) $item->difference)
                    * (float) ($item->unit_cost ?? 0)
            );

        $opnameLoss = (float) $adjustments
            ->filter(fn (StockAdjustment $item) => $item->adjustment_type === 'opname' && $item->difference < 0)
            ->sum(
                fn (StockAdjustment $item) => abs((int) $item->difference)
                    * (float) ($item->unit_cost ?? 0)
            );

        $opnameGain = (float) $adjustments
            ->filter(fn (StockAdjustment $item) => $item->adjustment_type === 'opname' && $item->difference > 0)
            ->sum(
                fn (StockAdjustment $item) => (int) $item->difference
                    * (float) ($item->unit_cost ?? 0)
            );

        return [
            'transactions' => $transactions->count(),
            'incoming_items' => $incomingTransactions
                ->sum(fn (StockTransaction $transaction) => $transaction->items->sum('quantity')),
            'outgoing_items' => $outgoingTransactions
                ->sum(fn (StockTransaction $transaction) => $transaction->items->sum('quantity')),
            'total_value_in' => (float) $incomingTransactions->sum(
                fn (StockTransaction $transaction) => $transaction->items->sum(
                    fn ($item) => (int) $item->quantity * (float) ($item->unit_price ?? 0)
                )
            ),
            'total_value_out' => (float) $outgoingTransactions->sum(
                fn (StockTransaction $transaction) => $transaction->items->sum(
                    fn ($item) => (int) $item->quantity * (float) ($item->unit_price ?? 0)
                )
            ),
            'sales_revenue' => $salesRevenue,
            'sales_cost' => $salesCost,
            'gross_profit' => $salesRevenue - $salesCost,
            'damage_loss' => $damageLoss,
            'opname_loss' => $opnameLoss,
            'opname_gain' => $opnameGain,
            'estimated_profit' => $salesRevenue
                - $salesCost
                - $damageLoss
                - $opnameLoss,
        ];
    }

    private function periodMode(Request $request): string
    {
        $periodMode = (string) $request->get('period_mode', 'all');

        return in_array(
            $periodMode,
            ['all', 'day', 'month', 'year', 'range'],
            true
        )
            ? $periodMode
            : 'all';
    }

    private function periodLabel(Request $request): string
    {
        return match ($this->periodMode($request)) {
            'day' => 'Harian: ' . ($request->period_day ?? '-'),
            'month' => 'Bulanan: ' . ($request->period_month ?? now()->format('Y-m')),
            'year' => 'Tahunan: ' . ($request->period_year ?? now()->year),
            'range' => 'Periode: '
                . ($request->start_date ?? '-')
                . ' s/d '
                . ($request->end_date ?? '-'),
            default => 'Semua Waktu',
        };
    }
}