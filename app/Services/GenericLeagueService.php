<?php

namespace App\Services;

use App\Contracts\LeagueServiceInterface;
use App\Models\League;
use App\Models\Team;
use App\Models\Fixture;
use App\Models\Game;

class GenericLeagueService implements LeagueServiceInterface
{
    protected $league;

    public function __construct(string $leagueName)
    {
        $this->league = League::where('name', $leagueName)->firstOrFail();
    }

    public function getTeamNames(): array
    {
        return ['message' => "{$this->league->name} teams are not available."];
    }

    public function getInitialData(): array
    {
        return [
            'teams' => Team::where('league_id', $this->league->id)->get(),
            'fixtures' => Fixture::where('league_id', $this->league->id)->get(),
        ];
    }

    public function generateFixtures(): array
    {
        return ['message' => "{$this->league->name} fixtures are not available."];
    }

    public function simulateWeek(): array
    {
        return ['message' => "{$this->league->name} simulation is not available."];
    }

    public function simulateAllWeeks(): array
    {
        return ['message' => "{$this->league->name} simulation is not available."];
    }

    public function resetSimulation(): array
    {
        return ['message' => "{$this->league->name} has been reset."];
    }
}
