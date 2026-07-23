<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;

/**
 * Customer Model
 *
 * This model represents the `customers` table.
 * It is used for:
 * ✅ Customer Register
 * ✅ Customer Login
 * ✅ Forgot Password
 * ✅ Reset Password
 * ✅ Change Password
 */
class Customer extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use CanResetPassword;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'created_by',
        'updated_by',
        'failed_attempts',
        'lockout_until',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'lockout_until' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    protected $guard = 'customer';

    public function passwordHistories()
    {
        return $this->hasMany(\App\Models\PasswordHistory::class);
    }

    public function otpVerifications()
    {
        return $this->hasMany(\App\Models\OtpVerification::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(\App\Models\ActivityLog::class);
    }
}
