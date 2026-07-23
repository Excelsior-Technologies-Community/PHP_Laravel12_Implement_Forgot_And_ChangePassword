<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Activity</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-blue-600 p-4 text-white flex justify-between">
        <h1 class="font-bold text-lg">Security Activity</h1>
        <div>
            <a href="{{ route('customer.change') }}" class="mr-4 hover:underline">Change Password</a>
            <a href="{{ route('customer.dashboard') }}" class="mr-4 hover:underline">Dashboard</a>
            <a href="{{ route('customer.logout') }}" class="hover:underline">Logout</a>
        </div>
    </nav>

    <div class="flex-grow p-8">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold mb-6">Login & Password Activity History</h2>
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-3">Type</th>
                        <th class="p-3">IP Address</th>
                        <th class="p-3">Device</th>
                        <th class="p-3">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($activities as $activity)
                    <tr class="border-b">
                        <td class="p-3">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</td>
                        <td class="p-3">{{ $activity->ip_address ?? '-' }}</td>
                        <td class="p-3 text-sm">{{ Str::limit($activity->user_agent, 40) ?? '-' }}</td>
                        <td class="p-3">{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
                @if($activities->isEmpty())
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">No activity found.</td>
                    </tr>
                @endif
                </tbody>
            </table>
            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</body>
</html>
