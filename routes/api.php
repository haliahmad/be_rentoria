<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Auth\LoginController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route untuk Register
Route::post('/register', [LoginController::class, 'register']); 

// Route untuk Login
Route::post('/login', [LoginController::class, 'login']);

// Group route dengan middleware 'auth:api'
Route::group(['middleware' => 'auth:api'], function() {
    
    // Route untuk Logout
    Route::post('/logout', [LoginController::class, 'logout']);
});


Route::prefix('admin')->group(function () {
    Route::middleware(['auth:api', 'checkRole:1'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});