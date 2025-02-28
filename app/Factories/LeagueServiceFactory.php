<?php

namespace App\Factories;

use App\Contracts\LeagueServiceInterface;
use App\Services\InsiderLeagueService;
use App\Services\PremierLeagueService;
use App\Services\ChampionsLeagueService;
use App\Services\GenericLeagueService;
use InvalidArgumentException;

class LeagueServiceFactory
{
    public static function create(string $leagueType): LeagueServiceInterface
    {
        return match ($leagueType) {
            'insider' => new InsiderLeagueService(),
            'premier' => new PremierLeagueService(),
            'champions' => new ChampionsLeagueService(),
            default => new GenericLeagueService($leagueType)
        };
    }
}
