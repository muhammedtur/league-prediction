<?php

namespace Database\Factories;
use App\Models\Fixture;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fixture>
 */
class FixtureFactory extends Factory
{
    protected $model = Fixture::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'league_id' => 1,
            'home_team_id' => rand(1, 4),
            'away_team_id' => rand(1, 4),
            'week' => rand(1, 6),
        ];
    }
}
