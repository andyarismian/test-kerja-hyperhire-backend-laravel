<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\PersonLikeController;

Route::prefix('v1')->group(function () {
    Route::get('/people/{id}', [PersonController::class, 'index']);
    Route::post('/people/{id}/like', [PersonController::class, 'like']);
    Route::post('/people/{id}/dislike', [PersonController::class, 'dislike']);
    Route::get('/people/{id}/likes', [PersonLikeController::class, 'getLikes']);
});

// Swagger UI route
Route::get('/docs', function () {
    return view('swagger');
})->name('api.docs');
