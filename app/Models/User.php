<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public const STATUS = [
        'active' => 'Active',
        'on_leave' => 'On Leave',
        'resigned' => 'Resigned',
    ];
    public const ROLES = [
        'admin' => 'Admin',
        'employee' => 'Employee',
        'manager' => 'Manager',
    ];
    protected $fillable = [
        'name',
        'avatar',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
        'job_title',
        'job_description',
        'employee_id',
        'status',
        'extension_number',
        'joining_date',
        'role',
        'department_id',
        'shift_id',
    ];
    public const STATUS_ACTIVE = [
        'active' => 'Active',
        'on_leave' => 'On Leave',
        'resigned' => 'Resigned',
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
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

}
