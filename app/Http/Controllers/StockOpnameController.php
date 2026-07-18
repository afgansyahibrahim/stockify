<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use App\Models\StockAdjustment;
use App\Services\ActivityLogger;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = StockOpname::query();

        /*
        |--------------------------------------------------------------------------
        | Staff hanya dapat mengakses opname miliknya
        |--------------------------------------------------------------------------
        */

        if (Auth::user()->role === 'staff') {
            $baseQuery->where('created_by', Auth::id());
        }

        /*
        |--------------------------------------------------------------------------
        | Ringkasan seluruh data
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total' => (clone $baseQuery)->count(),

            'pending' => (clone $baseQuery)
                ->where('status', 'pending')
                ->count(),

            'approved' => (clone $baseQuery)
                ->where('status', 'approved')
                ->count(),

            'rejected' => (clone $baseQuery)
                ->where('status', 'rejected')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Query daftar opname
        |--------------------------------------------------------------------------
        */

        $query = StockOpname::with([
            'creator',
            'approver',
            'items.product',
        ]);

        if (Auth::user()->role === 'staff') {
            $query->where('created_by', Auth::id());
        }

        /*
        |--------------------------------------------------------------------------
        | Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $keyword = trim((string) $request->search);

            $query->where(function ($subQuery) use ($keyword) {
                $subQuery
                    ->where(
                        'opname_code',
                        'like',
                        '%' . $keyword . '%'
                    )
                    ->orWhereHas(
                        'creator',
                        function ($creatorQuery) use ($keyword) {
                            $creatorQuery->where(
                                'name',
                                'like',
                                '%' . $keyword . '%'
                            );
                        }
                    )
                    ->orWhereHas(
                        'items.product',
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
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter status
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Filter tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'opname_date',
                $request->date
            );
        }

        $opnames = $query
            ->latest('opname_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'pages.stock-opnames.index',
            compact('opnames', 'summary')
        );
    }

    public function create()
    {
        $oldItems = collect(old('items', []));

        $oldProducts = Product::with([
            'category',
            'supplier',
        ])
            ->whereIn(
                'id',
                $oldItems
                    ->pluck('product_id')
                    ->filter()
                    ->values()
            )
            ->get()
            ->keyBy('id');

        $oldSelectedProducts = $oldItems
            ->map(function ($item) use ($oldProducts) {
                $productId = (int) ($item['product_id'] ?? 0);
                $product = $oldProducts->get($productId);

                if (!$product) {
                    return null;
                }

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => (int) $product->stock,
                    'category' => $product->category?->name,
                    'supplier' => $product->supplier?->name,
                    'physical_stock' => (int) (
                        $item['physical_stock']
                        ?? $product->stock
                    ),
                    'notes' => (string) ($item['notes'] ?? ''),
                ];
            })
            ->filter()
            ->values();

        return view(
            'pages.stock-opnames.create',
            compact('oldSelectedProducts')
        );
    }

    public function searchProducts(Request $request)
    {
        $keyword = trim(
            (string) $request->query('q', '')
        );

        if (mb_strlen($keyword) < 1) {
            return response()->json([]);
        }

        $products = Product::with([
            'category:id,name',
            'supplier:id,name',
        ])
            ->where('is_active', true)
            ->where(function ($query) use ($keyword) {
                $query
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
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => (int) $product->stock,
                    'category' =>
                        $product->category?->name,
                    'supplier' =>
                        $product->supplier?->name,
                ];
            })
            ->values();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opname_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'distinct',
                'exists:products,id',
            ],

            'items.*.physical_stock' => [
                'required',
                'integer',
                'min:0',
            ],

            'items.*.notes' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $products = Product::query()
                    ->whereIn(
                        'id',
                        collect($validated['items'])
                            ->pluck('product_id')
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if (
                    $products->count() !==
                    count($validated['items'])
                ) {
                    throw new RuntimeException(
                        'Sebagian produk tidak ditemukan.'
                    );
                }

                $opname = StockOpname::create([
                    'opname_code' =>
                        $this->generateOpnameCode(),

                    'status' => 'pending',

                    'opname_date' =>
                        $validated['opname_date'],

                    'created_by' => Auth::id(),

                    'approved_by' => null,

                    'approved_at' => null,

                    'notes' =>
                        $validated['notes'] ?? null,

                    'rejection_note' => null,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products->get(
                        $item['product_id']
                    );

                    if (!$product) {
                        throw new RuntimeException(
                            'Produk pada stock opname tidak ditemukan.'
                        );
                    }

                    $physicalStock = (int)
                        $item['physical_stock'];

                    $systemStock = (int)
                        $product->stock;

                    $opname->items()->create([
                        'product_id' => $product->id,

                        'system_stock' => $systemStock,

                        'physical_stock' => $physicalStock,

                        'difference' =>
                            $physicalStock - $systemStock,

                        'notes' =>
                            $item['notes'] ?? null,
                    ]);
                }

                ActivityLogger::log(
                    action: 'stock_opname.created',

                    description:
                        "Membuat stock opname {$opname->opname_code}.",

                    subject: $opname,

                    oldValues: null,

                    newValues: [
                        'status' => $opname->status,
                        'opname_date' => $opname->opname_date,
                        'total_products' =>
                            count($validated['items']),
                    ]
                );
            });

            return redirect()
                ->route('stock-opnames.index')
                ->with(
                    'success',
                    'Stock opname berhasil dikirim untuk persetujuan.'
                );
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    $exception->getMessage()
                );
        }
    }

    public function approve(StockOpname $stockOpname)
{
    try {
        DB::transaction(function () use ($stockOpname) {
            $opname = StockOpname::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($stockOpname->id);

            if ($opname->status !== 'pending') {
                throw new RuntimeException(
                    'Stock opname ini sudah diproses sebelumnya.'
                );
            }

            if ($opname->created_by === Auth::id()) {
                throw new RuntimeException(
                    'Pembuat stock opname tidak dapat menyetujui pengajuannya sendiri.'
                );
            }

            if ($opname->items->isEmpty()) {
                throw new RuntimeException(
                    'Stock opname tidak memiliki detail produk.'
                );
            }

            foreach ($opname->items as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item->product_id);

                $currentStock = (int) $product->stock;
                $recordedSystemStock = (int) $item->system_stock;
                $physicalStock = (int) $item->physical_stock;

                /*
                |--------------------------------------------------------------------------
                | Cegah opname lama menimpa transaksi terbaru
                |--------------------------------------------------------------------------
                */

                if ($currentStock !== $recordedSystemStock) {
                    throw new RuntimeException(
                        "Stok produk {$product->name} sudah berubah sejak opname dibuat. " .
                        "Stok saat opname: {$recordedSystemStock}, " .
                        "stok sekarang: {$currentStock}. " .
                        "Silakan buat stock opname baru."
                    );
                }

                $difference = $physicalStock - $currentStock;

                /*
                |--------------------------------------------------------------------------
                | Simpan jejak penyesuaian
                |--------------------------------------------------------------------------
                */

                if ($difference !== 0) {
                    StockAdjustment::create([
                        'stock_opname_id' => $opname->id,
                        'product_id' => $product->id,
                        'stock_before' => $currentStock,
                        'stock_after' => $physicalStock,
                        'difference' => $difference,
                        'approved_by' => Auth::id(),
                        'adjusted_at' => now(),
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Perbarui stok produk
                |--------------------------------------------------------------------------
                */

                $product->update([
                    'stock' => $physicalStock,
                ]);
            }

            $opname->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_note' => null,
            ]);
            ActivityLogger::log(
                action: 'stock_opname.approved',

                description:
                    "Menyetujui stock opname {$opname->opname_code}.",

                subject: $opname,

                oldValues: [
                    'status' => 'pending',
                ],

                newValues: [
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                ]
            );            
                    });

        return redirect()
            ->route('stock-opnames.index')
            ->with(
                'success',
                'Stock opname berhasil disetujui dan stok telah disesuaikan.'
            );
    } catch (Throwable $error) {
        report($error);

        return redirect()
            ->route('stock-opnames.index')
            ->with(
                'error',
                $error instanceof RuntimeException
                    ? $error->getMessage()
                    : 'Stock opname gagal disetujui.'
            );
    }
}

    public function reject(
        Request $request,
        StockOpname $stockOpname
    ) {
        $validated = $request->validate([
            'rejection_note' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        try {
            DB::transaction(function () use (
                $stockOpname,
                $validated
            ) {
                $opname = StockOpname::query()
                    ->lockForUpdate()
                    ->findOrFail($stockOpname->id);

                if ($opname->status !== 'pending') {
                    throw new RuntimeException(
                        'Stock opname ini sudah diproses sebelumnya.'
                    );
                }

                if ($opname->created_by === Auth::id()) {
                    throw new RuntimeException(
                        'Pembuat stock opname tidak dapat menolak pengajuannya sendiri.'
                    );
                }

                $opname->update([
                    'status' => 'rejected',

                    'approved_by' => Auth::id(),

                    'approved_at' => now(),

                    'rejection_note' =>
                        $validated['rejection_note'],
                ]);
                ActivityLogger::log(
                    action: 'stock_opname.rejected',

                    description:
                        "Menolak stock opname {$opname->opname_code}.",

                    subject: $opname,

                    oldValues: [
                        'status' => 'pending',
                    ],

                    newValues: [
                        'status' => 'rejected',
                        'rejection_note' =>
                            $validated['rejection_note'],
                        'approved_by' => Auth::id(),
                    ]
                );                
            });

            return back()->with(
                'success',
                'Stock opname berhasil ditolak.'
            );
        } catch (Throwable $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        try {
            DB::transaction(function () use ($stockOpname) {
                $opname = StockOpname::query()
                    ->lockForUpdate()
                    ->findOrFail($stockOpname->id);

                /*
                |--------------------------------------------------------------------------
                | Opname approved tidak boleh dihapus
                |--------------------------------------------------------------------------
                */

                if ($opname->status === 'approved') {
                    throw new RuntimeException(
                        'Stock opname yang sudah disetujui tidak dapat dihapus.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Staff hanya boleh menghapus opname miliknya
                |--------------------------------------------------------------------------
                */

                if (
                    Auth::user()->role === 'staff' &&
                    $opname->created_by !== Auth::id()
                ) {
                    abort(
                        403,
                        'Anda tidak memiliki akses untuk menghapus stock opname ini.'
                    );
                }

                $opname->delete();
            });

            return back()->with(
                'success',
                'Stock opname berhasil dihapus.'
            );
        } catch (Throwable $exception) {
            return back()->with(
                'error',
                $exception->getMessage()
            );
        }
    }

    private function generateOpnameCode(): string
    {
        $prefix = 'OPN-'
            . now()->format('Ymd')
            . '-';

        $lastOpname = StockOpname::query()
            ->where(
                'opname_code',
                'like',
                $prefix . '%'
            )
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastOpname) {
            $lastNumber = (int) substr(
                $lastOpname->opname_code,
                strrpos(
                    $lastOpname->opname_code,
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