<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MemorizationToolController;
use App\Http\Controllers\MemorizationController;
use App\Http\Controllers\MemoryBankController;

// -- Site Routes --
// about page
Route::get('/about', function () {
    return view('about');
})->name('about');
// offline page for PWA
Route::view('/offline', 'offline')->name('offline');
// manifest route for PWA
Route::get('/manifest.webmanifest', function () {
    $manifestPath = public_path('build/manifest.webmanifest');
    if (file_exists($manifestPath)) {
        return response()->file($manifestPath, [
            'Content-Type' => 'application/manifest+json'
        ]);
    }
    return response()->json(['error' => 'Manifest not found'], 404);
});
// privacy policy page
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');
// contact page
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// -- Application Routes --

// 1) Always present a fresh verse picker
Route::get('/memorization-tool', [MemorizationToolController::class, 'showPicker'])
    ->name('memorization-tool.picker');

// 2) Fetch the verse from the Scripture API
Route::get('/memorization-tool/fetch-verse', [MemorizationToolController::class, 'fetchVerse'])
    ->name('memorization-tool.fetch');

// 3) Display the fetched verse
Route::get('/memorization-tool/display', [\App\Http\Controllers\MemorizationController::class, 'show'])
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

// Dismiss new user card route
Route::post('/dismiss-new-user-card', function () {
    session(['new_user_card_dismissed' => true]);
    return response()->json(['success' => true]);
})->name('dismiss-new-user-card');

// Daily Quiz routes
Route::get('/daily-quiz', [\App\Http\Controllers\MemorizationController::class, 'show'])
    ->name('daily-quiz');
Route::get('/quiz/setup', function() {
    return view('quiz-setup');
})->name('quiz.setup');
Route::post('/daily-quiz/next', [\App\Http\Controllers\MemorizationController::class, 'nextQuizVerse'])
    ->name('daily-quiz.next');
Route::get('/daily-quiz/results', [\App\Http\Controllers\MemorizationController::class, 'getQuizResults'])
    ->name('daily-quiz.results');

// Quiz route (full screen version of daily quiz)
Route::get('/quiz', [\App\Http\Controllers\MemorizationController::class, 'show'])
    ->name('quiz');

// Super Admin routes
Route::get('/super-admin', [\App\Http\Controllers\SuperAdminController::class, 'index'])->name('super-admin');
Route::get('/super-admin/users', [\App\Http\Controllers\SuperAdminController::class, 'getAllUsers'])->name('super-admin.users');

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
