<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Message;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatComponent extends Component
{
    use WithFileUploads;

    public $users = [];
    public $pendingRequests = []; // incoming requests (Received)
    public $sentRequests = [];    // outgoing requests (Sent)
    public $selectedUserId;
    public $selectedUser;
    
    // Tabs state
    public $activeTab = 'chats'; // 'chats' ya 'requests'

    // Chat features
    public $messageText = '';
    public $photo;
    public $editingMessageId = null;
    public $replyMessageId = null; 
    public $selectedReplyMessage = null;
    public $infoMessageId = null; 
    public $infoMessage = null;

    // Contacts & blocks
    public $newContactEmail = '';
    public $contactError = '';
    public $contactSuccess = '';
    public $customNickname = '';
    public $isBlocked = false;
    public $blockedBy = null;

    public function mount()
    {
        $this->loadConnections();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadConnections();
    }

    public function loadConnections()
    {
        // 1. Accepted aur Blocked friends fetch karein
        $connections = Connection::where(function($q) {
                $q->where('user_id', Auth::id())->orWhere('contact_id', Auth::id());
            })
            ->whereIn('status', ['accepted', 'blocked'])
            ->get();

        $friendIds = [];
        foreach ($connections as $conn) {
            $friendId = ($conn->user_id == Auth::id()) ? $conn->contact_id : $conn->user_id;
            $friendIds[] = $friendId;
        }
        $friendIds = array_unique($friendIds);

        $this->users = [];
        foreach ($friendIds as $fId) {
            $friend = User::find($fId);
            if ($friend) {
                $connRecord = Connection::where(function($q) use ($fId) {
                    $q->where('user_id', Auth::id())->where('contact_id', $fId);
                })->orWhere(function($q) use ($fId) {
                    $q->where('user_id', $fId)->where('contact_id', Auth::id());
                })->first();

                $friend->display_name = $connRecord && $connRecord->nickname ? $connRecord->nickname : $friend->name;
                $friend->connection_status = $connRecord->status;
                $friend->blocked_by = $connRecord->blocked_by;
                $this->users[] = $friend;
            }
        }

        // 2. Incoming Requests (Aayi hui)
        $this->pendingRequests = Connection::where('contact_id', Auth::id())
            ->where('status', 'pending')
            ->with('user')
            ->get();

        // 3. Outgoing Requests (Bheji hui - Dono pending aur rejected load karein)
        $this->sentRequests = Connection::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'rejected'])
            ->with('contact')
            ->get();
    }

    // Connection Request Bhejna
    public function sendRequest()
    {
        $this->validate(['newContactEmail' => 'required|email']);
        $receiver = User::where('email', $this->newContactEmail)->first();

        if (!$receiver) {
            $this->contactError = "Email not found!";
            return;
        }

        if ($receiver->id == Auth::id()) {
            $this->contactError = "Cannot send request to yourself!";
            return;
        }

        $exists = Connection::where(function($q) use ($receiver) {
            $q->where('user_id', Auth::id())->where('contact_id', $receiver->id);
        })->orWhere(function($q) use ($receiver) {
            $q->where('user_id', $receiver->id)->where('contact_id', Auth::id());
        })->first();

        if ($exists) {
            if ($exists->status == 'rejected' && $exists->user_id == Auth::id()) {
                $exists->update(['status' => 'pending']); // Dubara pending kiya
                $this->contactSuccess = "Request sent again!";
                $this->contactError = "";
                $this->newContactEmail = '';
                $this->loadConnections();
                return;
            }
            $this->contactError = "Connection already exists!";
            return;
        }

        Connection::create([
            'user_id' => Auth::id(),
            'contact_id' => $receiver->id,
            'status' => 'pending'
        ]);

        $this->contactSuccess = "Request sent successfully!";
        $this->contactError = "";
        $this->newContactEmail = '';
        $this->loadConnections();
    }

    // Request Accept karna
    public function acceptRequest($requestId)
    {
        $conn = Connection::find($requestId);
        if ($conn && $conn->contact_id == Auth::id()) {
            $conn->update(['status' => 'accepted']);
            $this->loadConnections();
        }
    }

    // Request Reject karna (Status rejected set hoga)
    public function rejectRequest($requestId)
    {
        $conn = Connection::find($requestId);
        if ($conn && $conn->contact_id == Auth::id()) {
            $conn->update(['status' => 'rejected']);
            $this->loadConnections();
        }
    }

    // 1. DELETE CONNECTION: Bheji gayi requests cancel karne, reject hui records hatane ya friend delete karne ke liye
    public function deleteConnection($connectionId)
    {
        $conn = Connection::find($connectionId);
        if ($conn) {
            // Suraksha check: sirf sender ya receiver hi is record ko delete kar sakein
            if ($conn->user_id == Auth::id() || $conn->contact_id == Auth::id()) {
                
                // Agar active connection tha toh chat clear karein
                if ($conn->status == 'accepted') {
                    $friendId = ($conn->user_id == Auth::id()) ? $conn->contact_id : $conn->user_id;
                    $this->clearChatHistory($friendId);
                }

                $conn->delete();
                
                // Agar chat open thi toh use reset karein
                if ($this->selectedUserId == $conn->contact_id || $this->selectedUserId == $conn->user_id) {
                    $this->resetChat();
                }

                $this->loadConnections();
            }
        }
    }

    // Helper to clear chat on deletion
    private function clearChatHistory($friendId)
    {
        $messages = Message::where(function($query) use ($friendId) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $friendId);
        })->orWhere(function($query) use ($friendId) {
            $query->where('sender_id', $friendId)->where('receiver_id', Auth::id());
        })->get();

        foreach ($messages as $msg) {
            if ($msg->image) {
                Storage::disk('public')->delete($msg->image);
            }
            $msg->delete();
        }
    }

    public function saveNickname()
    {
        if ($this->selectedUserId) {
            $conn = Connection::where(function($q) {
                $q->where('user_id', Auth::id())->where('contact_id', $this->selectedUserId);
            })->first();

            if ($conn) {
                $conn->update(['nickname' => $this->customNickname]);
                $this->loadConnections();
                $this->selectUser($this->selectedUserId);
            }
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::find($userId);
        $this->editingMessageId = null;
        $this->replyMessageId = null;
        $this->selectedReplyMessage = null;
        $this->messageText = '';

        $conn = Connection::where(function($q) use ($userId) {
            $q->where('user_id', Auth::id())->where('contact_id', $userId);
        })->orWhere(function($q) use ($userId) {
            $q->where('user_id', $userId)->where('contact_id', Auth::id());
        })->first();

        if ($conn && $conn->status == 'blocked') {
            $this->isBlocked = true;
            $this->blockedBy = $conn->blocked_by;
        } else {
            $this->isBlocked = false;
            $this->blockedBy = null;
        }

        $nicknameRec = Connection::where('user_id', Auth::id())->where('contact_id', $userId)->first();
        $this->customNickname = $nicknameRec && $nicknameRec->nickname ? $nicknameRec->nickname : $this->selectedUser->name;
    }

    public function sendMessage()
    {
        if ($this->isBlocked) return;

        $this->validate([
            'messageText' => $this->photo ? 'nullable' : 'required',
            'photo' => 'nullable|image|max:5120'
        ]);

        $imagePath = null;
        if ($this->photo) {
            $imagePath = $this->photo->store('chats', 'public');
        }

        if ($this->editingMessageId) {
            $message = Message::find($this->editingMessageId);
            if ($message && $message->sender_id == Auth::id()) {
                $message->update(['message' => $this->messageText]);
            }
            $this->editingMessageId = null;
        } else {
            Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $this->selectedUserId,
                'message' => $this->messageText,
                'image' => $imagePath,
                'parent_id' => $this->replyMessageId
            ]);
        }

        $this->messageText = '';
        $this->photo = null;
        $this->replyMessageId = null;
        $this->selectedReplyMessage = null;
    }

    public function setReply($messageId)
    {
        $this->replyMessageId = $messageId;
        $this->selectedReplyMessage = Message::find($messageId);
    }

    public function cancelReply()
    {
        $this->replyMessageId = null;
        $this->selectedReplyMessage = null;
    }

    public function togglePin($messageId)
    {
        $message = Message::find($messageId);
        if ($message) {
            $message->update(['is_pinned' => !$message->is_pinned]);
        }
    }

    public function reactToMessage($messageId, $emoji)
    {
        $message = Message::find($messageId);
        if ($message) {
            $newReaction = ($message->reaction == $emoji) ? null : $emoji;
            $message->update(['reaction' => $newReaction]);
        }
    }

    public function showInfo($messageId)
    {
        $this->infoMessageId = $messageId;
        $this->infoMessage = Message::find($messageId);
    }

    public function closeInfo()
    {
        $this->infoMessageId = null;
        $this->infoMessage = null;
    }

    public function clearChat()
    {
        if ($this->selectedUserId) {
            $this->clearChatHistory($this->selectedUserId);
            $this->messageText = '';
        }
    }

    public function blockUser()
    {
        if ($this->selectedUserId) {
            Connection::where(function($q) {
                $q->where('user_id', Auth::id())->where('contact_id', $this->selectedUserId);
            })->orWhere(function($q) {
                $q->where('user_id', $this->selectedUserId)->where('contact_id', Auth::id());
            })->update([
                'status' => 'blocked',
                'blocked_by' => Auth::id()
            ]);
            $this->selectUser($this->selectedUserId);
            $this->loadConnections();
        }
    }

    public function unblockUser()
    {
        if ($this->selectedUserId) {
            Connection::where(function($q) {
                $q->where('user_id', Auth::id())->where('contact_id', $this->selectedUserId);
            })->orWhere(function($q) {
                $q->where('user_id', $this->selectedUserId)->where('contact_id', Auth::id());
            })->update([
                'status' => 'accepted',
                'blocked_by' => null
            ]);
            $this->selectUser($this->selectedUserId);
            $this->loadConnections();
        }
    }

    public function deleteContact()
    {
        if ($this->selectedUserId) {
            $conn = Connection::where(function($q) {
                $q->where('user_id', Auth::id())->where('contact_id', $this->selectedUserId);
            })->orWhere(function($q) {
                $q->where('user_id', $this->selectedUserId)->where('contact_id', Auth::id());
            })->first();

            if ($conn) {
                $this->deleteConnection($conn->id);
            }
        }
    }

    public function startEdit($messageId)
    {
        if ($this->isBlocked) return;
        $message = Message::find($messageId);
        if ($message && $message->sender_id == Auth::id()) {
            $this->editingMessageId = $messageId;
            $this->messageText = $message->message;
        }
    }

    public function cancelEdit()
    {
        $this->editingMessageId = null;
        $this->messageText = '';
    }

    public function deleteMessage($messageId)
    {
        $message = Message::find($messageId);
        if ($message) {
            if ($message->sender_id == Auth::id() || $message->receiver_id == Auth::id()) {
                if ($message->image) {
                    Storage::disk('public')->delete($message->image);
                }
                $message->delete();
            }
        }
    }

    public function resetChat()
    {
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->isBlocked = false;
        $this->blockedBy = null;
        $this->loadConnections();
    }

    public function render()
    {
        $messages = [];

        if ($this->selectedUserId && !$this->isBlocked) {
            Message::where('receiver_id', Auth::id())
                ->where('is_delivered', false)
                ->update(['is_delivered' => true]);

            Message::where('sender_id', $this->selectedUserId)
                ->where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'is_delivered' => true
                ]);
        }

        if ($this->selectedUserId) {
            $messages = Message::where(function($query) {
                $query->where('sender_id', Auth::id())->where('receiver_id', $this->selectedUserId);
            })->orWhere(function($query) {
                $query->where('sender_id', $this->selectedUserId)->where('receiver_id', Auth::id());
            })->orderBy('is_pinned', 'desc')->orderBy('created_at', 'asc')->get();
        }

        return view('livewire.chat-component', [
            'messages' => $messages
        ]);
    }
}