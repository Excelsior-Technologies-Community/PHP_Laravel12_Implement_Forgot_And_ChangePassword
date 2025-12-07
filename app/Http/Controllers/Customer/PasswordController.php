<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

/**
 * PasswordController
 *
 * This controller handles:
 * ✅ Forgot Password (send reset link via email)
 * ✅ Reset Password using token
 * ✅ Change Password (logged-in customer)
 *
 * Works with `customers` table and `password_resets` table
 */
class PasswordController extends Controller
{
    /**
     * Show forgot password form
     *
     * URL  : /customer/forgot-password
     * View : resources/views/customer/auth/forgot-password.blade.php
     */
    public function showForgotForm()
    {
        return view('customer.auth.forgot-password');
    }

    /**
     * Handle forgot password request
     *
     * Steps:
     * 1️⃣ Validate email
     * 2️⃣ Generate reset token
     * 3️⃣ Store token in password_resets table
     * 4️⃣ Send reset password email
     */
    public function sendResetLink(Request $request)
    {
        // Validate email and check if it exists in customers table
        $request->validate([
            'email' => 'required|email|exists:customers,email'
        ]);

        // Generate secure random token
        $token = Str::random(64);

        // Insert or update password reset record
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email'      => $request->email,
                'token'      => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Create password reset URL
        $resetUrl = url('/customer/reset-password/' . $token . '?email=' . $request->email);

        // Send reset password email using Mailable class
        Mail::to($request->email)->send(
            new ResetPasswordMail($resetUrl)
        );

        return back()->with(
            'success',
            'We have emailed your password reset link!'
        );
    }

    /**
     * Show reset password form
     *
     * URL  : /customer/reset-password/{token}
     * View : resources/views/customer/auth/reset-password.blade.php
     */
    public function showResetForm($token, Request $request)
    {
        $email = $request->email;

        return view(
            'customer.auth.reset-password',
            compact('token', 'email')
        );
    }

    /**
     * Handle password reset
     *
     * Steps:
     * 1️⃣ Validate input
     * 2️⃣ Verify reset token
     * 3️⃣ Update customer password
     * 4️⃣ Delete token from database
     */
    public function resetPassword(Request $request)
    {
        // Validate request data
        $request->validate([
            'email'    => 'required|email|exists:customers,email',
            'password' => 'required|min:6|confirmed',
            'token'    => 'required'
        ]);

        // Check token in password_resets table
        $reset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        // If token is invalid
        if (!$reset) {
            return back()->withErrors([
                'email' => 'Invalid or expired reset token'
            ]);
        }

        // Get customer and update password
        $customer = Customer::where('email', $request->email)->first();

        $customer->password = Hash::make($request->password);
        $customer->save();

        // Remove reset token after successful reset
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect()
            ->route('customer.login')
            ->with('success', 'Password reset successfully!');
    }

    /**
     * Show change password form
     * (Only for logged-in customers)
     *
     * URL  : /customer/change-password
     */
    public function showChangeForm()
    {
        return view('customer.auth.change-password');
    }

    /**
     * Handle change password (logged-in customer)
     *
     * Steps:
     * 1️⃣ Validate current & new password
     * 2️⃣ Check current password
     * 3️⃣ Update password securely
     */
    public function changePassword(Request $request)
    {
        // Validate input fields
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors([
                'current_password' => 'Current password does not match'
            ]);
        }

        // Update password
        $customer->password = Hash::make($request->new_password);
        $customer->save();

        return back()->with(
            'success',
            'Password changed successfully!'
        );
    }
}
