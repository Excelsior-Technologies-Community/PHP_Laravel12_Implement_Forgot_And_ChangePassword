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
