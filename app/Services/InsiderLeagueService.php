<?php

namespace App\Services;

use App\Contracts\LeagueServiceInterface;
use App\Models\League;
use App\Models\Team;
use App\Models\Fixture;
use App\Models\Game;

class InsiderLeagueService implements LeagueServiceInterface
{
    protected $league;

    public function __construct()
    {
        $this->league = League::where('name', 'InsiderLeague')->firstOrFail();
    }

    public function getTeamNames(): array
    {
        return Team::where('league_id', $this->league->id)->get()->pluck('name')->toArray();
    }

    public function getInitialData(): array
    {
        $teams = Team::where('league_id', $this->league->id)
            ->orderByDesc('points')
            ->orderByDesc('goals_scored')
            ->orderByDesc('goal_difference')
            ->get();

        $totalWeeks = 6;
        $currentWeek = Game::where('league_id', $this->league->id)->max('week') ?? 0;

        return [
            'teams' => $teams,
            'predictions' => $this->calculateChampionshipOdds($currentWeek),
            'isSeasonCompleted' => $currentWeek >= $totalWeeks,
            'currentWeek' => $currentWeek,
            'totalWeeks' => $totalWeeks,
            'fixtures' => Fixture::where('league_id', $this->league->id)->with(['homeTeam', 'awayTeam', 'games' => function ($query) {
                $query->limit(1);
            }])->get()->map(function ($fixture) {
                $fixture->game = $fixture->games->first();
                unset($fixture->games);
                return $fixture;
            }),
        ];
    }

    public function generateFixtures(): array
    {
        Fixture::where('league_id', $this->league->id)->delete();

        $teams = Team::where('league_id', $this->league->id)->pluck('id')->toArray();

        if (count($teams) !== 4) {
            return ['error' => 'Four teams are required to generate fixtures!'];
        }

        $fixtures = [];
        $week = 1;

        $matchups = [
            [0, 1, 2, 3], // 1. week
            [0, 2, 1, 3], // 2. week
            [0, 3, 1, 2], // 3. week
            [1, 0, 3, 2], // 4. week
            [2, 0, 3, 1], // 5. week
            [3, 0, 2, 1], // 6. week
        ];

        foreach ($matchups as $matchup) {
            $fixtures[] = Fixture::create([
                'league_id' => $this->league->id,
                'home_team_id' => $teams[$matchup[0]],
                'away_team_id' => $teams[$matchup[1]],
                'week' => $week,
            ]);
            $fixtures[] = Fixture::create([
                'league_id' => $this->league->id,
                'home_team_id' => $teams[$matchup[2]],
                'away_team_id' => $teams[$matchup[3]],
                'week' => $week,
            ]);

            $week++;
        }

        return ['message' => 'Fixtures generated successfully.', 'fixtures' => $fixtures];
    }

    public function simulateWeek(): array
    {
        $maxWeeks = 6;
        $currentWeek = Game::where('league_id', $this->league->id)->max('week');
        $nextWeek = $currentWeek ? $currentWeek + 1 : 1;

        if ($nextWeek > $maxWeeks) {
            return ['message' => 'All weeks have been simulated.'];
        }

        $playedFixtureIds = Game::where('league_id', $this->league->id)
            ->get()
            ->pluck('fixture_id')
            ->toArray();

        $nextWeekMatches = Fixture::where('league_id', $this->league->id)
            ->where('week', $nextWeek)
            ->whereNotIn('id', $playedFixtureIds)
            ->get();

        if ($nextWeekMatches->count() < 2) {
            return ['message' => "Error: Not enough matches for week {$nextWeek}.", 'matches' => $nextWeekMatches];
        }

        $simulatedGames = [];

        foreach ($nextWeekMatches as $match) {
            $homeTeam = Team::find($match->home_team_id);
            $awayTeam = Team::find($match->away_team_id);

            $simulation = $this->simulateMatch($homeTeam, $awayTeam);

            $homeScore = $simulation['home'];
            $awayScore = $simulation['away'];

            $homeTeam->updateStats($homeScore, $awayScore);
            $awayTeam->updateStats($awayScore, $homeScore);

            $simulatedGames[] = Game::create([
                'league_id' => $this->league->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'week' => $nextWeek,
                'fixture_id' => $match->id,
            ]);
        }

        $championshipPredictions = $this->calculateChampionshipOdds($currentWeek);

        return ['message' => "Week {$nextWeek} simulated successfully.", 'games' => $simulatedGames, 'predictions' => $championshipPredictions];
    }

    public function simulateAllWeeks(): array
    {
        $maxWeeks = 6;
        $simulatedWeeks = [];

        while (true) {
            $currentWeek = Game::where('league_id', $this->league->id)->max('week');
            $nextWeek = $currentWeek ? $currentWeek + 1 : 1;

            if ($nextWeek > $maxWeeks) {
                return ['message' => 'All weeks have been simulated.', 'simulatedWeeks' => $simulatedWeeks];
            }

            $weekResult = $this->simulateWeek();
            $simulatedWeeks[] = $weekResult;
        }
    }

    public function resetSimulation(): array
    {
        Game::where('league_id', $this->league->id)->delete();
        Fixture::where('league_id', $this->league->id)->delete();

        $teams = Team::where('league_id', $this->league->id)->get();

        foreach ($teams as $team) {
            $power = mt_rand(6000, 10000) / 100;
            $goalRate = mt_rand(1200, 1900) / 1000;

            $team->update([
                'power' => $power,
                'goal_rate' => $goalRate,
                'points' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_scored' => 0,
                'goals_conceded' => 0,
                'goal_difference' => 0
            ]);
        }

        return ['message' => 'Simulation has been reset.'];
    }

    private function simulateMatch($homeTeam, $awayTeam): array
    {
        $powerDifference = ($homeTeam->power - $awayTeam->power) / 100;

        $pointDifference = ($homeTeam->points - $awayTeam->points) / 10;

        $homeAdvantage = 0.3;

        $homeLambda = max(0.5, $homeTeam->goal_rate + ($powerDifference * 1.5) + ($pointDifference * 0.5) + $homeAdvantage);
        $awayLambda = max(0.5, $awayTeam->goal_rate - ($powerDifference * 1.5) - ($pointDifference * 0.5));

        $homeScore = $this->poissonRandom($homeLambda);
        $awayScore = $this->poissonRandom($awayLambda);

        return ['home' => $homeScore, 'away' => $awayScore];
    }

    private function poissonRandom($lambda): int
    {
        $L = exp(-$lambda);
        $k = 0;
        $p = 1;

        do {
            $k++;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L);

        return $k - 1;
    }

    private function getStandings()
    {
        $teams = Team::where('league_id', $this->league->id)->get();
        $sortedTeams = $teams->sort(function ($a, $b) {
            if ($a->points != $b->points) {
                return $b->points - $a->points;
            }
            if ($a->goal_difference != $b->goal_difference) {
                return $b->goal_difference - $a->goal_difference;
            }
            if ($a->goals_scored != $b->goals_scored) {
                return $b->goals_scored - $a->goals_scored;
            }

            // HEAD-TO-HEAD
            $headToHeadA = Game::where('home_team_id', $a->id)->where('away_team_id', $b->id)->sum('home_score') +
                Game::where('home_team_id', $b->id)->where('away_team_id', $a->id)->sum('away_score');

            $headToHeadB = Game::where('home_team_id', $b->id)->where('away_team_id', $a->id)->sum('home_score') +
                Game::where('home_team_id', $a->id)->where('away_team_id', $b->id)->sum('away_score');

            return $headToHeadB - $headToHeadA;
        });

        return $sortedTeams->values();
    }

    private function calculateChampionshipOdds($currentWeek): array
    {
        $standings = $this->getStandings();

        if (intval($currentWeek) < 3) {
            return collect($standings)->mapWithKeys(fn($team) => [$team->name => 0])->toArray();
        }

        $maxPoints = (6 - $currentWeek) * 3;
        $leaderPoints = $standings->first()->points;

        $weights = [];
        foreach ($standings as $team) {
            $potentialPoints = $team->points + $maxPoints;
            $weights[$team->name] = $potentialPoints;
        }

        $maxPossiblePoints = max($weights);
        if ($maxPossiblePoints == $leaderPoints) {
            return collect($standings)->mapWithKeys(fn($team) => [$team->name => ($team->name == $standings->first()->name) ? 100 : 0])->toArray();
        }

        $totalWeight = array_sum($weights);

        return array_map(function ($weight) use ($totalWeight) {
            return round(($weight / $totalWeight) * 100, 2);
        }, $weights);
    }
}
