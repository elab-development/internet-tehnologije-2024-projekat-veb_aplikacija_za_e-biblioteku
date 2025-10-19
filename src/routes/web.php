<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Swagger UI - automatski serviranje
Route::get('/swagger', function () {
    return view('swagger-ui');
});

// Swagger JSON endpoint
Route::get('/api-docs.json', function () {
    $swaggerJson = file_get_contents(storage_path('api-docs/api-docs.json'));
    return response($swaggerJson)->header('Content-Type', 'application/json');
});

require __DIR__.'/auth.php';
