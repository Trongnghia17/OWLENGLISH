<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard');
});

Route::prefix('error')->group(function () {
    Route::get('/403', function () {
        return view('errors.403');
    })->name('error.403');
    
    Route::get('/404', function () {
        return view('errors.404');
    })->name('error.404');
    
    Route::get('/500', function () {
        return view('errors.500');
    })->name('error.500');
});
