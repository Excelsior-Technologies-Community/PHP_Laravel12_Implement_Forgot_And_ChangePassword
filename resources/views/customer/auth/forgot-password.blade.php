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
                Send OTP
            </button>
        </form>

        <!-- Link to go back to login page -->
        <div class="text-center mt-5">
            <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>

</body>
</html>
