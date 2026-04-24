<?php

use Illuminate\Support\Facades\Route;

// Dashboard SPA — everything under /dashboard/*
Route::get('/dashboard/{any?}', fn () => view('dashboard.app'))
    ->where('any', '.*')
    ->name('dashboard.spa');

// Web SPA — everything else except API/REST/Sanctum
Route::get('/{any?}', fn () => view('web.app'))
    ->where('any', '^(?!api|rest|sanctum|up|horizon|telescope).*')
    ->name('web.spa');
