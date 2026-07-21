<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransactionItem;
use Carbon\CarbonInterface;

class InventoryCostResolver
{
    /**
     * Mengambil harga beli terakhir dari Barang Masuk yang sudah disetujui.
     *
     * Harga Produk tidak pernah dipakai sebagai cadangan HPP. Harga beli
     * aktual hanya berasal dari transaksi Barang Masuk yang telah disetujui.
     */
    public function resolve(Product $product, ?CarbonInterface $asOf = null): float
    {
        $query = StockTransactionItem::query()
            ->join(
                'stock_transactions',
                'stock_transaction_items.stock_transaction_id',
                '=',
                'stock_transactions.id'
            )
            ->where('stock_transaction_items.product_id', $product->id)
            ->where('stock_transactions.type', 'in')
            ->where('stock_transactions.status', 'approved')
            ->whereNotNull('stock_transaction_items.unit_price')
            ->where('stock_transaction_items.unit_price', '>', 0);

        if ($asOf !== null) {
            $query->where('stock_transactions.approved_at', '<=', $asOf);
        }

        $latestIncomingCost = $query
            ->orderByDesc('stock_transactions.approved_at')
            ->orderByDesc('stock_transaction_items.id')
            ->value('stock_transaction_items.unit_price');

        return $latestIncomingCost !== null
            ? max(0, (float) $latestIncomingCost)
            : 0.0;
    }
}