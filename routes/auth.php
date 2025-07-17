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
    
    // User info route (protected)
    Route::get('/user-info', [KindeAuthController::class, 'userInfo'])
        ->name('user_info')
        ->middleware('kinde.auth');
    
    // Portal route (protected)
    Route::get('/portal', [KindeAuthController::class, 'portal'])
        ->name('portal')
        ->middleware('kinde.auth');
}); 