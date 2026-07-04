<div class="py-6 px-4 max-w-7xl mx-auto space-y-6">
    
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between border-b pb-4 border-slate-200">
        <div>
            <h1 class="text-3xl font-black text-slate-800">Admin Control Center</h1>
            <p class="text-xs text-slate-500">Manage all registered users, stats, and chat systems.</p>
        </div>
        <a href="{{ route('chat') }}" class="bg-emerald-600 text-white text-xs px-4 py-2 rounded-lg font-bold hover:bg-emerald-700 transition">
            Go to Chat App
        </a>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Users -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Total Accounts</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalUsers }}</h3>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-full text-2xl">👥</div>
        </div>

        <!-- Card 2: Messages -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Messages Sent</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalMessages }}</h3>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-full text-2xl">💬</div>
        </div>

        <!-- Card 3: Connections -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Active Connections</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalConnections }}</h3>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-full text-2xl">🔗</div>
        </div>
    </div>

    <!-- USERS MANAGEMENT SYSTEM -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-800">Accounts Registry</h2>
            
            <!-- User Search Bar -->
            <div class="relative w-full md:w-80">
                <input type="text" wire:model.live="searchUser" placeholder="Search user by name or email..." class="w-full text-xs border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:border-emerald-500">
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="p-4">Profile</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Account Role</th>
                        <th class="p-4">Registered Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @if(count($usersList) == 0)
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">No users match your criteria.</td>
                    </tr>
                    @endif

                    @foreach($usersList as $usr)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-4 flex items-center space-x-3">
                            <div class="w-8 h-8 bg-emerald-600 text-white font-bold rounded-full flex items-center justify-center">
                                {{ substr($usr->name, 0, 1) }}
                            </div>
                            <span class="font-semibold text-slate-800">{{ $usr->name }}</span>
                        </td>
                        <td class="p-4 text-slate-500">{{ $usr->email }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[9px] font-bold uppercase {{ $usr->is_admin ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $usr->is_admin ? '👑 Admin' : 'User' }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-400">{{ $usr->created_at->format('d M Y, h:i A') }}</td>
                        <td class="p-4 text-right space-x-1.5">
                            <!-- Toggle Admin role Button -->
                            <button wire:click="toggleAdmin({{ $usr->id }})" class="px-3 py-1.5 border rounded-lg hover:bg-slate-100 transition font-medium">
                                {{ $usr->is_admin ? 'Remove Admin' : 'Make Admin' }}
                            </button>

                            <!-- Delete User Button -->
                            <button onclick="confirm('Are you sure you want to permanently delete this user account, including all their chat history?') || event.stopImmediatePropagation()" wire:click="deleteUser({{ $usr->id }})" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition font-bold">
                                Delete Account
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>