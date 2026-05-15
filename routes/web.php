<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\ResourceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/wars', [WarController::class, 'index'])->name('wars.index');
    Route::get('/wars/{war}', [WarController::class, 'show'])->name('wars.show');
    Route::post('/wars/{war}/join', [WarController::class, 'join'])->name('wars.join');
    Route::post('/wars/{war}/start', [WarController::class, 'start'])->name('wars.start');
    Route::get('/wars/{war}/map', [WarController::class, 'map'])->name('wars.map');
    Route::get('/wars/{war}/cities/{city}', [CityController::class, 'show'])->name('cities.show');

    Route::get('/api/wars/{war}/tiles', [MapController::class, 'tiles'])->name('api.wars.tiles');
    Route::get('/api/wars/{war}/cities', [MapController::class, 'cities'])->name('api.wars.cities');
    Route::get('/api/wars/{war}/resources', [ResourceController::class, 'index'])->name('api.wars.resources');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/wars', [\App\Http\Controllers\Admin\WarController::class, 'index'])->name('wars.index');
        Route::get('/wars/create', [\App\Http\Controllers\Admin\WarController::class, 'create'])->name('wars.create');
        Route::post('/wars', [\App\Http\Controllers\Admin\WarController::class, 'store'])->name('wars.store');
        Route::get('/wars/{war}/edit', [\App\Http\Controllers\Admin\WarController::class, 'edit'])->name('wars.edit');
        Route::put('/wars/{war}', [\App\Http\Controllers\Admin\WarController::class, 'update'])->name('wars.update');
        Route::delete('/wars/{war}', [\App\Http\Controllers\Admin\WarController::class, 'destroy'])->name('wars.destroy');
    });
});

require __DIR__.'/auth.php';
