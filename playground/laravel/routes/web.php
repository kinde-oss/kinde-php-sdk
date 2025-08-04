<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ExampleController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

// Redirect common auth routes to Kinde auth routes
Route::redirect('/login', '/auth/login');
Route::redirect('/register', '/auth/register');
Route::redirect('/logout', '/auth/logout');

// Test routes for Management API
Route::get('/test-management-api', [ExampleController::class, 'testManagementApi'])->name('test.management.api');
Route::get('/test-endpoint', [ExampleController::class, 'testSpecificEndpoint'])->name('test.endpoint');

// Include Kinde SDK auth routes
require base_path('../../routes/auth.php');
