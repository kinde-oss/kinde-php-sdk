<?php

use Illuminate\Support\Facades\Route;
use Kinde\KindeSDK\Frameworks\Laravel\Controllers\KindeAuthController;

Route::group(['prefix' => 'auth', 'as' => 'kinde.'], function () {
    // Login routes
    Route::get('/login', [KindeAuthController::class, 'login'])->name('login');
    Route::get('/register', [KindeAuthController::class, 'register'])->name('register');
    Route::get('/create-org', [KindeAuthController::class, 'createOrg'])->name('create-org');
    
    // Callback route (this should match your Kinde app configuration)
    Route::get('/callback', [KindeAuthController::class, 'callback'])->name('callback');
    
    // Logout route
    Route::get('/logout', [KindeAuthController::class, 'logout'])->name('logout');
    
    // Profile route (protected)
    Route::get('/profile', [KindeAuthController::class, 'profile'])
        ->name('profile')
        ->middleware('kinde.auth');
}); 