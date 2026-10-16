<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AudioDictionaryController;
use App\Http\Controllers\DictionaryController;

// trang chủ
Route::get('/', function () {
    return view('home');
})->name('home');

// trang liên hệ
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

// trang quản lý từ điển âm thanh admin
Route::resource('audio-dictionary', AudioDictionaryController::class)
    ->names('admin.audio-dictionary');

// update status audio admin
Route::post(
    'audio-dictionary/{audioDictionary}/toggle-status',
    [AudioDictionaryController::class, 'toggleStatus']
)->name('admin.audio-dictionary.toggle-status');

Route::get('/dictionary', [DictionaryController::class, 'index'])
    ->name('dictionary.index');

// import file excel
Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        Route::resource('audio-dictionary', AudioDictionaryController::class);

        Route::post(
            'audio-dictionary/import',
            [AudioDictionaryController::class,'import']
        )->name('audio-dictionary.import');

        Route::post(
            'audio-dictionary/{audioDictionary}/toggle-status',
            [AudioDictionaryController::class,'toggleStatus']
        )->name('audio-dictionary.toggle-status');
});
require __DIR__.'/auth.php';