<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Team;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'league_id' => 1,
            'name' => $this->faker->city,
            'power' => mt_rand(6000, 10000) / 100,
            'goal_rate' => round( mt_rand(1200, 1900) / 1000, 2),
            'points' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_scored' => 0,
            'goals_conceded' => 0,
            'goal_difference' => 0
        ];
    }
}
