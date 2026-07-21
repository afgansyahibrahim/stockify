<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogger;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'supplier',
            'attributes',
        ])->latest('created_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    })
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        );
                    });
            });
        }

        if (
            $request->filled('status') &&
            in_array(
                $request->status,
                ['active', 'inactive'],
                true
            )
        ) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $products = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Referensi harga beli terakhir per supplier
        |--------------------------------------------------------------------------
        |
        | Harga berasal dari Barang Masuk yang disetujui, bukan dari harga beli
        | default produk. Hanya harga dari supplier yang masih aktif yang
        | ditampilkan sebagai referensi pembelian saat ini. Riwayat transaksi
        | dan HPP tidak diubah oleh filter tampilan ini.
        |
        */
        $purchaseReferencesByProduct = $this->purchaseReferencesByProduct(
            $products->getCollection()->modelKeys()
        );

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'pages.products.index',
            compact(
                'products',
                'categories',
                'suppliers',
                'purchaseReferencesByProduct'
            )
        );
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('pages.products.product-create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $attributes = $this->normaliseAttributes($validated['attributes'] ?? []);
        unset($validated['attributes']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = DB::transaction(function () use ($validated, $attributes) {
            $product = Product::create($validated);
            $this->syncAttributes($product, $attributes);

            return $product;
        });

        ActivityLogger::log(
            action: 'product.created',

            description:
                "Membuat produk {$product->name}.",

            subject: $product,

            oldValues: null,

            newValues: [
                'name' => $product->name,
                'sku' => $product->sku,
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'stock' => $product->stock,
                'minimum_stock' => $product->minimum_stock,
                'selling_price' => $product->selling_price,
                'is_active' => $product->is_active,
                'attributes' => $attributes,
            ]
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);
        $attributes = $this->normaliseAttributes($validated['attributes'] ?? []);
        unset($validated['attributes']);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $oldValues = $product->only([
            'name',
            'sku',
            'category_id',
            'supplier_id',
            'description',
            'selling_price',
            'minimum_stock',
            'is_active',
        ]);
        $oldValues['attributes'] = $product->attributes()
            ->orderBy('id')
            ->get(['name', 'value'])
            ->map(fn ($attribute) => [
                'name' => $attribute->name,
                'value' => $attribute->value,
            ])
            ->all();

        DB::transaction(function () use ($product, $validated, $attributes) {
            $product->update($validated);
            $this->syncAttributes($product, $attributes);
        });

        $product->refresh()->load('attributes');

        ActivityLogger::log(
            action: 'product.updated',

            description:
                "Mengubah produk {$product->name}.",

            subject: $product,

            oldValues: $oldValues,

            newValues: $product->only([
                'name',
                'sku',
                'category_id',
                'supplier_id',
                'description',
                'selling_price',
                'minimum_stock',
                'is_active',
            ]) + [
                'attributes' => $product->attributes
                    ->sortBy('id')
                    ->map(fn ($attribute) => [
                        'name' => $attribute->name,
                        'value' => $attribute->value,
                    ])
                    ->values()
                    ->all(),
            ]
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
{
    $hasTransactions = $product
        ->stockTransactionItems()
        ->exists();

    $hasOpnameItems = $product
        ->stockOpnameItems()
        ->exists();

    $hasAdjustments = $product
        ->stockAdjustments()
        ->exists();

    if (
        $hasTransactions ||
        $hasOpnameItems ||
        $hasAdjustments
    ) {
        $product->update([
            'is_active' => false,
        ]);

        ActivityLogger::log(
            action: 'product.deactivated',

            description:
                "Menonaktifkan produk {$product->name}.",

            subject: $product,

            oldValues: [
                'is_active' => true,
            ],

            newValues: [
                'is_active' => false,
            ]
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk sudah memiliki riwayat. Produk dinonaktifkan dan tidak dihapus permanen.'
            );
    }

    $productData = $product->only([
        'id',
        'name',
        'sku',
        'category_id',
        'supplier_id',
        'stock',
        'minimum_stock',
        'is_active',
    ]);

    ActivityLogger::log(
        action: 'product.deleted',

        description:
            "Menghapus produk {$product->name}.",

        subject: $product,

        oldValues: $productData,

        newValues: null
    );

    $product->delete();

    return redirect()
        ->route('products.index')
        ->with(
            'success',
            'Produk berhasil dihapus.'
        );
}

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'attributes' => ['nullable', 'array', 'max:20'],
            'attributes.*.name' => ['nullable', 'string', 'max:100'],
            'attributes.*.value' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Bersihkan baris kosong dan cegah nama atribut yang sama.
     *
     * @param array<int, array{name?: mixed, value?: mixed}> $attributes
     * @return array<int, array{name: string, value: string|null}>
     */
    private function normaliseAttributes(array $attributes): array
    {
        $normalised = [];
        $seenNames = [];

        foreach ($attributes as $index => $attribute) {
            $name = trim((string) ($attribute['name'] ?? ''));
            $value = trim((string) ($attribute['value'] ?? ''));

            // Baris yang belum diisi tidak dianggap sebagai atribut.
            if ($name === '' && $value === '') {
                continue;
            }

            if ($name === '') {
                throw ValidationException::withMessages([
                    "attributes.{$index}.name" => 'Nama atribut wajib diisi.',
                ]);
            }

            $normalisedName = mb_strtolower($name);

            if (isset($seenNames[$normalisedName])) {
                throw ValidationException::withMessages([
                    "attributes.{$index}.name" => 'Nama atribut tidak boleh sama dalam satu produk.',
                ]);
            }

            $seenNames[$normalisedName] = true;
            $normalised[] = [
                'name' => $name,
                'value' => $value !== '' ? $value : null,
            ];
        }

        return $normalised;
    }

    /**
     * Atribut tidak memiliki relasi transaksi, sehingga aman disinkronkan
     * ulang saat produk diperbarui.
     *
     * @param array<int, array{name: string, value: string|null}> $attributes
     */
    private function syncAttributes(Product $product, array $attributes): void
    {
        $product->attributes()->delete();

        if ($attributes !== []) {
            $product->attributes()->createMany($attributes);
        }
    }

    /**
     * Ambil harga Barang Masuk yang benar-benar telah disetujui.
     *
     * Query ini sengaja memakai relasi Eloquent, bukan join manual. Dengan
     * demikian, filter status persetujuan dan relasi supplier selalu mengacu
     * pada transaksi yang sama dengan detail item harga tersebut.
     *
     * @param array<int, int> $productIds
     * @return array<int, array<int, array{supplier: string, unit_price: float, date: string|null}>>
     */
    private function purchaseReferencesByProduct(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return StockTransactionItem::query()
            ->whereIn('product_id', $productIds)
            ->whereNotNull('unit_price')
            ->whereHas('stockTransaction', function ($transactionQuery) {
                $transactionQuery
                    ->where('type', 'in')
                    ->where('status', 'approved')
                    ->whereHas('supplier', function ($supplierQuery) {
                        $supplierQuery->where('is_active', true);
                    });
            })
            ->with([
                'stockTransaction:id,supplier_id,transaction_date,approved_at',
                'stockTransaction.supplier:id,name,is_active',
            ])
            ->get([
                'id',
                'stock_transaction_id',
                'product_id',
                'unit_price',
            ])
            ->filter(
                fn (StockTransactionItem $item) =>
                    $item->stockTransaction !== null
            )
            ->groupBy('product_id')
            ->map(function ($items) {
                return $items
                    ->sortByDesc(function (StockTransactionItem $item) {
                        $transaction = $item->stockTransaction;

                        return $transaction->approved_at?->getTimestamp()
                            ?? $transaction->transaction_date?->getTimestamp()
                            ?? 0;
                    })
                    ->unique(
                        fn (StockTransactionItem $item) =>
                            $item->stockTransaction->supplier_id
                    )
                    ->map(function (StockTransactionItem $item) {
                        $transaction = $item->stockTransaction;
                        $referenceDate = $transaction->approved_at
                            ?? $transaction->transaction_date;

                        return [
                            'supplier' => $transaction->supplier?->name
                                ?? 'Supplier tidak tersedia',
                            'unit_price' => (float) $item->unit_price,
                            'date' => $referenceDate?->format('d/m/Y'),
                        ];
                    })
                    ->values()
                    ->all();
            })
            ->all();
    }

    public function activate(Product $product)
    {
        if ($product->is_active) {
            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'Produk tersebut masih aktif.'
                );
        }

        $product->update([
            'is_active' => true,
        ]);

        ActivityLogger::log(
            action: 'product.activated',

            description:
                "Mengaktifkan kembali produk {$product->name}.",

            subject: $product,

            oldValues: [
                'is_active' => false,
            ],

            newValues: [
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil diaktifkan kembali.'
            );
    }
}