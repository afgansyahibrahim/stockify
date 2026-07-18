<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('supplier_id')
                ->nullable()
                ->after('approved_by')
                ->constrained()
                ->nullOnDelete();

            $table->string('reference_number')
                ->nullable()
                ->after('transaction_code');

            $table->string('destination')
                ->nullable()
                ->after('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'supplier_id',
                'reference_number',
                'destination',
            ]);
        });
    }
};