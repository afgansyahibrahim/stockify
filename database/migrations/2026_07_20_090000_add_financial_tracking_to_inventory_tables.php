<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('outflow_category', 30)
                ->nullable()
                ->after('type');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->decimal('sale_unit_price', 12, 2)
                ->nullable()
                ->after('unit_price');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->string('adjustment_type', 30)
                ->default('opname')
                ->after('status');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->string('adjustment_type', 30)
                ->default('opname')
                ->after('difference');
            $table->decimal('unit_cost', 12, 2)
                ->nullable()
                ->after('adjustment_type');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn(['adjustment_type', 'unit_cost']);
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropColumn('adjustment_type');
        });

        Schema::table('stock_transaction_items', function (Blueprint $table) {
            $table->dropColumn('sale_unit_price');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('outflow_category');
        });
    }
};
