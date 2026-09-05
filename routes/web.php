<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MelodyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TemplateController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
});

Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/invitations', [AdminInvitationController::class, 'index'])->name('invitations.index');
    Route::patch('/invitations/{invitation}/status', [AdminInvitationController::class, 'updateStatus'])->name('invitations.status');
});

Route::get('/invitations/create', [InvitationController::class, 'create'])
    ->middleware('auth')
    ->name('invitations.create');

require __DIR__ . '/auth.php';
