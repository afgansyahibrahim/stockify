<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
                'summary'
            )
        );
    }
}