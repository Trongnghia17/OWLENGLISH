<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 0;
    const ROLE_TEACHER = 1;
    const ROLE_ASSISTANT = 2;
    const ROLE_STUDENT = 3;

    const ROLE_NAMES = [
        self::ROLE_ADMIN => 'Quản trị viên',
        self::ROLE_TEACHER => 'Giáo viên',
        self::ROLE_ASSISTANT => 'Trợ giảng',
        self::ROLE_STUDENT => 'Học viên',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => 'integer',
        ];
    }

    // Role checking methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function hasRole(int $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function hasMinimumRole(int $minimumRole): bool
    {
        return $this->role <= $minimumRole; // Đảo ngược logic vì Admin có role nhỏ nhất (0)
    }

    public function getRoleNameAttribute(): string
    {
        return self::ROLE_NAMES[$this->role] ?? 'Không xác định';
    }

    public static function getRoleOptions(): array
    {
        return self::ROLE_NAMES;
    }
}