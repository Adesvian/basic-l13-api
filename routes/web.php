<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/api/couriers');
Route::get('/health', fn () => response()->json(['status' => 'ok']));
