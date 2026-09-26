<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = null;
        $recentUsers = collect();

        if (auth()->user()->isAdmin()) {
            $stats = DB::table('users')
                ->selectRaw('COUNT(*) AS total_users')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS active_users', [User::STATUS_ACTIVE])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS inactive_users', [User::STATUS_INACTIVE])
                ->selectRaw('SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) AS admin_users', [User::ROLE_ADMIN])
                ->selectRaw('SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) AS regular_users', [User::ROLE_USER])
                ->first();

            $recentUsers = User::query()
                ->select(['id', 'name', 'email', 'profile_image', 'role', 'status', 'created_at'])
                ->latest('created_at')
                ->latest('id')
                ->limit(6)
                ->get();
        }

        return view('dashboard', compact('stats', 'recentUsers'));
    }
}
