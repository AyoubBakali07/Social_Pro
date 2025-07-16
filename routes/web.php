<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/agency/dashboard', function () {
    return Inertia::render('Agency/Dashboard');
})->middleware(['auth', 'verified'])->name('agency.dashboard');

Route::get('/agency/client', function () {
    return Inertia::render('Agency/Client');
})->middleware(['auth', 'verified'])->name('agency.client');

Route::get('/admin/dashboard', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::get('/client/dashboard', function () {
    return Inertia::render('Client/Dashboard');
})->middleware(['auth', 'verified'])->name('client.dashboard');

Route::get('/client/calendar', function () {
    return Inertia::render('Client/Calendar');
})->middleware(['auth', 'verified'])->name('client.calendar');

Route::get('/admin/agencies', function () {
    return Inertia::render('Admin/Agencies');
})->middleware(['auth', 'verified'])->name('admin.agencies');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
