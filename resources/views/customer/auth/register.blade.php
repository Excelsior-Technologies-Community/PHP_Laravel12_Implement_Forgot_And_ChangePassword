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
