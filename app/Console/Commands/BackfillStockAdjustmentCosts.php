<?php

namespace App\Console\Commands;

use App\Models\StockAdjustment;
use App\Services\InventoryCostResolver;
use Illuminate\Console\Command;

class BackfillStockAdjustmentCosts extends Command
{
    protected $signature = 'stockify:backfill-adjustment-costs
                            {--apply : Terapkan biaya pada adjustment lama yang bernilai Rp0}';

    protected $description = 'Isi biaya adjustment lama dari harga Barang Masuk terakhir yang telah disetujui.';

    public function handle(InventoryCostResolver $inventoryCostResolver): int
    {
        $adjustments = StockAdjustment::query()
            ->with('product')
            ->where(function ($query) {
                $query->whereNull('unit_cost')
                    ->orWhere('unit_cost', '<=', 0);
            })
            ->orderBy('id')
            ->get();

        if ($adjustments->isEmpty()) {
            $this->info('Tidak ada adjustment dengan biaya Rp0.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;
        $apply = (bool) $this->option('apply');

        foreach ($adjustments as $adjustment) {
            if ($adjustment->product === null) {
                $this->warn("Adjustment #{$adjustment->id} dilewati: produk tidak ditemukan.");
                $skipped++;

                continue;
            }

            $unitCost = $inventoryCostResolver->resolve(
                $adjustment->product,
                $adjustment->adjusted_at
            );

            if ($unitCost <= 0) {
                $this->warn(
                    "Adjustment #{$adjustment->id} ({$adjustment->product->name}) dilewati: " .
                    'tidak ada harga beli yang dapat dijadikan acuan.'
                );
                $skipped++;

                continue;
            }

            $this->line(
                "Adjustment #{$adjustment->id} · {$adjustment->product->name} · " .
                'biaya: Rp ' . number_format($unitCost, 2, ',', '.')
            );

            if ($apply) {
                $adjustment->update(['unit_cost' => $unitCost]);
            }

            $updated++;
        }

        if (! $apply) {
            $this->newLine();
            $this->warn(
                "Pratinjau selesai: {$updated} adjustment siap diperbaiki, {$skipped} dilewati."
            );
            $this->line('Tidak ada data yang diubah. Jalankan ulang dengan --apply untuk menerapkan.');

            return self::SUCCESS;
        }

        $this->info("Selesai: {$updated} adjustment diperbaiki, {$skipped} dilewati.");

        return self::SUCCESS;
    }
}