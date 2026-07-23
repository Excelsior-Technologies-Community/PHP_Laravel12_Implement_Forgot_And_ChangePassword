<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Login OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg w-full max-w-md p-8">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Verify OTP</h2>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <p class="text-center text-gray-600 mb-4">Enter the 6-digit OTP sent to your email.</p>

        <form action="{{ route('customer.login.otp.verify') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-gray-700 mb-1">OTP</label>
                <input type="text" name="login_otp" maxlength="6" required
                    class="w-full border border-gray-300 rounded px-4 py-2 text-center text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition duration-200">
                Verify & Login
            </button>
        </form>

        <div class="text-center mt-5">
            <a href="{{ route('customer.login') }}" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>
</body>
</html>
