<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use App\Services\ActivityLogger;

class ApprovalController extends Controller
{
    public function index()
    {
        $transactions = StockTransaction::with([
            'creator',
            'approver',
            'supplier',
            'items.product',
        ])
            ->latest('transaction_date')
            ->latest('id')
            ->get();

        return view(
            'pages.approvals.index',
            compact('transactions')
        );
    }

    public function approve(StockTransaction $transaction)
    {
        try {
            DB::transaction(function () use ($transaction) {
                $lockedTransaction = StockTransaction::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);

                if ($lockedTransaction->status !== 'pending') {
                    throw new RuntimeException(
                        'Transaksi ini sudah diproses sebelumnya.'
                    );
                }

                if ($lockedTransaction->created_by === Auth::id()) {
                    throw new RuntimeException(
                        'Pembuat transaksi tidak dapat menyetujui pengajuannya sendiri.'
                    );
                }

                if ($lockedTransaction->items->isEmpty()) {
                    throw new RuntimeException(
                        'Transaksi tidak memiliki detail produk.'
                    );
                }

                foreach ($lockedTransaction->items as $item) {
                    $product = Product::query()
                        ->lockForUpdate()
                        ->findOrFail($item->product_id);

                    $quantity = (int) $item->quantity;
                    $currentStock = (int) $product->stock;

                    if ($lockedTransaction->type === 'in') {
                        $product->update([
                            'stock' => $currentStock + $quantity,
                        ]);

                        continue;
                    }

                    if ($lockedTransaction->type === 'out') {
                        if ($quantity > $currentStock) {
                            throw new RuntimeException(
                                "Stok produk {$product->name} tidak mencukupi. " .
                                "Stok tersedia {$currentStock}, kebutuhan {$quantity}."
                            );
                        }

                        $product->update([
                            'stock' => $currentStock - $quantity,
                        ]);

                        continue;
                    }

                    throw new RuntimeException(
                        'Jenis transaksi tidak valid.'
                    );
                }

                $lockedTransaction->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'rejection_note' => null,
                ]);
                ActivityLogger::log(
                    action: 'transaction.approved',

                    description:
                        "Menyetujui transaksi {$lockedTransaction->transaction_code}.",

                    subject: $lockedTransaction,

                    oldValues: [
                        'status' => 'pending',
                    ],

                    newValues: [
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                    ]
                );
            });

            return redirect()
                ->route('approvals.index')
                ->with(
                    'success',
                    'Transaksi berhasil disetujui dan stok telah diperbarui.'
                );
        } catch (Throwable $error) {
            report($error);

            return redirect()
                ->route('approvals.index')
                ->with(
                    'error',
                    $error instanceof RuntimeException
                        ? $error->getMessage()
                        : 'Transaksi gagal disetujui.'
                );
        }
    }

    public function reject(
    Request $request,
    StockTransaction $transaction
) {
    $validated = $request->validate([
        'rejection_note' => [
            'required',
            'string',
            'max:1000',
        ],
    ]);

    try {
        DB::transaction(function () use (
            $transaction,
            $validated
        ) {
            $lockedTransaction = StockTransaction::query()
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($lockedTransaction->status !== 'pending') {
                throw new RuntimeException(
                    'Transaksi ini sudah diproses sebelumnya.'
                );
            }

            if ($lockedTransaction->created_by === Auth::id()) {
                throw new RuntimeException(
                    'Pembuat transaksi tidak dapat menolak pengajuannya sendiri.'
                );
            }

            $lockedTransaction->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_note' =>
                    $validated['rejection_note'],
            ]);
            ActivityLogger::log(
            action: 'transaction.rejected',

            description:
                "Menolak transaksi {$lockedTransaction->transaction_code}.",

            subject: $lockedTransaction,

            oldValues: [
                'status' => 'pending',
            ],

            newValues: [
                'status' => 'rejected',
                'rejection_note' =>
                    $validated['rejection_note'],
                'approved_by' => Auth::id(),
            ]
        );
        });

        return redirect()
            ->route('approvals.index')
            ->with(
                'success',
                'Transaksi berhasil ditolak.'
            );
    } catch (Throwable $error) {
        report($error);

        return redirect()
            ->route('approvals.index')
            ->with(
                'error',
                $error instanceof RuntimeException
                    ? $error->getMessage()
                    : 'Transaksi gagal ditolak.'
            );
    }
}
}