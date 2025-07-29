<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

// Redirect common auth routes to Kinde auth routes
Route::redirect('/login', '/auth/login');
Route::redirect('/register', '/auth/register');
Route::redirect('/logout', '/auth/logout');

// Include Kinde SDK auth routes
require base_path('../../routes/auth.php');
