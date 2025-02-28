<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\League;
use App\Models\Team;
use App\Models\Fixture;
use App\Models\Game;

class LeagueSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_fixtures()
    {
        $league = League::factory()->create();
        Team::factory(4)->create(['league_id' => $league->id]);

        $response = $this->postJson("/api/league/{$league->name}/generate-fixtures");

        $response->assertStatus(200)
            ->assertJsonStructure(['fixtures']);

        $this->assertDatabaseCount('fixtures', 6);
    }

    public function test_simulate_week()
    {
        $league = League::factory()->create();
        Team::factory(4)->create(['league_id' => $league->id]);
        Fixture::factory(6)->create(['league_id' => $league->id]);

        $response = $this->postJson("/api/league/{$league->name}/simulate-week");

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'games', 'standings']);

        $this->assertDatabaseHas('games', ['week' => 1]);
    }

    public function test_championship_analysis()
    {
        $league = League::factory()->create();
        Team::factory(4)->create(['league_id' => $league->id]);
        Fixture::factory(6)->create(['league_id' => $league->id]);

        for ($i = 0; $i < 6; $i++) {
            $this->postJson("/api/league/{$league->name}/simulate-week");
        }

        $response = $this->postJson("/api/league/{$league->name}/simulate-week");

        $response->assertStatus(200)
            ->assertJsonStructure(['championship_predictions']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->league = League::factory()->create(['name' => 'insider']);

        Team::factory(4)->create(['league_id' => $this->league->id]);
    }

}
