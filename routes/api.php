<?php

use App\Http\Controllers\Api\AnotacionesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/anotaciones/{numeroSecuencia}', [AnotacionesController::class, 'index']);
});
