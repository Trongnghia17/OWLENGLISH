<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
    

        // Role distribution for charts
        $roleStats = [];
        foreach (User::ROLE_NAMES as $roleValue => $roleName) {
            $roleStats[] = [
                'role' => $roleName,
                'count' => User::where('role', $roleValue)->count()
            ];
        }

        return view('admin.dashboard', compact( 'roleStats'));
    }
}