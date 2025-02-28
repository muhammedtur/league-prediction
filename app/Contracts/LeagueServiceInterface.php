<?php

namespace App\Contracts;

interface LeagueServiceInterface
{
    public function getTeamNames(): array;
    public function getInitialData(): array;
    public function generateFixtures(): array;
    public function simulateWeek(): array;
    public function simulateAllWeeks(): array;
    public function resetSimulation(): array;
}
