<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Role System Definitions:
| 0: Admin - Quản lý tài khoản Admin (highest authority)
| 1: Teacher Teaching - Giáo viên giảng dạy
| 2: Teacher Grading - Giáo viên chấm sửa bài
| 3: Teacher Content - Giáo viên làm đề, chủ đề
| 4: Student Care - Chăm sóc học viên
| 5: Assistant Content - Trợ lý chuyên môn làm đề, chủ đề (supports Teacher Content)
| 6: Student Center - Học viên trung tâm
| 7: Student Visitor - Học viên vãng lai (basic access)
|
| Note: Lower number = Higher permission level
*/

/*
|--------------------------------------------------------------------------
| Home & Default Routes
|--------------------------------------------------------------------------
*/

// Home route - Smart redirect based on user role
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
        }
        // Redirect based on role hierarchy
        switch ($user->role) {
            case 0: // Admin
                return redirect()->route('admin.dashboard');
            case 1:
            case 2:
            case 3: // All Teacher types
                return redirect()->route('teacher.dashboard');
            case 4:
            case 5: // All Assistant types
                return redirect()->route('assistant.dashboard');
            case 6:
            case 7: // All Student types
            default:
                return redirect()->route('student.dashboard');
        }
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Error & Status Pages
|--------------------------------------------------------------------------
*/

Route::prefix('error')->group(function () {
    Route::get('/403', function () {
        return view('errors.403');
    })->name('error.403');

    Route::get('/404', function () {
        return view('errors.404');
    })->name('error.404');

    Route::get('/500', function () {
        return view('errors.500');
    })->name('error.500');
});
/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Guest only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated user routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Role 0 - Highest Authority)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management - Complete CRUD for all users
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

/*
|--------------------------------------------------------------------------
| Teacher Routes (Roles 0, 1, 2, 3)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:0,1,2,3'])->prefix('teacher')->name('teacher.')->group(function () {
    // Teaching Management (Role 1 - Teacher Teaching)
    Route::middleware('role:0,1')->prefix('teaching')->name('teaching.')->group(function () {

    });
    // Grading Management (Role 2 - Teacher Grading)
    Route::middleware('role:0,2')->prefix('grading')->name('grading.')->group(function () {
    });
    // Content Creation (Role 3 - Teacher Content + Role 5 - Assistant Content)
    Route::middleware('role:0,3,5')->prefix('content')->name('content.')->group(function () {

    });
});

/*
|--------------------------------------------------------------------------
| Assistant Routes (Roles 0, 4, 5)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:0,4,5'])->prefix('assistant')->name('assistant.')->group(function () {

    // Student Care Management (Role 4 - Student Care)
    Route::middleware('role:0,4')->prefix('student-care')->name('student_care.')->group(function () {

    });

    Route::middleware('role:0,5')->prefix('content-assist')->name('content_assist.')->group(function () {

    });

});

/*
|--------------------------------------------------------------------------
| Student Routes (Roles 6, 7)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:6,7'])->prefix('student')->name('student.')->group(function () {
    // Center Student Features (Role 6 - Student Center)
    Route::middleware('role:6')->prefix('center')->name('center.')->group(function () {
    });
    // Visitor Student Features (Role 7 - Student Visitor - Basic Access)
    Route::middleware('role:7')->prefix('visitor')->name('visitor.')->group(function () {

    });


});



/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return view('errors.404');
});
