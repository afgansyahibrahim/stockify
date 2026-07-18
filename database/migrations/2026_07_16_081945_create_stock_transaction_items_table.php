<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transaction_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transaction_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->restrictOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['stock_transaction_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_items');
    }
};