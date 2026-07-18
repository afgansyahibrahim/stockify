<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();

            $table->enum('type', ['in', 'out']);
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('transaction_date');
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};