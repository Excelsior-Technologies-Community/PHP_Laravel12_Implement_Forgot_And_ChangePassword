<?php

namespace App\Http\Controllers\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\OtpVerification;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

/**
 * Customer AuthController
 *
 * This controller handles:
 * ✅ Customer Registration
 * ✅ Customer Login
 * ✅ Customer Dashboard
 * ✅ Customer Logout
 *
 * Authentication is done using `customer` guard
 */
class AuthController extends Controller
{
    /**
     * Show customer registration form
     *
     * URL  : /customer/register
     * View : resources/views/customer/auth/register.blade.php
     */
    public function showRegisterForm()
    {
        return view('customer.auth.register');
    }

    /**
     * Handle customer registration
     *
     * Validates input
     * Encrypts password
     * Stores customer in database
     */
    public function register(Request $request)
    {
        // Validate incoming form data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Create customer record
        Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encrypt password
            'status' => 'active',
            'created_by' => null, // Admin not used in this simple project
        ]);

        // Redirect to login page with success message
        return redirect()
            ->route('customer.login')
            ->with('success', 'Registration successful! Please login.');
    }

    /**
     * Show customer login form
     *
     * URL  : /customer/login
     * View : resources/views/customer/auth/login.blade.php
     */
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    /**
     * Handle customer login
     *
     * Uses Auth::guard('customer')
     */
    public function login(Request $request)
    {
        if ($request->has('login_otp')) {
            $request->validate([
                'login_otp' => 'required|size:6',
            ]);

            if (!session('login_otp') || !session('login_customer_id')) {
                return redirect()->route('customer.login')->withErrors(['email' => 'Session expired. Please login again.']);
            }

            if ($request->login_otp !== session('login_otp')) {
                return back()->withErrors(['login_otp' => 'Invalid OTP']);
            }

            $otpRecord = OtpVerification::where('customer_id', session('login_customer_id'))
                ->where('type', 'login')
                ->whereNull('verified_at')
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            if (!$otpRecord) {
                return redirect()->route('customer.login')->withErrors(['email' => 'OTP expired. Please login again.']);
            }

            $otpRecord->update(['verified_at' => Carbon::now()]);

            Auth::guard('customer')->loginUsingId(session('login_customer_id'));

            ActivityLog::create([
                'customer_id' => session('login_customer_id'),
                'type' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            session()->forget(['login_otp', 'login_customer_id', 'login_email']);

            return redirect()->route('customer.dashboard');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if ($customer && $customer->lockout_until && Carbon::now()->lessThan($customer->lockout_until)) {
            $remaining = Carbon::now()->diffInMinutes($customer->lockout_until);
            return back()->withErrors([
                'email' => "Account locked. Try again after {$remaining} minutes."
            ]);
        }

        $key = Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            if ($customer) {
                $customer->update([
                    'failed_attempts' => ($customer->failed_attempts ?? 0) + 1,
                    'lockout_until' => Carbon::now()->addMinutes(5),
                ]);
            }

            return back()->withErrors([
                'email' => "Too many login attempts. Account locked for 5 minutes."
            ]);
        }

        if (Auth::guard('customer')->attempt($request->only('email', 'password'))) {
            RateLimiter::clear($key);

            if ($customer) {
                $customer->update([
                    'failed_attempts' => 0,
                    'lockout_until' => null,
                ]);
            }

            Auth::guard('customer')->logout();

            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            OtpVerification::create([
                'customer_id' => $customer->id,
                'otp' => $otp,
                'type' => 'login',
                'expires_at' => Carbon::now()->addMinutes(10),
            ]);

            Mail::to($customer->email)->send(new OtpMail($otp));

            ActivityLog::create([
                'customer_id' => $customer->id,
                'type' => 'login',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => Carbon::now(),
            ]);

            $request->session()->put([
                'login_otp' => $otp,
                'login_customer_id' => $customer->id,
                'login_email' => $customer->email,
            ]);

            return redirect()->route('customer.login.otp');
        }

        RateLimiter::hit($key, 300);

        if ($customer) {
            $customer->update([
                'failed_attempts' => ($customer->failed_attempts ?? 0) + 1,
            ]);
        }

        return back()->withErrors([
            'email' => 'Invalid credentials'
        ]);
    }

    /**
     * Customer dashboard
     *
     * Accessible only after login
     */
    public function dashboard()
    {
        return view('customer.auth.dashboard');
    }

    /**
     * Customer logout
     *
     * Clears customer session
     */
    public function logout()
    {
        $customerId = Auth::guard('customer')->id();

        if ($customerId) {
            ActivityLog::create([
                'customer_id' => $customerId,
                'type' => 'logout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => Carbon::now(),
            ]);
        }

        Auth::guard('customer')->logout();

        return redirect()
            ->route('customer.login')
            ->with('success', 'You have been logged out successfully.');
    }

    public function activity(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $activities = $customer->activityLogs()->latest()->paginate(15);
        return view('customer.auth.activity', compact('activities'));
    }

    public function showLoginOtpForm()
    {
        if (!session('login_email')) {
            return redirect()->route('customer.login')->withErrors(['email' => 'Please login first.']);
        }

        return view('customer.auth.login-otp');
    }

    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'login_otp' => 'required|size:6',
        ]);

        if (!session('login_otp') || !session('login_customer_id')) {
            return redirect()->route('customer.login')->withErrors(['email' => 'Session expired. Please login again.']);
        }

        if ($request->login_otp !== session('login_otp')) {
            return back()->withErrors(['login_otp' => 'Invalid OTP']);
        }

        $otpRecord = OtpVerification::where('customer_id', session('login_customer_id'))
            ->where('type', 'login')
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return redirect()->route('customer.login')->withErrors(['email' => 'OTP expired. Please login again.']);
        }

        $otpRecord->update(['verified_at' => Carbon::now()]);

        Auth::guard('customer')->loginUsingId(session('login_customer_id'));

        ActivityLog::create([
            'customer_id' => session('login_customer_id'),
            'type' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => Carbon::now(),
        ]);

        session()->forget(['login_otp', 'login_customer_id', 'login_email']);

        return redirect()->route('customer.dashboard');
    }
}
