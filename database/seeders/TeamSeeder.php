<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\League;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $insiderLeague = League::where('name', 'InsiderLeague')->first();

        if (!$insiderLeague) {
            return;
        }

        $teams = [
            'Liverpool',
            'Arsenal',
            'Manchester City',
            'Chelsea'
        ];

        foreach ($teams as $team) {
            Team::create([
                'league_id' => $insiderLeague->id,
                'name' => $team,
                'power' => rand(50, 100),
                'goal_rate' => rand(120, 250) / 100,
                'points' => 0,
                'goals_scored' => 0,
                'goals_conceded' => 0,
            ]);
        }
    }
}
