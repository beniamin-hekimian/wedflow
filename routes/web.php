<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\MelodyController as AdminMelodyController;
use App\Http\Controllers\Admin\ResponseController as AdminResponseController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MelodyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicInvitationController;
use App\Http\Controllers\RsvpController;
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
    Route::get('/invitations/{invitation:slug}/edit', [InvitationController::class, 'edit'])->name('invitations.edit');
    Route::put('/invitations/{invitation:slug}', [InvitationController::class, 'update'])->name('invitations.update');
    Route::delete('/invitations/{invitation:slug}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
    Route::get('/invitations/{invitation:slug}/responses', [InvitationController::class, 'responses'])->name('invitations.responses');
    Route::patch('/invitations/{invitation:slug}/responses/{response}/visibility', [InvitationController::class, 'toggleVisibility'])->name('invitations.responses.visibility');
});

Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/invitations', [AdminInvitationController::class, 'index'])->name('invitations.index');
    Route::patch('/invitations/{invitation}/status', [AdminInvitationController::class, 'updateStatus'])->name('invitations.status');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::get('/templates', [AdminTemplateController::class, 'index'])->name('templates.index');
    Route::get('/melodies', [AdminMelodyController::class, 'index'])->name('melodies.index');
    Route::get('/responses', [AdminResponseController::class, 'index'])->name('responses.index');
});

Route::get('/invitations/create', [InvitationController::class, 'create'])
    ->middleware('auth')
    ->name('invitations.create');

require __DIR__ . '/auth.php';

Route::post('/{slug}/rsvp', [RsvpController::class, 'store'])->name('rsvp.store');

Route::get('/{slug}', PublicInvitationController::class)->name('invitations.show');
