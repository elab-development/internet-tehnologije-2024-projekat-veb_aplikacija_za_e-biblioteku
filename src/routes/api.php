<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
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
    
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search'])
        ->middleware(['throttle:30,1']);
    Route::get('/books/fetch-by-isbn', [BookController::class, 'fetchByIsbn'])
        ->middleware(['throttle:10,1']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [ApiAuthController::class, 'user']);
        Route::get('/me', [ApiAuthController::class, 'me']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        
        Route::middleware(['admin'])->group(function () {
            Route::get('/books/export', [BookController::class, 'exportCsv'])
                ->middleware(['throttle:5,1']);
            Route::get('/loans/export', [LoanController::class, 'exportCsv'])
                ->middleware(['throttle:5,1']);
            Route::put('/books/{book}/restore', [BookController::class, 'restore']);
            Route::post('/books/{book}/cover', [BookController::class, 'uploadCover'])
                ->middleware(['throttle:10,1']);
            Route::post('/books/{book}/pdf', [BookController::class, 'uploadPdf'])
                ->middleware(['throttle:5,1']);
        });
        
        Route::get('/books/{book}', [BookController::class, 'show']);
        Route::apiResource('books', BookController::class)->except(['index', 'show']);
        
        Route::apiResource('loans', LoanController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('/loans/{book}/borrow', [LoanController::class, 'borrow']);
        Route::post('/loans/{loan}/return', [LoanController::class, 'return']);
        

        Route::get('/books/{book}/read', [BookController::class, 'readBook'])
            ->middleware(['subscription', 'throttle:5,1']); //5 zahteva po minutu
        
        Route::get('/books/{book}/preview', [BookController::class, 'previewBook'])
            ->middleware(['throttle:10,1']); //10 zahteva
        
        Route::get('/books/{book}/page', [BookController::class, 'readBookPage'])
            ->middleware(['throttle:20,1']); //20 zahteva
    });
});
