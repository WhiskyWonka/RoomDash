<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any?}', function () {
    return file_get_contents(base_path('frontend/dist/index.html'));
})->where('any', '.*');
