<x-layouts.app>
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Super Admin Panel</h1>
        
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button 
                    class="tab-button py-2 px-1 border-b-2 font-medium text-sm active"
                    data-tab="statistics"
                >
                    Statistics
                </button>
                <button 
                    class="tab-button py-2 px-1 border-b-2 font-medium text-sm"
                    data-tab="audit-log"
                >
                    Audit Log
                </button>
            </nav>
        </div>

        <!-- Statistics Tab -->
        <div id="statistics-tab" class="tab-content">
            <h2 class="text-2xl font-semibold mb-4">Platform Statistics</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800">Total Users</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $statistics['total_users'] }}</p>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">Memory Verses</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $statistics['total_memory_verses'] }}</p>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800">Memorize Later</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ $statistics['total_memorize_later'] }}</p>
                </div>
                
                <div class="bg-purple-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800">Audit Logs</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $statistics['total_audit_logs'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Users -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Recent Users</h3>
                    <div class="space-y-2">
                        @foreach($statistics['recent_users'] as $user)
                            <div class="flex justify-between items-center bg-white p-3 rounded">
                                <div>
                                    <span class="font-medium">{{ $user->name }}</span>
                                    <span class="text-gray-500 text-sm">({{ $user->email }})</span>
                                </div>
                                <span class="text-gray-400 text-sm">{{ $user->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Analytics Placeholder -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Quick Analytics</h3>
                    <div class="space-y-4">
                        <div class="bg-white p-3 rounded">
                            <span class="text-gray-600">Active Users (30 days):</span>
                            <span class="font-bold text-right float-right">Coming Soon</span>
                        </div>
                        <div class="bg-white p-3 rounded">
                            <span class="text-gray-600">Popular Verses:</span>
                            <span class="font-bold text-right float-right">Coming Soon</span>
                        </div>
                        <div class="bg-white p-3 rounded">
                            <span class="text-gray-600">Completion Rate:</span>
                            <span class="font-bold text-right float-right">Coming Soon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Log Tab -->
        <div id="audit-log-tab" class="tab-content hidden">
            <h2 class="text-2xl font-semibold mb-4">Audit Log</h2>
            
            <div class="bg-gray-50 p-6 rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">Time</th>
                                <th class="px-4 py-2 text-left">User</th>
                                <th class="px-4 py-2 text-left">Action</th>
                                <th class="px-4 py-2 text-left">Table</th>
                                <th class="px-4 py-2 text-left">Record ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($statistics['recent_audit_logs'] as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm">{{ $log->created_at->format('M j, Y H:i') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $log->user ? $log->user->name : 'System' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($log->action === 'created') bg-green-100 text-green-800
                                            @elseif($log->action === 'updated') bg-blue-100 text-blue-800
                                            @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm font-mono">{{ $log->table_name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $log->record_id }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-gray-500 text-sm">Showing recent 10 entries. Full audit log pagination coming soon.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');

                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
                        btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    });

                    // Add active class to clicked button
                    this.classList.add('active', 'border-blue-500', 'text-blue-600');
                    this.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');

                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Show target tab content
                    document.getElementById(targetTab + '-tab').classList.remove('hidden');
                });
            });

            // Set initial active state
            tabButtons[0].classList.add('border-blue-500', 'text-blue-600');
            tabButtons[0].classList.remove('border-transparent', 'text-gray-500');
        });
    </script>

    <style>
        .tab-button {
            @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
        }
        .tab-button.active {
            @apply border-blue-500 text-blue-600;
        }
    </style>
</x-layouts.app>
