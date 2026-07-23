<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\PasswordHistory;
use App\Models\ActivityLog;
use App\Models\OtpVerification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

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
        $request->validate([
            'email' => 'required|email|exists:customers,email'
        ]);

        $customer = Customer::where('email', $request->email)->first();

        $existingOtp = OtpVerification::where('customer_id', $customer->id)
            ->where('type', 'forgot_password')
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if ($existingOtp) {
            return redirect()->route('customer.verify.otp', ['email' => $request->email])
                ->with('success', 'OTP already sent to your email. Please check your inbox.');
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpVerification::create([
            'customer_id' => $customer->id,
            'otp' => $otp,
            'type' => 'forgot_password',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($request->email)->send(new OtpMail($otp));

        ActivityLog::create([
            'customer_id' => $customer->id,
            'type' => 'password_reset',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('customer.verify.otp', ['email' => $request->email])
            ->with('success', 'OTP sent to your email!');
    }

    /**
     * Show reset password form
     *
     * URL  : /customer/reset-password/{token}
     * View : resources/views/customer/auth/reset-password.blade.php
     */
    public function showResetForm(Request $request)
    {
        $email = $request->email;
        return view('customer.auth.verify-otp', compact('email'));
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
        $request->validate([
            'email'    => 'required|email|exists:customers,email',
            'otp'      => 'required|size:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        $otpRecord = OtpVerification::where('customer_id', $customer->id)
            ->where('type', 'forgot_password')
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord || $otpRecord->otp !== $request->otp) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP'
            ]);
        }

        $otpRecord->update(['verified_at' => Carbon::now()]);

        $customer->password = Hash::make($request->password);
        $customer->save();

        return redirect()
            ->route('customer.login')
            ->with('success', 'Password reset successfully! Please login.');
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
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        /** @var \App\Models\Customer $customer */
        $customer = Auth::guard('customer')->user();

        if (!Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors([
                'current_password' => 'Current password does not match'
            ]);
        }

        $recentPasswords = $customer->passwordHistories()
            ->orderByDesc('created_at')
            ->limit(5)
            ->pluck('password');

        foreach ($recentPasswords as $oldPassword) {
            if (Hash::check($request->new_password, $oldPassword)) {
                return back()->withErrors([
                    'new_password' => 'You cannot reuse a recent password. Please choose a different one.'
                ]);
            }
        }

        $customer->password = Hash::make($request->new_password);
        $customer->save();

        PasswordHistory::create([
            'customer_id' => $customer->id,
            'password'    => Hash::make($request->new_password),
        ]);

        ActivityLog::create([
            'customer_id' => $customer->id,
            'type' => 'password_change',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => Carbon::now(),
        ]);

        return back()->with(
            'success',
            'Password changed successfully!'
        );
    }
}
