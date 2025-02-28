<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'power', 'goal_rate', 'points', 'won', 'drawn', 'lost', 'goals_scored', 'goals_conceded', 'goal_difference'];

    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function homeFixtures()
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    public function awayFixtures()
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    public function updateStats(int $goalsScored, int $goalsConceded): void
    {
        $this->goals_scored += $goalsScored;
        $this->goals_conceded += $goalsConceded;
        $this->goal_difference += $goalsScored - $goalsConceded;

        if ($goalsScored > $goalsConceded) {
            $this->won += 1;
            $this->points += 3;
        } elseif ($goalsScored === $goalsConceded) {
            $this->drawn += 1;
            $this->points += 1;
        } else {
            $this->lost += 1;
        }

        $this->save();
    }
}
