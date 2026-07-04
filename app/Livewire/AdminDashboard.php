<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Message;
use App\Models\Connection;
use Illuminate\Support\Facades\Auth;

class AdminDashboard extends Component
{
    public $searchUser = '';
    
    // Stats
    public $totalUsers = 0;
    public $totalMessages = 0;
    public $totalConnections = 0;

    public function mount()
    {
        // Security check: sirf logged-in admin hi access kar sake
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Unauthorized access to Admin Dashboard.');
        }

        $this->loadStats();
    }

    public function loadStats()
    {
        $this->totalUsers = User::count();
        $this->totalMessages = Message::count();
        $this->totalConnections = Connection::where('status', 'accepted')->count();
    }

    // 1. User ko Admin banana ya hatana
    public function toggleAdmin($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id != Auth::id()) { // khud ko de-admin nahi kar sakte
            $user->update([
                'is_admin' => !$user->is_admin
            ]);
        }
    }

    // 2. User ko permanently delete karna (associated chats aur connections ke sath)
    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user && $user->id != Auth::id()) {
            // Messages delete karein jo isne bheje ya receive kiye
            Message::where('sender_id', $userId)->orWhere('receiver_id', $userId)->delete();
            
            // Connections delete karein
            Connection::where('user_id', $userId)->orWhere('contact_id', $userId)->delete();
            
            // User delete karein
            $user->delete();
            
            $this->loadStats(); // Stats refresh
        }
    }

    public function render()
    {
        // Search filter lagakar users load karein
        $usersList = User::where('id', '!=', Auth::id())
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->searchUser . '%')
                      ->orWhere('email', 'like', '%' . $this->searchUser . '%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin-dashboard', [
            'usersList' => $usersList
        ])->layout('layouts.app'); // app layout use hoga navigation ke liye
    }
}