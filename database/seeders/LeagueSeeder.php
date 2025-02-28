<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\League;

class LeagueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        League::create(['name' => 'InsiderLeague']);
        League::create(['name' => 'PremierLeague']);
        League::create(['name' => 'ChampionsLeague']);
    }
}
