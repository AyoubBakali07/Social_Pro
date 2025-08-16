<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/agency/dashboard', [\App\Http\Controllers\AgencyController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('agency.dashboard');

Route::get('/agency/client', [\App\Http\Controllers\AgencyController::class, 'clientStats'])->middleware(['auth', 'verified'])->name('agency.client');

Route::post('/agency/posts', [\App\Http\Controllers\AgencyController::class, 'storePost'])->middleware(['auth', 'verified']);

Route::put('/agency/posts/{post}', [\App\Http\Controllers\AgencyController::class, 'updatePost'])->middleware(['auth', 'verified']);

Route::delete('/agency/posts/{post}', [\App\Http\Controllers\AgencyController::class, 'destroyPost'])->middleware(['auth', 'verified']);

Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/client/dashboard', [\App\Http\Controllers\ClientController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('client.dashboard');

Route::get('/client/calendar', function () {
    return Inertia::render('Client/Calendar');
})->middleware(['auth', 'verified'])->name('client.calendar');

Route::get('/admin/agencies', [\App\Http\Controllers\AdminController::class, 'agencies'])->middleware(['auth', 'verified'])->name('admin.agencies');

// Client invitation and setup routes
Route::middleware('auth')->group(function () {
    // Agency routes for managing clients
    Route::post('/agency/clients', [\App\Http\Controllers\AgencyController::class, 'storeClient'])
        ->name('agency.clients.store')
        ->middleware(['auth', 'verified']);
        
    // Client deactivation route
    Route::put('/agency/clients/{client}/deactivate', [\App\Http\Controllers\AgencyController::class, 'deactivateClient'])
        ->name('agency.clients.deactivate')
        ->middleware(['auth', 'verified']);
        
    // Client activation route
    Route::put('/agency/clients/{client}/activate', [\App\Http\Controllers\AgencyController::class, 'activateClient'])
        ->name('agency.clients.activate')
        ->middleware(['auth', 'verified']);
});

// Client password setup routes (no auth required as these are for new users)
Route::get('/setup-password/{token}', [\App\Http\Controllers\Auth\SetupPasswordController::class, 'create'])
    ->name('password.setup');

Route::post('/setup-password', [\App\Http\Controllers\Auth\SetupPasswordController::class, 'store'])
    ->name('password.setup.store');

    Route::get('/test-email', function (){
        Mail::raw('This is a test email', function ($message) {
            $message->to('ayoubbakali817@gmail.com')
                    ->subject('Test Email');
        });
        return 'Email sent!';
    });
    require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
