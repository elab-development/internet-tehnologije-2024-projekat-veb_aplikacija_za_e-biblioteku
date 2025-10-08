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
    Route::post('/login', [ApiAuthController::class, 'login']);
    
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/search', [BookController::class, 'search']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', [ApiAuthController::class, 'user']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        
        Route::apiResource('books', BookController::class)->except(['index', 'show']);
        
        Route::apiResource('loans', LoanController::class)->only(['index', 'store', 'show', 'update']);
        Route::post('/loans/{book}/borrow', [LoanController::class, 'borrow']);
        Route::post('/loans/{loan}/return', [LoanController::class, 'return']);
        
        Route::middleware(['admin'])->group(function () {
            Route::put('/books/{book}/restore', [BookController::class, 'restore']);
            Route::post('/books/{book}/cover', [BookController::class, 'uploadCover']);
            Route::post('/books/{book}/pdf', [BookController::class, 'uploadPdf']);
        });
        
        Route::get('/books/{book}/pdf', [BookController::class, 'downloadPdf']);
    });
});
