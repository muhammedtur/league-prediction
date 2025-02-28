<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Insider Champions League</title>
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<div id="app" class="container py-4">
    <h1 class="text-center mb-4">Insider Champions League</h1>

    <div id="league" data-league='{{ $league }}'></div>

    <div v-if="loading" class="text-center my-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">League data loading...</p>
    </div>

    <div v-if="errorMessage" class="alert alert-danger text-center my-5">
        <strong>Error!</strong> @{{ errorMessage }}
    </div>

    <div v-if="!initialized && !loading" class="text-center my-5">
        <div class="card">
            <div class="card-body">
                @foreach($teams as $team)
                    <h5>{{ $team }}</h5>
                @endforeach
                <button @click="setupLeague" class="btn btn-primary btn-md">Generate Fixtures</button>
            </div>
        </div>
    </div>

    <div v-if="initialized && !loading" class="row mb-4">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Week @{{ currentWeek }} / @{{ totalWeeks }}</h4>
                        <div>
                            <button
                                v-if="isSeasonComplete"
                                @click="resetLeague"
                                class="btn btn-success btn-sm me-2">
                                Start New Season
                            </button>
                            <button
                                v-if="currentWeek < totalWeeks && !isSeasonComplete"
                                @click="playWeek"
                                class="btn btn-light btn-sm me-2">
                                Play Next Week
                            </button>
                            <button
                                v-if="currentWeek < totalWeeks && !isSeasonComplete"
                                @click="playAllWeeks"
                                class="btn btn-warning btn-sm me-2">
                                Play All Weeks
                            </button>
                            <button
                                @click="resetLeague"
                                class="btn btn-danger btn-sm">
                                Reset League
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h5 class="card-title">League Status</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                            <tr>
                                <th>Order</th>
                                <th>Team</th>
                                <th class="text-center">P</th>
                                <th class="text-center">W</th>
                                <th class="text-center">D</th>
                                <th class="text-center">L</th>
                                <th class="text-center">GD</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(team, index) in table" :key="team.id">
                                <td>@{{ index + 1 }}</td>
                                <td>@{{ team.name }}</td>
                                <td class="text-center">@{{ team.points }}</td>
                                <td class="text-center">@{{ team.won }}</td>
                                <td class="text-center">@{{ team.drawn }}</td>
                                <td class="text-center">@{{ team.lost }}</td>
                                <td class="text-center">@{{ team.goal_difference }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Şampiyonluk Tahminleri Tablosu -->
    <div v-if="showPredictions && initialized && !loading" class="row mb-4">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">Championship Predictions</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                            <tr>
                                <th>Team</th>
                                <th class="text-center">Championship Prediction</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="team in table" :key="'prediction-'+team.id">
                                <td>@{{ team.name }}</td>
                                <td class="text-center">
                                    @{{ getWinProbability(team.name) }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="initialized && !loading" class="row mb-4">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Matches</h4>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs" id="fixtureTabs" role="tablist">
                        <li class="nav-item" role="presentation" v-for="week in totalWeeks" :key="'tab-'+week">
                            <button
                                class="nav-link"
                                :class="{ active: week === 1 }"
                                :id="'week-'+week+'-tab'"
                                data-bs-toggle="tab"
                                :data-bs-target="'#week-'+week"
                                type="button"
                                role="tab">
                                Week @{{ week }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="fixtureTabContent">
                        <div
                            v-for="week in totalWeeks"
                            :key="'content-'+week"
                            class="tab-pane fade"
                            :class="{ 'show active': week === 1 }"
                            :id="'week-'+week"
                            role="tabpanel">

                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Home</th>
                                    <th class="text-center">Score</th>
                                    <th>Away</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="fixture in getFixturesByWeek(week)" :key="fixture.id" :class="{'table-info': week === currentWeek}">
                                    <td>@{{ fixture.home_team.name }}</td>
                                    <td v-if="fixture.game?.home_score !== undefined" class="text-center">
                                        @{{ fixture.game.home_score }} - @{{ fixture.game.away_score }}
                                    </td>
                                    <td v-else class="text-center">
                                        vs
                                    </td>
                                    <td>@{{ fixture.away_team.name }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/vue.js') }}"></script>
<script src="{{ asset('js/axios.min.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
