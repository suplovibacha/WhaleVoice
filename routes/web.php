
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AudioDictionaryController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\LocalAudioController;

// Trang chủ
Route::get('/', function () {
    return view('home');
})->name('home');

// Trang liên hệ
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Từ điển
Route::get('/dictionary', [DictionaryController::class, 'index'])
    ->name('dictionary.index');
// Phát âm thanh từ thư mục WAV local
Route::get('/audio/local', [LocalAudioController::class, 'show'])
    ->name('audio.local');

// Các trang yêu cầu đăng nhập
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Khu vực quản trị: chỉ admin
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {

            Route::get('/', function () {
                return view('admin.dashboard');
            })->name('dashboard');

            Route::resource('accounts', AccountController::class);

            Route::resource('audio-dictionary', AudioDictionaryController::class);

            Route::post(
                'audio-dictionary/import',
                [AudioDictionaryController::class, 'import']
            )->name('audio-dictionary.import');

            Route::post(
                'audio-dictionary/{audioDictionary}/toggle-status',
                [AudioDictionaryController::class, 'toggleStatus']
            )->name('audio-dictionary.toggle-status');
        });
});

require __DIR__.'/auth.php';
