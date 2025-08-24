<?php

use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Support\Facades\Route;

Route::post('webhook', 'WebhookController')
    ->withoutMiddleware(TrimStrings::class)
    ->name('webhook');
