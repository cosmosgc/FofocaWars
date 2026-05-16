<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ArmyController;
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
    Route::get('/wars/{war}/armies', [ArmyController::class, 'index'])->name('armies.index');
    Route::get('/wars/{war}/armies/send', fn(\App\Models\War $w) => redirect()->route('armies.index', $w))->name('armies.send');
    Route::post('/wars/{war}/armies/send', [ArmyController::class, 'send']);
    Route::post('/wars/{war}/armies/{army}/recall', [ArmyController::class, 'recall'])->name('armies.recall');
    Route::get('/wars/{war}/cities/{city}', [CityController::class, 'show'])->name('cities.show');
    Route::post('/wars/{war}/cities/{city}/rename', [CityController::class, 'rename'])->name('cities.rename');

    Route::get('/api/wars/{war}/tiles', [MapController::class, 'tiles'])->name('api.wars.tiles');
    Route::get('/api/wars/{war}/cities', [MapController::class, 'cities'])->name('api.wars.cities');
    Route::get('/api/wars/{war}/resources', [ResourceController::class, 'index'])->name('api.wars.resources');
    Route::get('/api/wars/{war}/armies/movements', [\App\Http\Controllers\Api\ArmyController::class, 'movements'])->name('api.wars.armies.movements');
    Route::get('/api/wars/{war}/armies/map-movements', [\App\Http\Controllers\Api\ArmyController::class, 'mapMovements'])->name('api.wars.armies.map-movements');
    Route::get('/api/wars/{war}/unit-types', [\App\Http\Controllers\Api\ArmyController::class, 'unitTypes'])->name('api.wars.unit-types');
    Route::get('/api/wars/{war}/cities/{city}/units', [\App\Http\Controllers\Api\ArmyController::class, 'cityUnits'])->name('api.wars.city-units');
    Route::post('/api/wars/{war}/train', [\App\Http\Controllers\Api\ArmyController::class, 'train'])->name('api.wars.train');
    Route::get('/api/wars/{war}/training-queue', [\App\Http\Controllers\Api\ArmyController::class, 'trainingQueue'])->name('api.wars.training-queue');
    Route::get('/api/wars/{war}/garrisons', [\App\Http\Controllers\Api\ArmyController::class, 'garrisons'])->name('api.wars.garrisons');
    Route::get('/api/wars/{war}/battles/reports', [\App\Http\Controllers\Api\BattleController::class, 'reports'])->name('api.wars.battles.reports');

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
