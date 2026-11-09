<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MenuController::class, 'index'])->name('menu');
Route::post('/pesanan', [MenuController::class, 'store'])->middleware('throttle:15,1')->name('orders.store');
Route::get('/lacak/{token}', [MenuController::class, 'track'])->name('orders.track');

Route::get('/staf/masuk', [StaffController::class, 'login'])->middleware('guest')->name('staff.login');
Route::post('/staf/masuk', [StaffController::class, 'authenticate'])->middleware(['guest', 'throttle:5,1'])->name('staff.authenticate');
Route::middleware('auth')->prefix('staf')->name('staff.')->group(function () {
    Route::get('/', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::patch('/menu/{dish}/ketersediaan', [StaffController::class, 'toggleDish'])->name('dishes.toggle');
    Route::patch('/pesanan/{order}', [StaffController::class, 'updateOrder'])->name('orders.update');
    Route::post('/keluar', [StaffController::class, 'logout'])->name('logout');
});
