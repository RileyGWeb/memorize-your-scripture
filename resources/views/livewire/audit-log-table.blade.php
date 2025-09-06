<div>
    <!-- Filters and Search -->
    <div class="mb-6 bg-white p-6 rounded-xl shadow-sm border">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Search Input -->
            <div class="flex-1 max-w-md">
                <label for="search" class="sr-only">Search audit logs</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" 
                           id="search"
                           type="text" 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Search users, actions, tables...">
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Action Filter -->
                <div>
                    <select wire:model.live="actionFilter" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}">{{ ucfirst(strtolower($action)) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Table Filter -->
                <div>
                    <select wire:model.live="tableFilter" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Tables</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}">{{ ucfirst($table) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <select wire:model.live="perPage" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10 per page</option>
                        <option value="15">15 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                @if($search || $actionFilter || $tableFilter)
                    <button wire:click="clearFilters" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                        Clear Filters
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="mb-4 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Showing {{ $auditLogs->firstItem() ?? 0 }} to {{ $auditLogs->lastItem() ?? 0 }} of {{ $auditLogs->total() }} results
        </div>
        <div wire:loading class="text-sm text-blue-600">
            <svg class="inline w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Loading...
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('created_at')" 
                                    class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Time
                                <span class="ml-2">
                                    @if($sortField === 'created_at')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('action')" 
                                    class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Action
                                <span class="ml-2">
                                    @if($sortField === 'action')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('table_name')" 
                                    class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Table
                                <span class="ml-2">
                                    @if($sortField === 'table_name')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>
                        <th class="px-6 py-3 text-left">
                            <button wire:click="sortBy('record_id')" 
                                    class="group inline-flex items-center text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                                Record ID
                                <span class="ml-2">
                                    @if($sortField === 'record_id')
                                        @if($sortDirection === 'asc')
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        @endif
                                    @else
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                        </svg>
                                    @endif
                                </span>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $log->created_at->format('M j, Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                                    <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if($log->user)
                                                <span class="text-xs font-medium text-gray-600">{{ strtoupper(substr($log->user->name, 0, 2)) }}</span>
                                            @else
                                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->user ? $log->user->name : 'System' }}
                                        </div>
                                        @if($log->user)
                                            <div class="text-xs text-gray-500">
                                                {{ Str::limit($log->user->email, 25) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                    @if($log->action === 'CREATE') bg-green-100 text-green-800
                                    @elseif($log->action === 'UPDATE') bg-blue-100 text-blue-800
                                    @elseif($log->action === 'DELETE') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst(strtolower($log->action)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900 bg-gray-50">
                                {{ $log->table_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{ $log->record_id }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">No audit logs found</h3>
                                    <p class="text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($auditLogs->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>
</div>
