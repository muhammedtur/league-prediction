<?php

namespace App\Http\Controllers;

use App\Factories\LeagueServiceFactory;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    protected $leagueService;

    public function __construct(Request $request)
    {
        $league = $request->route('league');
        $this->leagueService = LeagueServiceFactory::create($league);
    }

    public function showLeaguePage($league)
    {
        return view('league', ['league' => $league, 'teams' => $this->leagueService->getTeamNames()]);
    }

    public function getInitialData()
    {
        return response()->json($this->leagueService->getInitialData());
    }

    public function generateFixtures()
    {
        return response()->json($this->leagueService->generateFixtures());
    }

    public function simulateWeek()
    {
        return response()->json($this->leagueService->simulateWeek());
    }

    public function simulateAll()
    {
        return response()->json($this->leagueService->simulateAllWeeks());
    }

    public function reset()
    {
        return response()->json($this->leagueService->resetSimulation());
    }
}
