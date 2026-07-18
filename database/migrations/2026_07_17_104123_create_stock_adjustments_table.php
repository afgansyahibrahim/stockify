<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_opname_id')
                ->constrained('stock_opnames')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->integer('difference');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('adjusted_at');

            $table->timestamps();

            $table->unique([
                'stock_opname_id',
                'product_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};