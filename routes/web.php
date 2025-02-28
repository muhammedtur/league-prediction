<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;


Route::prefix('league/{league}')->group(function () {
    Route::get('/', [LeagueController::class, 'showLeaguePage'])->name('league.showLeaguePage');
});
