<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/login', [LoginController::class, 'loginweb'])->name('login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login.get');
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard')->middleware('auth.check');
Route::post('/admin/verifySchool/{school}', [AdminController::class, 'verifySchool'])->name('admin.verifySchool');
Route::delete('/admin/rejectSchool/{school}', [AdminController::class, 'rejectSchool'])->name('admin.rejectSchool');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
