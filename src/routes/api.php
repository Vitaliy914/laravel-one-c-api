<?php

use Illuminate\Support\Facades\Route;
use Vitaliy914\OneCApi\Controllers\OneCApiController;

Route::match(['get', 'post'],'/' . config('one-c.exchange_path'), [OneCApiController::class, 'index'])
    ->middleware(\Illuminate\Session\Middleware\StartSession::class)
    ->name('onecapi.index');
