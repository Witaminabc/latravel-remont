<?php

use Illuminate\Support\Facades\Route;
use Teo\Acf\Http\Controllers\FieldGroupController;
use Teo\Acf\Http\Controllers\FieldValueController;

Route::prefix(config('acf.route_prefix', 'api/admin/acf'))
    ->middleware(config('acf.middleware', ['auth:sanctum']))
    ->group(function () {
        Route::get('/groups', [FieldGroupController::class, 'index']);
        Route::post('/groups', [FieldGroupController::class, 'store']);
        Route::get('/groups/{key}', [FieldGroupController::class, 'show']);
        Route::put('/groups/{key}', [FieldGroupController::class, 'update']);
        Route::delete('/groups/{key}', [FieldGroupController::class, 'destroy']);

        Route::get('/types', [FieldGroupController::class, 'types']);

        Route::get('/values/{type}/{id}', [FieldValueController::class, 'show']);
        Route::put('/values/{type}/{id}', [FieldValueController::class, 'update']);
    });
