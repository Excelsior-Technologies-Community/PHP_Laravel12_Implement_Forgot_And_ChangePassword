<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
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
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',
        ]);

        // Create customer record
        Customer::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password), // Encrypt password
            'status'     => 'active',
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
        // Get only email and password from request
        $credentials = $request->only('email', 'password');

        // Attempt login using customer guard
        if (Auth::guard('customer')->attempt($credentials)) {
            // Login successful → Redirect to dashboard
            return redirect()->route('customer.dashboard');
        }

        // Login failed → Back with error
        return back()->withErrors([
            'email' => 'Invalid email or password',
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
