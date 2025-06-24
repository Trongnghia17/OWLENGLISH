@extends('layouts.app')

@section('title', 'Quản lý Tài khoản')

@section('content')
<div class="content">
    <div class="pb-5">

    
        <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">
        @if(request('role') === 'teacher')
            Quản lý Giáo viên
        @elseif(request('role') === 'assistant')
            Quản lý Trợ lý
        @elseif(request('role') === 'student')
            Quản lý Học sinh
        @else
            Quản lý Tài khoản
        @endif
    </h1>
</div>

        <!-- Search and Actions Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="position-relative" style="width: 300px;">
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-5" placeholder="Tìm kiếm thành viên..." id="searchMembers">
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-download me-2"></i>Xuất Excel
                </button>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Thêm tài khoản
                </a>
            </div>
        </div>

    
        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Users Table -->
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="border-0 px-4 py-3">
                                TÀI KHOẢN 
                            </th>
                            <th scope="col" class="border-0 px-4 py-3">
                                EMAIL 
                            </th>
                            <th scope="col" class="border-0 px-4 py-3">
                                VAI TRÒ 
                            </th>
                            <th scope="col" class="border-0 px-4 py-3">
                                TRẠNG THÁI 
                            </th>
                            <th scope="col" class="border-0 px-4 py-3">
                                NGÀY TẠO 
                            </th>
                            <th scope="col" class="border-0 px-4 py-3">
                                THAO TÁC
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-primary text-white d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px; border-radius: 50%; font-weight: 600;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $user->name }}</span>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info ms-2">Bạn</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="mailto:{{ $user->email }}" class="text-primary text-decoration-none">
                                    {{ $user->email }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $roleClass = match($user->role) {
                                        1, 2, 3 => 'bg-success',
                                        4, 5 => 'bg-warning',
                                        6, 7 => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $roleClass }}">{{ $user->role_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($user->is_active)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Vô hiệu hóa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted">
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center ">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary btn-sm " type="button" 
                                            data-bs-toggle="dropdown" aria-expanded="false" 
                                            data-bs-auto-close="outside">
                           <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 160px;">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">
                                                <i class="fas fa-eye me-2 text-primary"></i>Xem chi tiết
                                            </a>
                                        </li>
                                        @if(auth()->user()->canEditRole())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                <i class="fas fa-edit me-2 text-warning"></i>Chỉnh sửa
                                            </a>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" 
                                                  class="d-inline w-100">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item w-100 text-start"
                                                        {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                                        onclick="event.stopPropagation();">
                                                    <i class="fas fa-toggle-{{ $user->is_active ? 'off' : 'on' }} me-2 {{ $user->is_active ? 'text-danger' : 'text-success' }}"></i>
                                                    {{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                                </button>
                                            </form>
                                        </li>
                                        @if($user->id !== auth()->id())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                                  class="d-inline w-100"
                                                  onsubmit="event.stopPropagation(); return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger w-100 text-start"
                                                        onclick="event.stopPropagation();">
                                                    <i class="fas fa-trash me-2"></i>Xóa tài khoản
                                                </button>
                                            </form>
                                        </li>
                                        @endif
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                <p>Chưa có thành viên nào</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Hiển thị {{ $users->firstItem() }} đến {{ $users->lastItem() }} 
                trong tổng số {{ $users->total() }} kết quả
            </div>
            <nav aria-label="Phân trang">
                {{ $users->links('pagination::bootstrap-4') }}
            </nav>
        </div>
        @endif
    </div>
</div>

<style>

</style>

@endsection