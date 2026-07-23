<?php

use Illuminate\Support\Facades\Route;

/**
 * Import Customer Controllers
 * - AuthController → Register, Login, Dashboard, Logout
 * - PasswordController → Change, Forgot & Reset Password
 */
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\PasswordController;

/**
 * Default Laravel welcome page
 * Accessible at: http://localhost:8000
 */
Route::get('/', function () {
    return view('welcome');
});

/**
 * ========================================================
 * CUSTOMER AUTHENTICATION ROUTES
 * ========================================================
 *
 * Prefix: /customer
 * Example URLs:
 * - /customer/register
 * - /customer/login
 * - /customer/dashboard
 */
Route::prefix('customer')->group(function () {

    /**
     * ----------------------------------------------------
     * Register Routes
     * ----------------------------------------------------
     */

    // Show customer registration form
    Route::get('/register', [AuthController::class, 'showRegisterForm'])
        ->name('customer.register');

    // Handle customer registration form submission
    Route::post('/register', [AuthController::class, 'register'])
        ->name('customer.register.submit');

    /**
     * ----------------------------------------------------
     * Login Routes
     * ----------------------------------------------------
     */

    // Show customer login form
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('customer.login');

    // Show login OTP form
    Route::get('/login-otp', [AuthController::class, 'showLoginOtpForm'])
        ->name('customer.login.otp');

    // Verify login OTP
    Route::post('/login-otp', [AuthController::class, 'verifyLoginOtp'])
        ->name('customer.login.otp.verify');

    // Handle customer login form submission
    Route::post('/login', [AuthController::class, 'login'])
        ->name('customer.login.submit');

    /**
     * ----------------------------------------------------
     * Protected Routes (Only Logged-in Customers)
     * ----------------------------------------------------
     *
     * Middleware: auth:customer
     * Prevents access without login
     */
    Route::middleware('auth:customer')->group(function () {

        // Customer dashboard after successful login
        Route::get('/dashboard', [AuthController::class, 'dashboard'])
            ->name('customer.dashboard');

        // Customer logout
        Route::get('/logout', [AuthController::class, 'logout'])
            ->name('customer.logout');

        /**
         * -------------------------------
         * Change Password (Logged-in)
         * -------------------------------
         */

        // Show change password form
        Route::get('/change-password', [PasswordController::class, 'showChangeForm'])
            ->name('customer.change');

        // Handle change password submission
        Route::post('/change-password', [PasswordController::class, 'changePassword'])
            ->name('customer.change.submit');
    });

    /**
     * ----------------------------------------------------
     * Forgot & Reset Password (Without Login)
     * ----------------------------------------------------
     */

    // Show forgot password form (email input)
    Route::get('/forgot-password', [PasswordController::class, 'showForgotForm'])
        ->name('customer.forgot');

    // Handle forgot password form submission
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->name('customer.forgot.submit');

    // Show OTP verification form
    Route::get('/verify-otp', [PasswordController::class, 'showResetForm'])
        ->name('customer.verify.otp');

    // Handle OTP verification and password reset
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])
        ->name('customer.reset.submit');

    Route::get('/activity', [AuthController::class, 'activity'])
        ->name('customer.activity');
});
