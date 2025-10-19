<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LikeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'time' => now()->toISOString()
        ]);
    });

    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::post('/login', [ApiAuthController::class, 'login'])
        ->middleware(['throttle:5,1']); // 5 pokušaja po minuti
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/books/export', [BookController::class, 'exportCsv'])
            ->middleware(['throttle:5,1']);
        Route::get('/books/export.csv', [BookController::class, 'exportCsv'])
            ->middleware(['throttle:5,1']);
    });
    
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search'])
        ->middleware(['throttle:30,1']);
    Route::get('/books/fetch-by-isbn', [BookController::class, 'fetchByIsbn'])
        ->middleware(['throttle:10,1']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::get('/books/{book}/preview', [BookController::class, 'previewBook'])
        ->middleware(['throttle:10,1']);
    Route::get('/books/{book}/like-status', [LikeController::class, 'status']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/subscriptions', [App\Http\Controllers\SubscriptionController::class, 'create']);
        Route::get('/subscriptions/status', [App\Http\Controllers\SubscriptionController::class, 'status']);
        Route::get('/subscriptions/history', [App\Http\Controllers\SubscriptionController::class, 'history']);
    });
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [ApiAuthController::class, 'user']);
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        
                Route::apiResource('books', BookController::class)->except(['index', 'show']);
                Route::post('/books/{book}', [BookController::class, 'update'])->name('books.update.post');
        
        Route::apiResource('loans', LoanController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('/loans/{book}/borrow', [LoanController::class, 'borrow']);
        Route::post('/loans/{loan}/return', [LoanController::class, 'return']);
        

        Route::get('/books/{book}/read', [BookController::class, 'readBook'])
            ->middleware(['throttle:5,1']); //5 zahteva po minutu
        
        Route::get('/books/{book}/pdf', [BookController::class, 'viewPdf'])
            ->middleware(['throttle:5,1']);
        
        Route::get('/books/{book}/page', [BookController::class, 'readBookPage'])
            ->middleware(['throttle:20,1']); //20 zahteva
        
        Route::post('/books/{book}/like', [LikeController::class, 'toggle']);
    });
});
