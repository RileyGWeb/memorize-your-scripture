<x-layouts.app>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
                    <h1 class="text-3xl font-bold text-gray-900">Super Admin Panel</h1>
                    <p class="mt-1 text-sm text-gray-500">Comprehensive dashboard for managing your scripture memorization platform</p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-sm border mb-8">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button 
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm active"
                            data-tab="dashboard"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Dashboard</span>
                            </div>
                        </button>
                        <button 
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm"
                            data-tab="analytics"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Analytics</span>
                            </div>
                        </button>
                        <button 
                            class="tab-button py-4 px-1 border-b-2 font-medium text-sm"
                            data-tab="audit-log"
                        >
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Audit Log</span>
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Dashboard Tab -->
                <div id="dashboard-tab" class="tab-content p-6">
                    <!-- Key Metrics Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl cursor-pointer hover:from-blue-100 hover:to-blue-200 transition-all duration-200 shadow-sm border border-blue-200" 
                             onclick="showAllUsers()">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-blue-800">Total Users</h3>
                                    <p class="text-3xl font-bold text-blue-600 mt-1">{{ $statistics['total_users'] }}</p>
                                    <p class="text-xs text-blue-500 mt-2">Click to view all</p>
                                </div>
                                <div class="text-blue-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-sm border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-green-800">Memory Verses</h3>
                                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $statistics['total_memory_verses'] }}</p>
                                </div>
                                <div class="text-green-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl shadow-sm border border-yellow-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-yellow-800">Memorize Later</h3>
                                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $statistics['total_memorize_later'] }}</p>
                                </div>
                                <div class="text-yellow-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl shadow-sm border border-purple-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-purple-800">Audit Logs</h3>
                                    <p class="text-3xl font-bold text-purple-600 mt-1">{{ $statistics['total_audit_logs'] }}</p>
                                </div>
                                <div class="text-purple-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-xl shadow-sm border border-indigo-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-indigo-800">Active (7d)</h3>
                                    <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $statistics['active_users_7_days'] }}</p>
                                </div>
                                <div class="text-indigo-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-6 rounded-xl shadow-sm border border-pink-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-pink-800">Active (30d)</h3>
                                    <p class="text-3xl font-bold text-pink-600 mt-1">{{ $statistics['active_users_30_days'] }}</p>
                                </div>
                                <div class="text-pink-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        <!-- Recent Users -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Users</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <div class="space-y-3">
                                @foreach($statistics['recent_users'] as $user)
                                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div>
                                            <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                            <p class="text-sm text-gray-500">{{ Str::limit($user->email, 25) }}</p>
                                        </div>
                                        <span class="text-gray-400 text-xs">{{ $user->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <div class="space-y-3">
                                @foreach($statistics['recent_audit_logs'] as $log)
                                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if($log->action === 'CREATE') bg-green-100 text-green-800
                                                @elseif($log->action === 'UPDATE') bg-blue-100 text-blue-800
                                                @elseif($log->action === 'DELETE') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif
                                            ">
                                                {{ ucfirst(strtolower($log->action)) }}
                                            </span>
                                            <span class="text-sm text-gray-600">{{ $log->table_name }}</span>
                                        </div>
                                        <span class="text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Key Performance Indicators -->
                        <div class="bg-white p-6 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Key Performance</h3>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Conversion Rate</span>
                                    <span class="font-bold text-lg text-gray-900">
                                        {{ $statistics['total_users'] > 0 ? round(($statistics['active_users_30_days'] / $statistics['total_users']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Avg. Daily Active</span>
                                    <span class="font-bold text-lg text-gray-900">{{ round($statistics['active_users_7_days'] / 7, 1) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Verses per User</span>
                                    <span class="font-bold text-lg text-gray-900">{{ $statistics['total_users'] > 0 ? round($statistics['total_memory_verses'] / $statistics['total_users'], 1) : 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Study Queue per User</span>
                                    <span class="font-bold text-lg text-gray-900">{{ $statistics['total_users'] > 0 ? round($statistics['total_memorize_later'] / $statistics['total_users'], 1) : 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div id="analytics-tab" class="tab-content hidden p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-gray-900">Analytics Dashboard</h2>
                        <p class="text-gray-600">Data visualization and insights for the last 30 days</p>
                    </div>
                    
                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
                        <!-- User Registrations Chart -->
                        <div class="bg-white p-8 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">User Registrations</h3>
                                <div class="text-sm text-gray-500">Last 30 days</div>
                            </div>
                            <div class="relative h-80">
                                <canvas id="userRegistrationsChart"></canvas>
                            </div>
                        </div>

                        <!-- Activity Chart -->
                        <div class="bg-white p-8 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Platform Activity</h3>
                                <div class="text-sm text-gray-500">Last 30 days</div>
                            </div>
                            <div class="relative h-80">
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <!-- Action Breakdown -->
                        <div class="bg-white p-8 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Action Breakdown</h3>
                                <div class="text-sm text-gray-500">Distribution</div>
                            </div>
                            <div class="relative h-80">
                                <canvas id="actionBreakdownChart"></canvas>
                            </div>
                        </div>

                        <!-- Usage Trends & Metrics -->
                        <div class="bg-white p-8 rounded-xl shadow-sm border">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Key Metrics</h3>
                                <div class="text-sm text-gray-500">Performance</div>
                            </div>
                            <div class="space-y-6">
                                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg">
                                    <div>
                                        <span class="text-sm text-blue-800 font-medium">Avg. Daily Active Users</span>
                                        <p class="text-xs text-blue-600">Based on 7-day average</p>
                                    </div>
                                    <span class="font-bold text-2xl text-blue-600">{{ round($statistics['active_users_7_days'] / 7, 1) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg">
                                    <div>
                                        <span class="text-sm text-green-800 font-medium">Verses per User</span>
                                        <p class="text-xs text-green-600">Average memorized verses</p>
                                    </div>
                                    <span class="font-bold text-2xl text-green-600">{{ $statistics['total_users'] > 0 ? round($statistics['total_memory_verses'] / $statistics['total_users'], 1) : 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg">
                                    <div>
                                        <span class="text-sm text-yellow-800 font-medium">Study Queue per User</span>
                                        <p class="text-xs text-yellow-600">Average memorize later items</p>
                                    </div>
                                    <span class="font-bold text-2xl text-yellow-600">{{ $statistics['total_users'] > 0 ? round($statistics['total_memorize_later'] / $statistics['total_users'], 1) : 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg">
                                    <div>
                                        <span class="text-sm text-purple-800 font-medium">Engagement Rate</span>
                                        <p class="text-xs text-purple-600">30-day active user rate</p>
                                    </div>
                                    <span class="font-bold text-2xl text-purple-600">{{ $statistics['total_users'] > 0 ? round(($statistics['active_users_30_days'] / $statistics['total_users']) * 100, 1) : 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit Log Tab -->
                <div id="audit-log-tab" class="tab-content hidden p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-semibold text-gray-900">Audit Log</h2>
                        <p class="text-gray-600">Track all system activities and user actions with advanced filtering and pagination</p>
                    </div>
                    
                    @livewire('audit-log-table', [], key('audit-log-table'), lazy: true)
                </div>
            </div>
        </div>

    <!-- All Users Modal -->
    <div id="allUsersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-6 border w-11/12 max-w-6xl shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">All Users</h3>
                        <p class="text-sm text-gray-600 mt-1">Complete list of registered users</p>
                    </div>
                    <button onclick="closeAllUsers()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="usersTable" class="overflow-x-auto max-h-96">
                    <!-- Users will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Chart data from backend
        const chartData = @json($chartData);
        
        // Chart instances
        let chartInstances = {
            userRegistrations: null,
            activity: null,
            actionBreakdown: null
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
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
                    const targetElement = document.getElementById(targetTab + '-tab');
                    if (targetElement) {
                        targetElement.classList.remove('hidden');
                        
                        // Initialize charts when analytics tab is shown (only once)
                        if (targetTab === 'analytics' && !chartInstances.userRegistrations) {
                            setTimeout(initializeCharts, 100);
                        }
                    }
                });
            });

            // Set initial active state
            tabButtons[0].classList.add('border-blue-500', 'text-blue-600');
            tabButtons[0].classList.remove('border-transparent', 'text-gray-500');
        });

        // Show all users modal
        function showAllUsers() {
            fetch('/super-admin/users')
                .then(response => response.json())
                .then(data => {
                    let tableHTML = `
                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-600">Total: <span class="font-semibold">${data.total}</span> users</p>
                                <div class="text-xs text-gray-500">Click any row to view details</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg border overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                    `;
                    
                    data.users.forEach(user => {
                        const joinedDate = new Date(user.created_at).toLocaleDateString();
                        const lastLogin = user.last_login_date ? new Date(user.last_login_date).toLocaleDateString() : 'Never';
                        
                        tableHTML += `
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">${user.name.charAt(0).toUpperCase()}</span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">${user.name}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${user.email}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${joinedDate}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${lastLogin}</td>
                            </tr>
                        `;
                    });
                    
                    tableHTML += '</tbody></table></div>';
                    
                    document.getElementById('usersTable').innerHTML = tableHTML;
                    document.getElementById('allUsersModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                });
        }

        // Close all users modal
        function closeAllUsers() {
            document.getElementById('allUsersModal').classList.add('hidden');
        }

        // Close modal on outside click
        document.getElementById('allUsersModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAllUsers();
            }
        });

        // Initialize charts (only once)
        function initializeCharts() {
            // Destroy existing charts if they exist
            Object.values(chartInstances).forEach(chart => {
                if (chart) {
                    chart.destroy();
                }
            });

            // User Registrations Chart
            const userRegCtx = document.getElementById('userRegistrationsChart');
            if (userRegCtx) {
                const userRegData = chartData.user_registrations || [];
                const labels = userRegData.map(item => new Date(item.date).toLocaleDateString());
                const data = userRegData.map(item => item.count);

                chartInstances.userRegistrations = new Chart(userRegCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'New Users',
                            data: data,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Activity Chart
            const activityCtx = document.getElementById('activityChart');
            if (activityCtx) {
                const activityData = chartData.activity_data || [];
                const labels = activityData.map(item => new Date(item.date).toLocaleDateString());
                const data = activityData.map(item => item.count);

                chartInstances.activity = new Chart(activityCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Activities',
                            data: data,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 0,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Action Breakdown Chart
            const actionCtx = document.getElementById('actionBreakdownChart');
            if (actionCtx) {
                const actionData = chartData.action_breakdown || [];
                const labels = actionData.map(item => item.action);
                const data = actionData.map(item => item.count);
                const colors = [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ];

                chartInstances.actionBreakdown = new Chart(actionCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                        datasets: [{
                            data: data,
                            backgroundColor: colors.slice(0, labels.length),
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverBorderWidth: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        }
    </script>

    <style>
        .tab-button {
            @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all duration-200;
        }
        .tab-button.active {
            @apply border-blue-500 text-blue-600;
        }
        .tab-button:hover {
            @apply bg-gray-50;
        }
        .tab-button.active:hover {
            @apply bg-blue-50;
        }
        
        /* Custom scrollbar for modal */
        #usersTable::-webkit-scrollbar {
            width: 6px;
        }
        #usersTable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        #usersTable::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        #usersTable::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Smooth chart container sizing */
        .relative canvas {
            max-height: 320px;
        }
    </style>
</x-layouts.app>
