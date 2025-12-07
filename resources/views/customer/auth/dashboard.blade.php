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
