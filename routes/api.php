<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\OtpController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\School\SchoolClassController;
use App\Http\Controllers\Api\School\SchoolController;
use App\Http\Controllers\Api\Student\StudentController;
use App\Http\Controllers\profile\ProfileController;
use App\Http\Controllers\QuoteController;
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
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/verify', [OtpController::class, 'verify']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user/{user}', [ProfileController::class, 'update']);

   // Route::apiResource('schools', SchoolController::class);
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index']);
        Route::post('/', [SchoolController::class, 'store']);
        Route::get('/{school}', [SchoolController::class, 'show']);
        Route::put('/{school}', [SchoolController::class, 'update']);
        Route::delete('/{school}', [SchoolController::class, 'destroy']);
        Route::post('/join', [SchoolController::class, 'joinSchool']);
    });

    Route::prefix('classes')->group(function () {
        Route::get('/', [SchoolClassController::class, 'index']);
        Route::get('/{id}', [SchoolClassController::class, 'show']);
        Route::post('/', [SchoolClassController::class, 'store']);
        Route::get('/student/{studentId}', [SchoolClassController::class, 'classesForStudent']);
        Route::put('/{id}', [SchoolClassController::class, 'update']);
        Route::delete('/{id}', [SchoolClassController::class, 'destroy']);
    });

    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::post('/', [StudentController::class, 'store']);
        Route::post('/{studentId}/classes', [StudentController::class, 'addStudentToClass']);
        Route::get('/{studentId}/classes', [StudentController::class, 'classesForStudent']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::delete('/{id}', [StudentController::class, 'destroy']);
    });

});









Route::post('/quotes', [QuoteController::class, 'store']);
Route::get('/quotes/random', [QuoteController::class, 'showRandom']);
