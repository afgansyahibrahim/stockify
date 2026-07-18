<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StockAdjustment;
use App\Services\ActivityLogger;

class StockMutationController extends Controller
{
    public function stockIn()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.mutations.stock-in', compact('products', 'suppliers'));
    }

    public function stockOut()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('pages.mutations.stock-out', compact('products'));
    }

    public function storeIn(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
            'required',
            'distinct',
            'exists:products,id',
        ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated) {
            $transaction = StockTransaction::create([
                'transaction_code' => $this->generateTransactionCode('in'),
                'type' => 'in',
                'status' => 'pending',
                'created_by' => Auth::id(),
                'supplier_id' => $validated['supplier_id'],
                'reference_number' => $validated['reference_number'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $transaction->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? null,
                ]);
            }
            ActivityLogger::log(
                action: 'transaction.created',

                description:
                    "Membuat pengajuan barang masuk {$transaction->transaction_code}.",

                subject: $transaction,

                oldValues: null,

                newValues: [
                    'type' => 'in',
                    'status' => 'pending',
                    'supplier_id' => $transaction->supplier_id,
                    'transaction_date' => $transaction->transaction_date,
                    'total_products' => count($validated['items']),
                    'total_quantity' => collect($validated['items'])
                        ->sum('quantity'),
                ]
            );
        });

        return redirect()
            ->route('stock.in')
            ->with('success', 'Pengajuan barang masuk berhasil dikirim dan menunggu persetujuan.');
    }

    public function storeOut(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => ['required', 'date'],
            'destination' => ['required', 'string', 'max:150'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
            'required',
            'distinct',
            'exists:products,id',
        ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($item['quantity'] > $product->stock) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'items' => "Jumlah keluar untuk produk {$product->name} melebihi stok yang tersedia.",
                    ]);
            }
        }

        DB::transaction(function () use ($validated) {
    $transaction = StockTransaction::create([
        'transaction_code' =>
            $this->generateTransactionCode('out'),

        'type' => 'out',
        'status' => 'pending',
        'created_by' => Auth::id(),

        'destination' =>
            $validated['destination'],

        'reference_number' =>
            $validated['reference_number'] ?? null,

        'transaction_date' =>
            $validated['transaction_date'],

        'notes' =>
            $validated['notes'] ?? null,
    ]);

    $products = Product::query()
        ->whereIn(
            'id',
            collect($validated['items'])
                ->pluck('product_id')
        )
        ->get()
        ->keyBy('id');

    foreach ($validated['items'] as $item) {
        $product = $products->get(
            $item['product_id']
        );

        if (!$product) {
            throw new \RuntimeException(
                'Produk barang keluar tidak ditemukan.'
            );
        }

        $transaction->items()->create([
            'product_id' => $product->id,
            'quantity' => (int) $item['quantity'],
            'unit_price' => (float) ($product->purchase_price ?? 0),
        ]);
    }
    ActivityLogger::log(
        action: 'transaction.created',

        description:
            "Membuat pengajuan barang keluar {$transaction->transaction_code}.",

        subject: $transaction,

        oldValues: null,

        newValues: [
            'type' => 'out',
            'status' => 'pending',
            'destination' => $transaction->destination,
            'transaction_date' => $transaction->transaction_date,
            'total_products' => count($validated['items']),
            'total_quantity' => collect($validated['items'])
                ->sum('quantity'),
        ]
    );
});

        return redirect()
            ->route('stock.out')
            ->with('success', 'Pengajuan barang keluar berhasil dikirim dan menunggu persetujuan.');
    }

   public function history(Request $request)
{
    $query = StockTransaction::with([
        'creator',
        'approver',
        'supplier',
        'items.product',
    ])
        ->latest('transaction_date')
        ->latest('id');

    /*
    |--------------------------------------------------------------------------
    | Staff hanya melihat transaksi yang telah disetujui
    |--------------------------------------------------------------------------
    */

    if (Auth::user()->role === 'staff') {
        $query->where('status', 'approved');
    }

    /*
    |--------------------------------------------------------------------------
    | Pencarian transaksi
    |--------------------------------------------------------------------------
    */

    if ($request->filled('search')) {
        $search = trim((string) $request->search);

        $query->where(function ($subQuery) use ($search) {
            $subQuery
                ->where(
                    'transaction_code',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'reference_number',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhere(
                    'destination',
                    'like',
                    '%' . $search . '%'
                )
                ->orWhereHas(
                    'supplier',
                    function ($supplierQuery) use ($search) {
                        $supplierQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                )
                ->orWhereHas(
                    'creator',
                    function ($userQuery) use ($search) {
                        $userQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                )
                ->orWhereHas(
                    'items.product',
                    function ($productQuery) use ($search) {
                        $productQuery
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'sku',
                                'like',
                                '%' . $search . '%'
                            );
                    }
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Filter jenis transaksi
    |--------------------------------------------------------------------------
    */

    if (
        $request->filled('type') &&
        in_array($request->type, ['in', 'out'], true)
    ) {
        $query->where('type', $request->type);
    }

    /*
    |--------------------------------------------------------------------------
    | Filter status
    |--------------------------------------------------------------------------
    */

    if (
        Auth::user()->role !== 'staff' &&
        $request->filled('status') &&
        in_array(
            $request->status,
            ['pending', 'approved', 'rejected'],
            true
        )
    ) {
        $query->where('status', $request->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Filter periode
    |--------------------------------------------------------------------------
    */

    $periodMode = $request->get('period_mode', 'all');

    if (
        $periodMode === 'day' &&
        $request->filled('period_day')
    ) {
        $query->whereDate(
            'transaction_date',
            $request->period_day
        );
    }

    if (
        $periodMode === 'month' &&
        $request->filled('period_month')
    ) {
        [$year, $month] = explode(
            '-',
            $request->period_month
        );

        $query
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month);
    }

    if (
        $periodMode === 'year' &&
        $request->filled('period_year')
    ) {
        $query->whereYear(
            'transaction_date',
            $request->period_year
        );
    }

    if ($periodMode === 'range') {
        if ($request->filled('start_date')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->start_date
            );
        }

        if ($request->filled('end_date')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->end_date
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Ringkasan transaksi
    |--------------------------------------------------------------------------
    */

    $filteredTransactions = (clone $query)->get();

    $summary = [
        'total' => $filteredTransactions->count(),

        'pending' => $filteredTransactions
            ->where('status', 'pending')
            ->count(),

        'incoming' => $filteredTransactions
            ->where('type', 'in')
            ->where('status', 'approved')
            ->sum(
                fn ($transaction) =>
                    $transaction->items->sum('quantity')
            ),

        'outgoing' => $filteredTransactions
            ->where('type', 'out')
            ->where('status', 'approved')
            ->sum(
                fn ($transaction) =>
                    $transaction->items->sum('quantity')
            ),

        'total_value_in' => $filteredTransactions
            ->where('type', 'in')
            ->where('status', 'approved')
            ->sum(
                fn ($transaction) =>
                    $transaction->items->sum(
                        fn ($item) =>
                            $item->quantity *
                            ($item->unit_price ?? 0)
                    )
            ),

        'total_value_out' => $filteredTransactions
            ->where('type', 'out')
            ->where('status', 'approved')
            ->sum(
                fn ($transaction) =>
                    $transaction->items->sum(
                        fn ($item) =>
                            $item->quantity *
                            ($item->unit_price ?? 0)
                    )
            ),
    ];

    /*
    |--------------------------------------------------------------------------
    | Pagination transaksi
    |--------------------------------------------------------------------------
    */

    $transactions = $query
        ->paginate(10, ['*'], 'transaction_page')
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | Riwayat penyesuaian Stock Opname
    |--------------------------------------------------------------------------
    */

    $adjustmentQuery = StockAdjustment::with([
        'product',
        'stockOpname.creator',
        'approver',
    ])
        ->latest('adjusted_at')
        ->latest('id');

    if ($request->filled('search')) {
        $keyword = trim((string) $request->search);

        $adjustmentQuery->where(
            function ($subQuery) use ($keyword) {
                $subQuery
                    ->whereHas(
                        'product',
                        function ($productQuery) use ($keyword) {
                            $productQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $keyword . '%'
                                )
                                ->orWhere(
                                    'sku',
                                    'like',
                                    '%' . $keyword . '%'
                                );
                        }
                    )
                    ->orWhereHas(
                        'stockOpname',
                        function ($opnameQuery) use ($keyword) {
                            $opnameQuery->where(
                                'opname_code',
                                'like',
                                '%' . $keyword . '%'
                            );
                        }
                    )
                    ->orWhereHas(
                        'approver',
                        function ($approverQuery) use ($keyword) {
                            $approverQuery->where(
                                'name',
                                'like',
                                '%' . $keyword . '%'
                            );
                        }
                    );
            }
        );
    }

    if (
        $periodMode === 'day' &&
        $request->filled('period_day')
    ) {
        $adjustmentQuery->whereDate(
            'adjusted_at',
            $request->period_day
        );
    }

    if (
        $periodMode === 'month' &&
        $request->filled('period_month')
    ) {
        [$year, $month] = explode(
            '-',
            $request->period_month
        );

        $adjustmentQuery
            ->whereYear('adjusted_at', $year)
            ->whereMonth('adjusted_at', $month);
    }

    if (
        $periodMode === 'year' &&
        $request->filled('period_year')
    ) {
        $adjustmentQuery->whereYear(
            'adjusted_at',
            $request->period_year
        );
    }

    if ($periodMode === 'range') {
        if ($request->filled('start_date')) {
            $adjustmentQuery->whereDate(
                'adjusted_at',
                '>=',
                $request->start_date
            );
        }

        if ($request->filled('end_date')) {
            $adjustmentQuery->whereDate(
                'adjusted_at',
                '<=',
                $request->end_date
            );
        }
    }

    $adjustments = $adjustmentQuery
        ->paginate(10, ['*'], 'adjustment_page')
        ->withQueryString();

    return view(
        'pages.mutations.history',
        compact(
            'transactions',
            'adjustments',
            'summary',
            'periodMode'
        )
    );
}
    public function myHistory(Request $request)
{
    $query = StockTransaction::with([
        'supplier',
        'approver',
        'items.product',
    ])
        ->where('created_by', auth()->id())
        ->latest('transaction_date')
        ->latest('id');

    if (
        $request->filled('type') &&
        in_array($request->type, ['in', 'out'], true)
    ) {
        $query->where('type', $request->type);
    }

    if (
        $request->filled('status') &&
        in_array(
            $request->status,
            ['pending', 'approved', 'rejected'],
            true
        )
    ) {
        $query->where('status', $request->status);
    }

    $transactions = $query
        ->paginate(10)
        ->withQueryString();

    return view(
        'pages.mutations.my-history',
        compact('transactions')
    );
}

private function generateTransactionCode(string $type): string
{
    if (!in_array($type, ['in', 'out'], true)) {
        throw new \InvalidArgumentException(
            'Jenis transaksi tidak valid.'
        );
    }

    $prefix = strtoupper($type)
        . '-'
        . now()->format('Ymd')
        . '-';

    $lastTransaction = StockTransaction::where(
        'transaction_code',
        'like',
        $prefix . '%'
    )
        ->latest('id')
        ->first();

    $nextNumber = 1;

    if ($lastTransaction) {
        $lastNumber = (int) substr(
            $lastTransaction->transaction_code,
            strrpos(
                $lastTransaction->transaction_code,
                '-'
            ) + 1
        );

        $nextNumber = $lastNumber + 1;
    }

    return $prefix . str_pad(
        (string) $nextNumber,
        3,
        '0',
        STR_PAD_LEFT
    );
}

    
}