<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest('created_at')
            ->latest('id');

        /*
        |--------------------------------------------------------------------------
        | Pencarian
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where(
                        'action',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'description',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'subject_type',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhereHas(
                        'user',
                        function ($userQuery) use ($search) {
                            $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter jenis tindakan
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->action
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter tanggal
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->date
            );
        }

        $logs = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Referensi nama untuk detail perubahan
        |--------------------------------------------------------------------------
        |
        | Nilai log menyimpan ID agar jejak audit tetap ringkas. Tampilan tidak
        | boleh memperlihatkan ID teknis tersebut kepada pengguna, sehingga nama
        | pengguna, supplier, dan kategori disiapkan untuk halaman log.
        |
        */

        $referenceIds = [
            'users' => [],
            'suppliers' => [],
            'categories' => [],
        ];

        foreach ($logs as $log) {
            foreach ([$log->old_values, $log->new_values] as $values) {
                if (! is_array($values)) {
                    continue;
                }

                foreach (['approved_by', 'created_by', 'updated_by'] as $key) {
                    if (! empty($values[$key])) {
                        $referenceIds['users'][] = (int) $values[$key];
                    }
                }

                if (! empty($values['supplier_id'])) {
                    $referenceIds['suppliers'][] = (int) $values['supplier_id'];
                }

                if (! empty($values['category_id'])) {
                    $referenceIds['categories'][] = (int) $values['category_id'];
                }
            }
        }

        $referenceNames = [
            'users' => User::query()
                ->whereIn('id', array_unique($referenceIds['users']))
                ->pluck('name', 'id')
                ->all(),

            'suppliers' => Supplier::query()
                ->whereIn('id', array_unique($referenceIds['suppliers']))
                ->pluck('name', 'id')
                ->all(),

            'categories' => Category::query()
                ->whereIn('id', array_unique($referenceIds['categories']))
                ->pluck('name', 'id')
                ->all(),
        ];

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $summary = [
            'total' => ActivityLog::count(),

            'today' => ActivityLog::query()
                ->whereDate('created_at', today())
                ->count(),

            'approvals' => ActivityLog::query()
                ->where(function ($query) {
                    $query
                        ->where('action', 'like', '%.approved')
                        ->orWhere('action', 'like', '%.rejected');
                })
                ->count(),
        ];

        return view(
            'pages.activity-logs.index',
            compact(
                'logs',
                'actions',
                'summary',
                'referenceNames'
            )
        );
    }
}