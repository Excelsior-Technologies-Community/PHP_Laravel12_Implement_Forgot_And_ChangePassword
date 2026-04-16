<?php

namespace App\Http\Controllers\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $key = Str::lower($request->email) . '|' . $request->ip();

        // 🔒 Check attempts (3 tries)
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors([
                'email' => "Too many login attempts. Try again after 300 seconds."
            ]);
        }

        // Attempt login using customer guard
        if (Auth::guard('customer')->attempt($request->only('email', 'password'))) {
            RateLimiter::clear($key); // reset attempts
            return redirect()->route('customer.dashboard');
        }

        // Failed login → lock for 5 minutes
        RateLimiter::hit($key, 300);

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
        Auth::guard('customer')->logout();

        return redirect()
            ->route('customer.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
