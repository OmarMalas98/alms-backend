<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controllers\LevelController;
use App\Http\Controllers\Controllers\RoleController;
use App\Http\Controllers\Controllers\StatusController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

Route::prefix('user')->middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/update', [UserController::class, 'update']);
    Route::post('/update_password', [UserController::class, 'updatePassword']);
});

Route::prefix('role')->group(function () {
    Route::get('/index', [RoleController::class, 'index']);
    Route::post('/add', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::post('/update/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
});

Route::prefix('status')->group(function () {
    Route::get('/index', [StatusController::class, 'index']);
    Route::post('/add', [StatusController::class, 'store']);
    Route::get('/{id}', [StatusController::class, 'show']);
    Route::post('/update/{id}', [StatusController::class, 'update']);
    Route::delete('/{id}', [StatusController::class, 'destroy']);
});

Route::prefix('level')->group(function () {
    Route::get('/index', [LevelController::class, 'index']);
    Route::post('/add', [LevelController::class, 'store']);
    Route::get('/{id}', [LevelController::class, 'show']);
    Route::post('/update/{id}', [LevelController::class, 'update']);
    Route::delete('/{id}', [LevelController::class, 'destroy']);
});







