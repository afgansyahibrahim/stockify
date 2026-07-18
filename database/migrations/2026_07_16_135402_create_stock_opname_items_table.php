<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_opname_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            // Stok sistem saat opname dibuat
            $table->integer('system_stock');

            // Stok hasil hitung fisik
            $table->integer('physical_stock');

            // physical_stock - system_stock
            $table->integer('difference');

            $table->string('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['stock_opname_id', 'product_id'],
                'stock_opname_product_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};