<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'sale_unit_price',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'sale_unit_price' => 'decimal:2',
    ];

    public function stockTransaction()
    {
        return $this->belongsTo(StockTransaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
