<?php
use Itpathsolutions\DBStan\DBStanAnalyzer;
use Illuminate\Support\Facades\Route;
// DBStanController is not used in the code, but it is required for the route definition. So, we need to import it.
use Itpathsolutions\DBStan\Http\Controllers\DBStanController;

// Route::middleware(['web'])->prefix('dbstan')->group(function () {
Route::prefix('dbstan')->group(function () {
        Route::get('/', [DBStanController::class, 'index']);
    });