<?php

use App\Actions\OpenDemoWorkspace;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MailboxController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/demo', function (OpenDemoWorkspace $demo) {
    abort_unless(app()->environment('local') && config('pegasus.demo_enabled'), 404);
    Auth::login($demo->handle());
    request()->session()->regenerate();

    return redirect('/applications');
});

Route::get('/', fn () => redirect(auth()->check() ? '/applications' : '/login'))->name('home');
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->middleware('guest')->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('guest')->name('google.callback');
Route::middleware('auth')->group(function () {
    Route::get('/settings/mailboxes/google/connect', [GoogleAuthController::class, 'connect'])->middleware('throttle:6,1');
    Route::get('/settings/gmail/callback', [GoogleAuthController::class, 'callback']);
    Route::get('/dashboard', fn () => redirect('/applications'))->name('dashboard');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{id}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::post('/settings/mailboxes/imap', [MailboxController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/settings/mailboxes', [MailboxController::class, 'index'])->name('mailboxes.index');
    Route::delete('/settings/mailboxes/{id}', [MailboxController::class, 'destroy'])->name('mailboxes.destroy');
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
