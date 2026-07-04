<div class="flex h-screen bg-slate-100 overflow-hidden" x-data="{ infoOpen: @entangle('infoMessageId') }">
    
    <!-- LEFT SIDE: Contacts, Tabs & Outgoing status -->
    <div class="w-full md:w-1/3 bg-white border-r border-slate-200 flex flex-col {{ $selectedUserId ? 'hidden md:flex' : 'flex' }} transition-all duration-300">
        
        <!-- Modern Green Header with Add Contact -->
        <div class="p-4 bg-emerald-600 text-white shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight">Lover Chat</h2>
                
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 bg-emerald-500 hover:bg-emerald-400 text-white rounded-full transition shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"></path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-slate-100 p-4 z-50 text-slate-800" style="display: none;">
                        <h3 class="text-sm font-semibold text-slate-700 mb-2">Send Chat Request</h3>
                        <div class="space-y-2">
                            <input type="email" wire:model="newContactEmail" placeholder="Enter user's email..." class="w-full text-xs border border-slate-200 rounded-lg p-2 focus:outline-none focus:border-emerald-500">
                            <button @click="open = false" wire:click="sendRequest" class="w-full bg-emerald-600 text-white text-xs py-2 rounded-lg font-semibold hover:bg-emerald-700 transition">Send Request</button>
                        </div>
                    </div>
                </div>
            </div>
            @if($contactError) <span class="text-red-100 text-xs mt-2 block">{{ $contactError }}</span> @endif
            @if($contactSuccess) <span class="text-emerald-100 text-xs mt-2 block">{{ $contactSuccess }}</span> @endif
        </div>

        <!-- GREEN NAVIGATION TABS -->
        <div class="flex border-b border-slate-200 bg-emerald-700 text-white">
            <button wire:click="setTab('chats')" class="flex-1 py-3 text-center text-xs font-bold tracking-wider uppercase border-b-2 transition duration-200 {{ $activeTab == 'chats' ? 'border-white bg-emerald-800' : 'border-transparent opacity-80 hover:opacity-100' }}">
                Chats ({{ count($users) }})
            </button>
            <button wire:click="setTab('requests')" class="flex-1 py-3 text-center text-xs font-bold tracking-wider uppercase border-b-2 transition duration-200 {{ $activeTab == 'requests' ? 'border-white bg-emerald-800' : 'border-transparent opacity-80 hover:opacity-100' }}">
                Sent Status ({{ count($sentRequests) }})
            </button>
        </div>

        <!-- TAB 1: CHATS VIEW -->
        @if($activeTab == 'chats')
            <!-- RECEIVED REQUESTS NOTIFICATION (Incoming) -->
            @if(count($pendingRequests) > 0)
            <div class="bg-amber-50 p-3 border-b border-amber-100 transition duration-300">
                <h3 class="text-[10px] font-bold text-amber-800 tracking-wider uppercase mb-2">Incoming Requests</h3>
                <div class="space-y-1.5">
                    @foreach($pendingRequests as $req)
                    <div class="flex items-center justify-between bg-white p-2.5 rounded-lg border border-amber-100 shadow-sm animate-pulse">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">{{ $req->user->name }}</p>
                            <p class="text-[9px] text-slate-400">wants to connect</p>
                        </div>
                        <div class="flex space-x-1">
                            <button wire:click="acceptRequest({{ $req->id }})" class="bg-emerald-600 text-white text-[9px] px-2.5 py-1.5 rounded font-bold transition">Accept</button>
                            <button wire:click="rejectRequest({{ $req->id }})" class="bg-red-500 text-white text-[9px] px-2.5 py-1.5 rounded font-bold transition">Reject</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Active Connections Chat list -->
            <div class="overflow-y-auto flex-1 divide-y divide-slate-50">
                @if(count($users) == 0)
                <div class="p-8 text-center text-slate-400">
                    <p class="text-sm">No connections yet.</p>
                    <p class="text-xs mt-1">Click the top plus button to invite users.</p>
                </div>
                @endif

                @foreach($users as $friend)
                <button
                    wire:click="selectUser({{ $friend->id }})"
                    class="w-full text-left p-4 hover:bg-slate-50 flex items-center space-x-3 transition duration-150 {{ $selectedUserId == $friend->id ? 'bg-emerald-50 hover:bg-emerald-50' : '' }}">
                    
                    <div class="w-12 h-12 bg-emerald-600 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-sm">
                        {{ substr($friend->display_name, 0, 1) }}
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-slate-800 text-sm md:text-base">{{ $friend->display_name }}</p>
                            @if($friend->connection_status == 'blocked')
                                <span class="text-[9px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded font-bold uppercase">Blocked</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 truncate">{{ $friend->email }}</p>
                    </div>
                </button>
                @endforeach
            </div>
        @endif

        <!-- TAB 2: SENT REQUESTS HISTORY VIEW -->
        @if($activeTab == 'requests')
            <div class="overflow-y-auto flex-1 p-4 bg-slate-50 space-y-3">
                <h3 class="text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-1">Outgoing Request Manager</h3>
                
                @if(count($sentRequests) == 0)
                <div class="p-8 text-center text-slate-400 bg-white border rounded-xl shadow-sm">
                    <p class="text-xs">No sent requests history found.</p>
                </div>
                @endif

                @foreach($sentRequests as $sReq)
                <div class="flex items-center justify-between bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm transition-all duration-300">
                    <div>
                        <p class="text-xs font-bold text-slate-800">{{ $sReq->contact->name }}</p>
                        <p class="text-[10px] text-slate-400">{{ $sReq->contact->email }}</p>
                        
                        <div class="mt-2.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider {{ $sReq->status == 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600' }}">
                                {{ $sReq->status == 'pending' ? '⏳ Pending' : '❌ Rejected' }}
                            </span>
                        </div>
                    </div>

                    <!-- Cancel / Delete request Button (Permanently deletes connection row) -->
                    <button onclick="confirm('Delete this request status?') || event.stopImmediatePropagation()" wire:click="deleteConnection({{ $sReq->id }})" class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-full transition shadow-sm" title="Delete status">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                        </svg>
                    </button>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- RIGHT SIDE: Chat Window & Features -->
    <div class="w-full md:w-2/3 flex flex-row bg-slate-50 {{ $selectedUserId ? 'flex' : 'hidden md:flex' }}">
        
        <!-- Main Chat Box -->
        <div class="flex-1 flex flex-col h-full relative">
            @if($selectedUser)
            
            <!-- Modern Green Chat Header -->
            <div class="p-4 border-b border-slate-200 bg-white flex items-center justify-between shadow-sm">
                <div class="flex items-center flex-1" x-data="{ showRename: false }">
                    <button wire:click="resetChat" class="md:hidden mr-3 p-1 text-slate-600 hover:bg-slate-100 rounded-full transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>

                    <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center text-white font-bold mr-3">
                        {{ substr($customNickname, 0, 1) }}
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <h2 class="text-sm md:text-base font-bold text-slate-800 leading-tight" x-show="!showRename">{{ $customNickname }}</h2>
                            <input x-show="showRename" type="text" wire:model="customNickname" class="text-xs border border-slate-200 rounded px-2 py-0.5 focus:outline-none focus:border-emerald-500" @keydown.enter="showRename = false; $wire.saveNickname()">
                            
                            @if(!$isBlocked)
                            <button @click="showRename = !showRename" class="text-slate-400 hover:text-emerald-600 transition" title="Rename Contact">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path>
                                </svg>
                            </button>
                            @endif
                        </div>
                        <span class="text-xs {{ $isBlocked ? 'text-red-500' : 'text-emerald-500' }} font-medium">
                            {{ $isBlocked ? '● Connection Blocked' : '● Active Connection' }}
                        </span>
                    </div>
                </div>

                <!-- Three-Dot Options Dropdown -->
                <div x-data="{ menuOpen: false }" class="relative">
                    <button @click="menuOpen = !menuOpen" class="p-2 text-slate-500 hover:bg-slate-100 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"></path>
                        </svg>
                    </button>

                    <div x-show="menuOpen" @click.away="menuOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-100 py-1 z-50 text-slate-700" style="display: none;">
                        <button onclick="confirm('Clear all messages?') || event.stopImmediatePropagation()" wire:click="clearChat" @click="menuOpen = false" class="w-full text-left px-4 py-2 text-xs hover:bg-slate-50">Clear Chat</button>
                        
                        @if($isBlocked && $blockedBy == Auth::id())
                            <button wire:click="unblockUser" @click="menuOpen = false" class="w-full text-left px-4 py-2 text-xs text-emerald-600 font-bold hover:bg-slate-50">Unblock User</button>
                        @elseif(!$isBlocked)
                            <button onclick="confirm('Block contact?') || event.stopImmediatePropagation()" wire:click="blockUser" @click="menuOpen = false" class="w-full text-left px-4 py-2 text-xs text-red-600 font-bold hover:bg-slate-50">Block User</button>
                        @endif

                        <button onclick="confirm('Delete contact?') || event.stopImmediatePropagation()" wire:click="deleteContact" @click="menuOpen = false" class="w-full text-left px-4 py-2 text-xs text-red-700 font-bold hover:bg-slate-50 border-t">Delete Contact</button>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Container -->
            <div wire:poll.2s class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-blend-mode: overlay; background-color: #f1f5f9;">
                @foreach($messages as $msg)
                <div class="flex {{ $msg->sender_id == Auth::id() ? 'justify-end' : 'justify-start' }} group transition-all duration-300 animate-fade-in">
                    
                    <!-- Bubble Container -->
                    <div class="relative max-w-[75%] px-3.5 py-2 rounded-2xl shadow-sm {{ $msg->sender_id == Auth::id() ? 'bg-emerald-600 text-white rounded-tr-none' : 'bg-white text-slate-800 rounded-tl-none border border-slate-100' }}">
                        
                        <!-- Pin Icon (If Pinned) -->
                        @if($msg->is_pinned)
                        <span class="absolute -top-2 right-2 text-amber-500" title="Pinned">📌</span>
                        @endif

                        <!-- Quoted Reply Render -->
                        @if($msg->parent_id && $msg->parent)
                        <div class="mb-2 p-2 rounded-lg bg-emerald-700/20 border-l-4 border-emerald-400 text-xs">
                            <p class="font-bold text-[10px] opacity-75">Quoted Message:</p>
                            <p class="truncate">{{ $msg->parent->message }}</p>
                        </div>
                        @endif

                        <!-- Display Image -->
                        @if($msg->image)
                        <div class="mb-2 overflow-hidden rounded-lg">
                            <img src="{{ asset('storage/' . $msg->image) }}" class="max-w-full h-auto max-h-60 rounded-lg cursor-pointer hover:opacity-90">
                        </div>
                        @endif

                        <!-- Message Text -->
                        @if($msg->message)
                        <p class="text-[14px] leading-relaxed break-words whitespace-pre-wrap">{{ $msg->message }}</p>
                        @endif

                        <!-- Reaction Display -->
                        @if($msg->reaction)
                        <span class="absolute -bottom-2 -right-1 bg-white border rounded-full px-1 py-0.5 text-xs shadow-sm">{{ $msg->reaction }}</span>
                        @endif

                        <!-- HOVER TOOLBAR: Actions Menu -->
                        <div class="absolute -left-36 top-1/2 -translate-y-1/2 flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 bg-white p-1 rounded-full border shadow-sm">
                            
                            <div class="flex space-x-0.5 px-1 border-r">
                                <button wire:click="reactToMessage({{ $msg->id }}, '👍')" class="hover:scale-125 transition">👍</button>
                                <button wire:click="reactToMessage({{ $msg->id }}, '❤️')" class="hover:scale-125 transition">❤️</button>
                                <button wire:click="reactToMessage({{ $msg->id }}, '😂')" class="hover:scale-125 transition">😂</button>
                                <button wire:click="reactToMessage({{ $msg->id }}, '😮')" class="hover:scale-125 transition">😮</button>
                            </div>

                            <button onclick="navigator.clipboard.writeText('{{ addslashes($msg->message) }}'); alert('Copied!')" class="p-1 hover:bg-slate-100 text-slate-500 rounded-full" title="Copy Message">📋</button>

                            @if(!$isBlocked)
                            <button wire:click="setReply({{ $msg->id }})" class="p-1 hover:bg-slate-100 text-slate-500 rounded-full" title="Reply">↩️</button>
                            @endif

                            <button wire:click="togglePin({{ $msg->id }})" class="p-1 hover:bg-slate-100 text-slate-500 rounded-full" title="Pin Message">📌</button>
                            <button wire:click="showInfo({{ $msg->id }})" class="p-1 hover:bg-slate-100 text-slate-500 rounded-full" title="Message Info">ℹ️</button>

                            @if($msg->sender_id == Auth::id() && !$msg->image && !$isBlocked)
                            <button wire:click="startEdit({{ $msg->id }})" class="p-1 hover:bg-slate-100 text-slate-500 rounded-full" title="Edit">✏️</button>
                            @endif

                            <button onclick="confirm('Delete?') || event.stopImmediatePropagation()" wire:click="deleteMessage({{ $msg->id }})" class="p-1 hover:bg-red-50 text-red-500 rounded-full" title="Delete">🗑️</button>
                        </div>

                        <!-- Time & Ticks -->
                        <div class="flex items-center justify-end space-x-1 mt-1 opacity-75">
                            <span class="text-[9px] block text-right font-medium">
                                {{ $msg->created_at->format('h:i A') }}
                            </span>

                            @if($msg->sender_id == Auth::id())
                            <span class="inline-flex">
                                @if($msg->is_read)
                                <svg class="w-3.5 h-3.5 text-emerald-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12l5 5L20 4M13 12l5 5M12 12l5 5"></path></svg>
                                @elseif($msg->is_delivered)
                                <svg class="w-3.5 h-3.5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12l5 5L20 4M13 12l5 5M12 12l5 5"></path></svg>
                                @else
                                <svg class="w-3.5 h-3.5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"></path></svg>
                                @endif
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Blocked Banner -->
            @if($isBlocked)
                <div class="p-4 bg-red-50 border-t border-red-200 text-center text-sm font-semibold text-red-700">
                    @if($blockedBy == Auth::id())
                        You blocked this user. <button wire:click="unblockUser" class="ml-2 underline font-bold text-red-900 hover:text-slate-900">Unblock</button>
                    @else
                        This connection is blocked. You cannot send messages.
                    @endif
                </div>
            @else
                <!-- Image Upload Preview -->
                @if($photo)
                <div class="p-3 bg-slate-100 border-t border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <img src="{{ $photo->temporaryUrl() }}" class="w-12 h-12 object-cover rounded-lg border">
                        <span class="text-xs text-slate-500">Photo selected...</span>
                    </div>
                    <button wire:click="$set('photo', null)" class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-md">Cancel</button>
                </div>
                @endif

                <!-- Reply Header Preview Box -->
                @if($selectedReplyMessage)
                <div class="px-4 py-2 bg-emerald-50 border-t border-emerald-100 flex items-center justify-between">
                    <div class="flex-1 text-xs text-slate-600 truncate">
                        <p class="font-bold text-[10px] text-emerald-600">Replying to message:</p>
                        <p class="italic">"{{ $selectedReplyMessage->message }}"</p>
                    </div>
                    <button wire:click="cancelReply" class="text-xs text-slate-400 hover:text-red-500 font-bold ml-2">Close</button>
                </div>
                @endif

                <!-- Edit Indicator -->
                @if($editingMessageId)
                <div class="px-4 py-1.5 bg-emerald-50 border-t border-slate-200 flex items-center justify-between">
                    <span class="text-xs text-emerald-600 font-medium">Editing message...</span>
                    <button wire:click="cancelEdit" class="text-xs text-slate-500 font-bold">Cancel</button>
                </div>
                @endif

                <!-- Modern Message Multiline Form -->
                <form wire:submit.prevent="sendMessage" class="p-3 border-t border-slate-200 bg-white flex items-end space-x-2">
                    
                    <label class="cursor-pointer p-2.5 hover:bg-slate-100 rounded-full text-slate-500 transition mb-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"></path>
                        </svg>
                        <input type="file" wire:model="photo" class="hidden" accept="image/*">
                    </label>

                    <textarea
                        wire:model="messageText"
                        rows="2"
                        placeholder="Type a message..."
                        class="flex-1 border border-slate-200 bg-slate-50 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-emerald-500 focus:bg-white resize-none transition duration-200"
                        style="max-height: 120px;"></textarea>
                    
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white p-3 rounded-full transition shadow-md mb-1">
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2 21l21-9L2 3v7l15 2-15 2v7z"></path>
                        </svg>
                    </button>
                </form>
            @endif
            @else
            <div class="flex-1 flex flex-col items-center justify-center text-slate-400 bg-slate-50">
                <div class="p-4 bg-white rounded-full shadow-sm mb-4">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                    </svg>
                </div>
                <p class="font-medium text-slate-500">Select contact to chat</p>
            </div>
            @endif
        </div>

        <!-- RIGHT SIDE SLIDE-OUT MODAL: MESSAGE INFO PANEL -->
        <div x-show="infoOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="w-80 bg-white border-l border-slate-200 shadow-xl flex flex-col h-full z-20" style="display: none;">
            @if($infoMessage)
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm">Message Information</h3>
                <button wire:click="closeInfo" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            
            <div class="p-4 flex-1 overflow-y-auto space-y-6">
                <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg text-xs text-slate-600 italic break-words">
                    <p class="font-bold not-italic text-slate-800 text-[10px] mb-1 uppercase tracking-wider">Content Preview</p>
                    "{{ $infoMessage->message }}"
                </div>

                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-1.5 bg-blue-50 text-blue-600 rounded-full">🕒</div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Sent</p>
                            <p class="text-[10px] text-slate-500">{{ $infoMessage->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="p-1.5 {{ $infoMessage->is_delivered ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400' }} rounded-full">✓✓</div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Delivered</p>
                            <p class="text-[10px] text-slate-500">
                                @if($infoMessage->is_delivered)
                                    {{ $infoMessage->updated_at->format('d M Y, h:i A') }}
                                @else
                                    Pending...
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="p-1.5 {{ $infoMessage->is_read ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-50 text-slate-400' }} rounded-full">✓✓</div>
                        <div>
                            <p class="text-xs font-bold text-slate-800">Read</p>
                            <p class="text-[10px] text-slate-500">
                                @if($infoMessage->is_read)
                                    {{ $infoMessage->updated_at->format('d M Y, h:i A') }}
                                @else
                                    Not read yet...
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>