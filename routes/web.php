<?php

use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::get('/weather', [WeatherController::class, 'search'])->name('weather.search');
    Route::get('/weather/coords', [WeatherController::class, 'coords'])->name('weather.coords');
});

