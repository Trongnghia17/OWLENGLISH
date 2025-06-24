<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
public function index(Request $request)
{
    $query = User::query();
    
    // Filter by role group if specified
    if ($request->has('role')) {
        $roleParam = $request->get('role');
        
        switch ($roleParam) {
            case 'teacher':
                $query->whereIn('role', [1, 2, 3]); // Teacher roles
                break;
            case 'assistant':
                $query->whereIn('role', [4, 5]); // Assistant roles
                break;
            case 'student':
                $query->whereIn('role', [6, 7]); // Student roles
                break;
            case '0': // Admin role
                $query->where('role', 0);
                break;
        }
    }
    
    // Add search functionality if search term exists
    if ($request->has('search') && $request->search) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('email', 'like', "%{$searchTerm}%");
        });
    }
    
    $users = $query->orderBy('created_at', 'desc')->paginate(15);
    
    // Preserve query parameters in pagination links
    $users->appends($request->query());
    
    return view('admin.users.index', compact('users'));
}

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|integer|in:0,1,2,3,4,5,6,7', // All new roles
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản thành công!');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Kiểm tra quyền sửa vai trò
        if (!auth()->user()->canEditRole()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa vai trò!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|integer|in:0,1,2,3,4,5,6,7', 
            'is_active' => 'boolean',
        ]);

        // Không cho phép admin tự thay đổi vai trò của mình
        if ($user->id === auth()->id() && $request->role != $user->role) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể thay đổi vai trò của chính mình!');
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user)
    {
       

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa tài khoản thành công!');
    }

    public function toggleStatus(User $user)
    {
        // Kiểm tra quyền thay đổi trạng thái
        if (!auth()->user()->canEditRole()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền thay đổi trạng thái tài khoản!');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể thay đổi trạng thái tài khoản của chính mình!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->route('admin.users.index')
            ->with('success', "Đã {$status} tài khoản thành công!");
    }

    public function bulkUpdateRole(Request $request)
    {
        // Kiểm tra quyền
        if (!auth()->user()->canEditRole()) {
            return response()->json(['error' => 'Bạn không có quyền thực hiện thao tác này!'], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'new_role' => 'required|integer|in:0,1,2,3,4,5,6,7',
        ]);

        // Loại bỏ user hiện tại khỏi danh sách
        $userIds = array_filter($request->user_ids, function($id) {
            return $id != auth()->id();
        });

        if (empty($userIds)) {
            return response()->json(['error' => 'Không có user nào để cập nhật!'], 400);
        }

        $updatedCount = User::whereIn('id', $userIds)->update(['role' => $request->new_role]);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật vai trò cho {$updatedCount} tài khoản!",
            'updated_count' => $updatedCount
        ]);
    }

    public function getRoleStats()
    {
        $stats = [];
        $roleGroups = User::getMainRoleGroups();
        
        foreach ($roleGroups as $groupName => $roles) {
            $count = User::whereIn('role', $roles)->where('is_active', true)->count();
            $stats[$groupName] = $count;
        }

        return response()->json($stats);
    }
}