<?php

use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

Route::prefix('league/{league}')->group(function () {
    Route::get('/get-data', [LeagueController::class, 'getInitialData']);
    Route::post('/generate-fixtures', [LeagueController::class, 'generateFixtures']);
    Route::post('/simulate-week', [LeagueController::class, 'simulateWeek']);
    Route::post('/simulate-all', [LeagueController::class, 'simulateAll']);
    Route::post('/reset', [LeagueController::class, 'reset']);
});
