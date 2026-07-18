<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\ActivityLogger;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'supplier',
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
                'suppliers'
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

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

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
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'is_active' => $product->is_active,
            ]
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);

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
            'purchase_price',
            'selling_price',
            'minimum_stock',
            'is_active',
        ]);

        $product->update($validated);

        $product->refresh();

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
                'purchase_price',
                'selling_price',
                'minimum_stock',
                'is_active',
            ])
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
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);
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