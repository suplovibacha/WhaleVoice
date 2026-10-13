<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AccountController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('accounts', AccountController::class);

    });

Route::get('/accounts', [AccountController::class, 'index'])
    ->name('admin.accounts.index');

Route::patch(
    '/accounts/{account}/toggle-status',
    [AccountController::class, 'toggleStatus']
)->name('admin.accounts.toggleStatus');

require __DIR__.'/auth.php';