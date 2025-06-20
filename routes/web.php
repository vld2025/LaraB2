<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-session', function () {
    session(['test' => 'working']);
    return 'Session set';
});

Route::get('/check-session', function () {
    return session('test', 'not working');
});
