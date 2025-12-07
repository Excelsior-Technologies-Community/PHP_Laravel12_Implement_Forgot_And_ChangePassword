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
    /**
     * TRAITS USED
     */

    // Enables factory support (useful for testing / seeding)
    use HasFactory;

    // Enables soft delete feature (uses deleted_at column)
    use SoftDeletes;

    // Enables password reset functionality for customer
    // Required for Forgot Password feature
    use CanResetPassword;

    /**
     * Mass assignable attributes
     * These fields can be inserted/updated using Customer::create()
     */
    protected $fillable = [
        'name',         // Customer name
        'email',        // Customer email
        'password',     // Encrypted password
        'status',       // active / inactive
        'created_by',   // Admin who created customer
        'updated_by',   // Admin who updated customer
    ];

    /**
     * Hidden attributes
     * These fields will not be shown in JSON responses
     */
    protected $hidden = [
        'password',    // Never expose password
    ];

    /**
     * Guard name
     * This tells Laravel to use the `customer` auth guard
     * instead of default `web` guard
     */
    protected $guard = 'customer';
}
