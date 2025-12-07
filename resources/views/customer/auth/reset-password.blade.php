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
