<?php

use Illuminate\Support\Facades\Route;
use App\Models\Color;

Route::get('/colors', function () {
    return Color::all();
});