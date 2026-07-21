<?php

namespace App\Console\Commands;

use App\Models\StockTransactionItem;
use App\Services\InventoryCostResolver;
use Illuminate\Console\Command;

class BackfillOutgoingTransactionCosts extends Command
{
    protected $signature = 'stockify:backfill-outgoing-costs
                            {--apply : Terapkan HPP dari Barang Masuk ke transaksi keluar lama}';

    protected $description = 'Memperbaiki HPP transaksi keluar lama yang sebelumnya mengambil harga beli dari master Produk.';

    public function handle(InventoryCostResolver $inventoryCostResolver): int
    {
        $items = StockTransactionItem::query()
            ->with(['product', 'stockTransaction'])
            ->whereHas('stockTransaction', function ($query) {
                $query
                    ->where('type', 'out')
                    ->where('status', 'approved');
            })
            ->orderBy('id')
            ->get();

        if ($items->isEmpty()) {
            $this->info('Tidak ada transaksi keluar yang telah disetujui.');

            return self::SUCCESS;
        }

        $apply = (bool) $this->option('apply');
        $ready = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $product = $item->product;
            $transaction = $item->stockTransaction;

            if ($product === null || $transaction === null) {
                $this->warn("Item #{$item->id} dilewati: produk atau transaksi tidak ditemukan.");
                $skipped++;

                continue;
            }

            $asOf = $transaction->approved_at ?? $transaction->created_at;
            $unitCost = $inventoryCostResolver->resolve($product, $asOf);

            if ($unitCost <= 0) {
                $this->warn(
                    "{$transaction->transaction_code} · {$product->name} dilewati: " .
                    'tidak ditemukan Barang Masuk yang telah disetujui.'
                );
                $skipped++;

                continue;
            }

            $oldCost = (float) ($item->unit_price ?? 0);

            if (abs($oldCost - $unitCost) < 0.005) {
                continue;
            }

            $this->line(
                "{$transaction->transaction_code} · {$product->name} · " .
                'Rp ' . number_format($oldCost, 0, ',', '.') .
                ' → Rp ' . number_format($unitCost, 0, ',', '.')
            );

            $ready++;

            if ($apply) {
                $item->update(['unit_price' => $unitCost]);
                $updated++;
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->warn(
                "Pratinjau selesai: {$ready} item siap diperbaiki, {$skipped} item dilewati."
            );
            $this->line('Tidak ada data yang diubah. Jalankan ulang dengan --apply untuk menerapkan.');

            return self::SUCCESS;
        }

        $this->info("Selesai: {$updated} item HPP diperbaiki, {$skipped} item dilewati.");

        return self::SUCCESS;
    }
}