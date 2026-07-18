<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'sku',
        'description',
        'purchase_price',
        'selling_price',
        'stock',
        'minimum_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(StockTransactionItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
    public function stockTransactionItems()
    {
        return $this->hasMany(
            StockTransactionItem::class
        );
    }

    public function stockOpnameItems()
    {
        return $this->hasMany(
            StockOpnameItem::class
        );
    }

}