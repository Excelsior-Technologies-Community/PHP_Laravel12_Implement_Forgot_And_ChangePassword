# PHP_Laravel12_Implement_Forgot_And_ChangePassword
---

## Introduction

The main purpose of this project is to implement **Forgot Password** and **Change Password** functionality for a custom Customer authentication system in Laravel 12.

To properly demonstrate and test these features, we also include basic authentication flows such as Customer Registration, Login, Logout, and Dashboard.

---

## Features Included

- Customer Registration (supporting feature)  
- Customer Login / Logout (supporting feature)  
- Customer Dashboard (supporting feature)  
- Forgot Password (Email Reset Link)  (Main Feature)  
- Reset Password using Token  (Main Feature)  
- Change Password (Logged-in Customer)  (Main Feature)  

This project is:  

- Simple and clean  
- Perfect for freshers  
- Interview-focused  
- Based on real-world Laravel authentication logic  

We reuse concepts from Laravel 11, but implement everything properly in Laravel 12 with a new project name and clean structure.

---

##  Project Structure Overview

```bash

PHP_Laravel12_Implement_Forgot_And_ChangePassword
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   └── Customer
│   │   │       ├── AuthController.php
│   │   │       └── PasswordController.php
│   ├── Mail
│   │   └── ResetPasswordMail.php
│   └── Models
│       └── Customer.php
│
├── database
│   ├── migrations
│   │   └── 2025_xx_xx_create_customers_table.php
│          └── (customers + password_resets table)
│   │
├── resources
│   └── views
│       ├── customer
│       │   └── auth
│       │       ├── login.blade.php
│       │       ├── register.blade.php
│       │       ├── dashboard.blade.php
│       │       ├── forgot-password.blade.php
│       │       ├── reset-password.blade.php
│       │       └── change-password.blade.php
│       └── emails
│           └── reset-password.blade.php
│
├── routes
│   └── web.php
└── .env


```

##  Main Features

```bash


### 1️. Forgot Password (Customer)

Flow:

Customer forgets password

→ Enters email address

→ System generates reset token

→ Reset link sent via email

→ Customer creates new password



- Token is stored securely

- Email-based verification

- Works only for valid customers  

### 2️. Change Password (Customer)

Flow:

Customer logs in

→ Enters current password

→ Enters new password

→ System validates current password

→ Password updated securely


- Current password validation

- Secure hashing

- Only accessible when logged in  


```
##  Step 1: Create Laravel 12 Project
```bash

composer create-project laravel/laravel PHP_Laravel12_Implement_Forgot_And_ChangePassword "12.*"
cd PHP_Laravel12_Implement_Forgot_And_ChangePassword

```
## Step 2: Configure Database (.env)
```

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forget_change_password
DB_USERNAME=root
DB_PASSWORD=

```
Create database manually in phpMyAdmin: 
```

forget_change_password
 
```
otherwise
```
php artisan migrate

```
## Step 3: Migration

This migration creates TWO tables: customers and password_resets.

```
php artisan make:migration create_customers_table
```

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration class
 * This file is responsible for creating database tables
 * when we run: php artisan migrate
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * This method creates tables in the database.
     */
    public function up(): void
    {
        /**
         * ===============================
         * CUSTOMERS TABLE
         * ===============================
         * This table stores customer details
         * for authentication (Register, Login, Change Password)
         */
        Schema::create('customers', function (Blueprint $table) {

            // Auto-increment primary key (customer ID)
            $table->id();

            // Customer full name
            $table->string('name');

            // Customer email address (must be unique)
            // Used for login and forgot password
            $table->string('email')->unique();

            // Encrypted (hashed) password
            // Never store plain text password
            $table->string('password');

            // Customer account status
            // active  → can login
            // inactive → blocked
            $table->enum('status', ['active', 'inactive'])->default('active');

            /**
             * created_by
             * Stores the admin (users table) who created this customer
             * Nullable because customer can also self-register
             */
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            /**
             * updated_by
             * Stores the admin who last updated customer record
             */
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // created_at & updated_at columns
            $table->timestamps();

            /**
             * Soft delete column
             * When customer is deleted, record stays in DB
             * deleted_at column is filled instead of hard delete
             */
            $table->softDeletes();
        });

        /**
         * ===============================
         * PASSWORD RESETS TABLE
         * ===============================
         * This table is used for Forgot Password feature
         * It stores password reset token temporarily
         */
        Schema::create('password_resets', function (Blueprint $table) {

            // Customer email requesting password reset
            $table->string('email')->index();

            // Random token sent in reset password link
            $table->string('token');

            // Token creation time
            // Used to expire/reset old tokens if needed
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     * This method deletes tables if we rollback migration.
     */
    public function down(): void
    {
        // Drop password_resets table first (dependency safe)
        Schema::dropIfExists('password_resets');

        // Drop customers table
        Schema::dropIfExists('customers');
    }
};

```
Run migration:
```
php artisan migrate

```
Database tables created:

customers
password_resets

## Step 4: Customer Model

```
php artisan make:model Customer

```

```
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

```
## Step 5: Authentication Guard (Customer)
```

config/auth.php

'guards' => [
    'customer' => [
        'driver' => 'session',
        'provider' => 'customers',
    ],
],

'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Customer::class,
    ],
],

```
## Step 6: Create Controllers
```

php artisan make:controller Customer/AuthController
php artisan make:controller Customer/PasswordController

```


AuthController.php → Handles Register, Login, Dashboard, Logout
 
PasswordController.php → Handles Forgot Password, Reset Password, Change Password

AuthController (Register / Login / Dashboard / Logout)
---

File: app/Http/Controllers/Customer/AuthController.php
```
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

```
PasswordController
---

File: app/Http/Controllers/Customer/PasswordController.php
 
- Forgot Password

 Validates customer email
 Generates token
 Stores token in database
 Sends reset email

- Reset Password

 Validates token
 Updates password
 Deletes token

- Change Password

 Current password validation
 Secure password update
```
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

```
## Step 7: Views
---

We need to create all Blade files for customer auth.

#### 1️) register.blade.php
---

File: resources/views/customer/auth/register.blade.php

This is the Customer Registration page using Tailwind CSS for styling.

It handles displaying validation errors and success messages.

The form sends a POST request to customer.register.submit and includes CSRF protection.

```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Main container centered vertically & horizontally -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Customer Register</h2>

        <!-- Display validation errors if any -->
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Display success message if registration was successful -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Registration form -->
        <form action="{{ route('customer.register.submit') }}" method="POST" class="space-y-5">
            @csrf  <!-- CSRF token for security -->

            <!-- Name field -->
            <div>
                <label class="block text-gray-700 mb-1" for="name">Name</label>
                <input type="text" name="name" id="name" placeholder="Enter your name"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Email field -->
            <div>
                <label class="block text-gray-700 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Password field -->
            <div>
                <label class="block text-gray-700 mb-1" for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Confirm password field -->
            <div>
                <label class="block text-gray-700 mb-1" for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm your password"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Submit button -->
            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Register
            </button>
        </form>

        <!-- Link to login page -->
        <div class="text-center mt-5">
            <p class="text-gray-600">Already have an account? 
                <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
```

#### 2️) login.blade.php
---

File: resources/views/customer/auth/login.blade.php

This is the Customer Login page styled with Tailwind CSS.

It shows validation errors, success messages, and includes a Forgot Password link.

The form posts to customer.login.submit with CSRF protection.
```

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Main container centered on the screen -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <!-- Page heading -->
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Customer Login</h2>

        <!-- Display validation errors if login fails -->
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <!-- Display success message if redirected after registration or reset -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <!-- Login form -->
        <form action="{{ route('customer.login.submit') }}" method="POST" class="space-y-5">
            @csrf  <!-- CSRF token for security -->

            <!-- Email input -->
            <div>
                <label class="block text-gray-700 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Password input -->
            <div>
                <label class="block text-gray-700 mb-1" for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <!-- Forgot password link -->
                <div class="text-right mt-1">
                    <a href="{{ route('customer.forgot') }}" class="text-blue-600 hover:underline text-sm">Forgot Password?</a>
                </div>
            </div>

            <!-- Submit button -->
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Login
            </button>
        </form>

        <!-- Link to registration page -->
        <div class="text-center mt-5">
            <p class="text-gray-600">Don't have an account? 
                <a href="{{ route('customer.register') }}" class="text-blue-600 hover:underline">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
```
#### 3️) dashboard.blade.php
---

File: resources/views/customer/auth/dashboard.blade.php

This is the Customer Dashboard page, showing a welcome message with the logged-in customer’s name.

It provides links to Change Password and Logout, ensuring easy navigation.

The layout is simple, responsive, and styled with Tailwind CSS.
```

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Navigation bar -->
    <nav class="bg-blue-600 p-4 text-white flex justify-between">
        <!-- Dashboard title -->
        <h1 class="font-bold text-lg">Customer Dashboard</h1>

        <!-- Navigation links -->
        <div>
            <!-- Link to Change Password page -->
            <a href="{{ route('customer.change') }}" class="mr-4 hover:underline">Change Password</a>
            <!-- Logout link -->
            <a href="{{ route('customer.logout') }}" class="hover:underline">Logout</a>
        </div>
    </nav>

    <!-- Main content area -->
    <div class="flex-grow flex items-center justify-center">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-lg text-center">
            <!-- Greeting with customer name -->
            <h2 class="text-2xl font-bold mb-4">Welcome, {{ Auth::guard('customer')->user()->name }}!</h2>
            <!-- Dashboard message -->
            <p class="text-gray-700">This is your dashboard. You can change your password or logout from here.</p>
        </div>
    </div>

</body>
</html>
```
#### 4️) forgot-password.blade.php
---

File: resources/views/customer/auth/forgot-password.blade.php

This page allows the customer to enter their email to request a password reset link.

It shows validation errors or a success message when the reset email is sent.

The layout is responsive and clean, using Tailwind CSS.
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Main form container -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <!-- Page title -->
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Forgot Password</h2>

        <!-- Display validation errors -->
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <!-- Display success message (e.g., email sent) -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <!-- Forgot password form -->
        <form action="{{ route('customer.forgot.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <!-- Email input field -->
                <label class="block text-gray-700 mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Submit button -->
            <button type="submit"
                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Send Reset Link
            </button>
        </form>

        <!-- Link to go back to login page -->
        <div class="text-center mt-5">
            <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>

</body>
</html>

```
#### 5️) reset-password.blade.php
---

File: resources/views/customer/auth/reset-password.blade.php

This page allows the customer to enter a new password and confirm it using the token sent via email.

Hidden inputs ensure the token and email are submitted securely for verification.

Shows validation errors or success messages, styled with Tailwind CSS for a clean look.
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Main container for reset password form -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <!-- Page title -->
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Reset Password</h2>

        <!-- Display validation errors -->
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <!-- Display success message if password is reset -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <!-- Reset password form -->
        <form action="{{ route('customer.reset.submit') }}" method="POST" class="space-y-5">
            @csrf
            <!-- Hidden inputs to pass token and email from link -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <!-- New password input -->
                <label class="block text-gray-700 mb-1" for="password">New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <!-- Confirm password input -->
                <label class="block text-gray-700 mb-1" for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm new password"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Submit button -->
            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Reset Password
            </button>
        </form>

        <!-- Back to login link -->
        <div class="text-center mt-5">
            <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>

</body>
</html>
```

#### 6️) change-password.blade.php
---

File: resources/views/customer/auth/change-password.blade.php

This page allows the customer to change their current password by entering the current and new password with confirmation.

Shows validation errors or success messages to guide the user.

Uses Tailwind CSS for a clean, responsive, and modern UI.
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <!-- Main container for change password form -->
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <!-- Page title -->
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Change Password</h2>

        <!-- Display validation errors -->
        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <!-- Display success message if password is changed -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <!-- Change password form -->
        <form action="{{ route('customer.change.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <!-- Current password input -->
                <label class="block text-gray-700 mb-1" for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" placeholder="Enter current password"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <!-- New password input -->
                <label class="block text-gray-700 mb-1" for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter new password"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
                <!-- Confirm new password input -->
                <label class="block text-gray-700 mb-1" for="new_password_confirmation">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm new password"
                    class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <!-- Submit button -->
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Change Password
            </button>
        </form>

        <!-- Back to dashboard link -->
        <div class="text-center mt-5">
            <a href="{{ route('customer.dashboard') }}" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>

</body>
</html>

```
## Step 8: Mail Class

```
php artisan make:mail ResetPasswordMail
```

FIle: app/Mail/ResetPasswordMail.php
```
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * ResetPasswordMail
 *
 * This Mailable class is responsible for:
 * ✅ Sending reset password email to customer
 * ✅ Passing reset URL to email view
 *
 * Used in PasswordController
 */
class ResetPasswordMail extends Mailable
{
    /**
     * Allows the email to be queued (optional)
     * Improves performance for large applications
     */
    use Queueable;

    /**
     * Converts models into arrays when queueing emails
     */
    use SerializesModels;

    /**
     * Public variable accessible inside Blade email view
     *
     * Example usage in blade:
     * {{ $resetUrl }}
     */
    public $resetUrl;

    /**
     * Constructor
     *
     * Receives reset password URL from controller
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the email
     *
     * - Sets email subject
     * - Defines which blade file is used for email content
     */
    public function build()
    {
        return $this->subject('Reset Password Notification')
                    ->view('emails.reset-password');
    }
}


```
Mail Configuration (IMPORTANT)
.env
```

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="Customer App"

```
## Step 9: Email View (Professional)

resources/views/emails/reset-password.blade.php

This email template sends a password reset link to the customer’s email.

Includes a clear header, instructions, a reset button, and a security notice.

Styled with inline CSS and tables to ensure proper display across all email clients.
```

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color: #f5f6fa;">

    <!-- 
        Main container table
        Centers the email content and limits width for better readability
        Adds background color, rounded corners, and subtle shadow
    -->
    <table align="center" cellpadding="0" cellspacing="0" width="100%" 
           style="max-width:600px; margin:50px auto; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);">

        <!-- Header Section -->
        <tr>
            <td style="padding: 30px; text-align: center; background-color: #4f46e5; color: #ffffff; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <!-- Title of the email -->
                <h1 style="margin:0; font-size:24px;">Reset Your Password</h1>
            </td>
        </tr>

        <!-- Body Section -->
        <tr>
            <td style="padding: 30px; color: #333333; line-height: 1.6; font-size: 16px;">
                
                <!-- Greeting -->
                <p>Hello,</p>

                <!-- Instruction -->
                <p>We received a request to reset your password. Click the button below to reset it:</p>

                <!-- Reset Password Button -->
                <p style="text-align:center; margin:30px 0;">
                    <a href="{{ $resetUrl }}" 
                       style="background-color:#4f46e5; color:#ffffff; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;">
                       Reset Password
                    </a>
                </p>

                <!-- Security Notice -->
                <p>If you did not request a password reset, you can safely ignore this email.</p>

                <!-- Closing / Signature -->
                <p style="margin-top:30px;">Thanks,<br>
                   <strong>{{ config('app.name') }}</strong>
                </p>
            </td>
        </tr>

        <!-- Footer Section -->
        <tr>
            <td style="padding: 20px; text-align: center; font-size: 12px; color: #888888; background-color: #f5f6fa; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                <!-- Footer text -->
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
        </tr>

    </table>

</body>
</html>

```
## Step 10: Routes (routes/web.php)
```

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

    // Send password reset email
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->name('customer.forgot.submit');

    // Show reset password form (via token)
    Route::get('/reset-password/{token}', [PasswordController::class, 'showResetForm'])
        ->name('customer.reset');

    // Handle reset password submission
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])
        ->name('customer.reset.submit');
});

```
# Output:
---

For Customer Register:
---

```
http://127.0.0.1:8000/customer/register
```
<img width="1914" height="1088" alt="Screenshot 2025-12-13 134346" src="https://github.com/user-attachments/assets/bf598add-e17d-42b3-8ea8-ec30267b6b6d" />

For Customer Login:
---

```
http://127.0.0.1:8000/customer/login
```
<img width="1914" height="1088" alt="Screenshot 2025-12-13 134410" src="https://github.com/user-attachments/assets/a80edc40-9e7f-4b13-8a3d-8278bc07cdbc" />

For Customer Dashboard:
---

```
http://127.0.0.1:8000/customer/dashboard
```
<img width="1911" height="1085" alt="Screenshot 2025-12-13 134424" src="https://github.com/user-attachments/assets/4cebe203-b0e3-4cf3-81d2-388b03c2e0f4" />

For Change Password:
---

```
http://127.0.0.1:8000/customer/change-password
```
<img width="1919" height="1086" alt="Screenshot 2025-12-13 134509" src="https://github.com/user-attachments/assets/45e9c438-329b-42f2-845c-73d988247dc3" />

<img width="1919" height="1092" alt="Screenshot 2025-12-13 134524" src="https://github.com/user-attachments/assets/2c441b5b-f2ad-43be-bab5-4fb865aa4b39" />

For Forgot Password:
---

```
http://127.0.0.1:8000/customer/forgot-password
```

<img width="1919" height="1092" alt="Screenshot 2025-12-13 134616" src="https://github.com/user-attachments/assets/12e5e2de-c5f1-4868-9bef-e54fa5da632a" />

<img width="1172" height="743" alt="Screenshot 2025-12-13 134710" src="https://github.com/user-attachments/assets/6c207e27-8fee-475f-95a9-6f437a00e46d" />

<img width="1917" height="1088" alt="Screenshot 2025-12-13 134745" src="https://github.com/user-attachments/assets/78a66fab-42d6-4091-a741-1ca762ed9559" />

---
Final Result
---


Fully working customer authentication
Forgot password with email
Secure change password
Laravel 12 compliant

Project Complete

Your PHP_Laravel12_Implement_Forgot_And_ChangePassword project is now fully implemented!

