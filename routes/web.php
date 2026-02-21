<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return file_get_contents(base_path('frontend/dist/index.html'));
});
