<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $teamStats = null;

        if ($user->isAdmin()) {
            $teamStats = DB::table('users')
                ->selectRaw('COUNT(*) AS total_users')
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS active_users', [User::STATUS_ACTIVE])
                ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS inactive_users', [User::STATUS_INACTIVE])
                ->first();
        }

        return view('settings.index', compact('user', 'teamStats'));
    }
}
