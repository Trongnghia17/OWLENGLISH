<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', User::ROLE_STUDENT)->count(),
            'total_assistants' => User::where('role', User::ROLE_ASSISTANT)->count(),
            'total_teachers' => User::where('role', User::ROLE_TEACHER)->count(),
            'total_admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        // Role distribution for charts
        $roleStats = [];
        foreach (User::ROLE_NAMES as $roleValue => $roleName) {
            $roleStats[] = [
                'role' => $roleName,
                'count' => User::where('role', $roleValue)->count()
            ];
        }

        return view('admin.dashboard', compact('stats', 'roleStats'));
    }
}