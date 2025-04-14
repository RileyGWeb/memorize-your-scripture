<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemorizationToolController;
use App\Http\Controllers\MemoryBankController;

// 1) Always present a fresh verse picker
Route::get('/memorization-tool', [MemorizationToolController::class, 'showPicker'])
    ->name('memorization-tool.picker');

// 2) Fetch the verse from the Scripture API
Route::get('/memorization-tool/fetch-verse', [MemorizationToolController::class, 'fetchVerse'])
    ->name('memorization-tool.fetch');

// 3) Display the fetched verse
Route::get('/memorization-tool/display', [MemorizationToolController::class, 'displayVerse'])
    ->name('memorization-tool.display');

// 4) Save the memory verse
Route::post('/memorization-tool/save', [MemorizationToolController::class, 'saveMemory'])
    ->name('memorization-tool.save');

Route::get('/bank/search-verses', [MemoryBankController::class, 'searchVerses'])
     ->name('memory-bank.search-verses');

// The memory bank
Route::get('/bank', [MemoryBankController::class, 'index'])->name('memory-bank.index');

// This method gets a verse based on only provided parameters, the other fetch method relies on a verse selection existing in a session variable
Route::get('/bank/fetch-verse', [MemoryBankController::class, 'fetchVerseText'])
     ->name('memory-bank.fetch-verse');

Route::get('/', function () {
    return view('home');
})->name('home');

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
