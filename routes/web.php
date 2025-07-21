<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/agency/dashboard', [\App\Http\Controllers\AgencyController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('agency.dashboard');

Route::get('/agency/client', [\App\Http\Controllers\AgencyController::class, 'clientStats'])->middleware(['auth', 'verified'])->name('agency.client');

Route::post('/agency/posts', [\App\Http\Controllers\AgencyController::class, 'storePost'])->middleware(['auth', 'verified']);

Route::delete('/agency/posts/{post}', [\App\Http\Controllers\AgencyController::class, 'destroyPost'])->middleware(['auth', 'verified']);

Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/client/dashboard', [\App\Http\Controllers\ClientController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('client.dashboard');

Route::get('/client/calendar', function () {
    return Inertia::render('Client/Calendar');
})->middleware(['auth', 'verified'])->name('client.calendar');

Route::get('/admin/agencies', [\App\Http\Controllers\AdminController::class, 'agencies'])->middleware(['auth', 'verified'])->name('admin.agencies');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
