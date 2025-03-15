<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemorizationToolController;

// 1) Always present a fresh verse picker
Route::get('/memorization-tool', [MemorizationToolController::class, 'showPicker'])
    ->name('memorization-tool.picker');

// 2) Fetch the verse from the Scripture API
Route::get('/memorization-tool/fetch-verse', [MemorizationToolController::class, 'fetchVerse'])
    ->name('memorization-tool.fetch');

// 3) Display the fetched verse
Route::get('/memorization-tool/display', [MemorizationToolController::class, 'displayVerse'])
    ->name('memorization-tool.display');

Route::get('/', function () {
    return view('home');
});

// Route::get('/memorization-tool', function () {
//     return view('memorization-tool');
// });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
