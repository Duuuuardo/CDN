<?php

use App\Http\Controllers\MediaController;
use App\Http\Middleware\ApiKeyMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('upload')->middleware(ApiKeyMiddleware::class)->group(function () {
    Route::post('image', [MediaController::class, 'uploadImage']);
    Route::post('video', [MediaController::class, 'uploadVideo']);
});

Route::get('files', [MediaController::class, 'listFiles']);

Route::delete('files/{type}/{name}', [MediaController::class, 'deleteFile'])
    ->where(['type' => 'images|videos', 'name' => '.+'])
    ->middleware(ApiKeyMiddleware::class);
