<?php

use App\Http\Controllers\SearchController;

Route::get('/', [SearchController::class, 'index'])->name('search.form');
Route::post('/search', [SearchController::class, 'search'])->name('search.run');
