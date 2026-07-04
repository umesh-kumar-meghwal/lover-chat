<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ChatComponent;
use App\Http\Controllers\ProfileController; // Profile Controller ko import kiya
use App\Livewire\AdminDashboard;

Route::get('/', function () {
    return view('welcome');
});

// 1. Chat Route
Route::get('/chat', ChatComponent::class)
    ->middleware(['auth', 'verified'])
    ->name('chat');

// 2. Dashboard Route (Redirects to Chat)
Route::get('/dashboard', function () {
    return redirect()->route('chat');
})->middleware(['auth', 'verified'])->name('dashboard');
// Admin Dashboard Route (is_admin middleware check is handled inside component mount for safety)
Route::get('/admin/dashboard', AdminDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.dashboard');

// 3. Profile Routes (Breeze Navigation ke liye zaroori hain)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';