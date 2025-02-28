axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

new Vue({
    el: '#app',
    delimiters: ['{{', '}}'],
    data: {
        loading: false,
        initialized: false,
        league: document.getElementById('league').dataset.league,
        teams: [],
        teamNames: {},
        fixtures: [],
        table: [],
        predictions: {},
        currentWeek: 0,
        totalWeeks: 6,
        isSeasonComplete: false,
        editingFixture: null,
        editHomeGoals: 0,
        editAwayGoals: 0,
        errorMessage: null
    },
    computed: {
        showPredictions() {
            return this.currentWeek > 3;
        }
    },
    mounted() {
        console.log("Vue app started");
        this.fetchData();
    },
    watch: {
        currentWeek(newWeek, oldWeek) {
            if (newWeek > 0 && newWeek !== oldWeek) {
                this.$nextTick(() => {
                    try {
                        const tabEl = document.querySelector(`#week-${newWeek}-tab`);
                        if (tabEl) {
                            const tab = new bootstrap.Tab(tabEl);
                            tab.show();
                        }
                    } catch (e) {
                        console.error("Tab error:", e);
                    }
                });
            }
        }
    },
    methods: {
        fetchData() {
            console.log("fetchData requested");
            this.loading = true;
            this.errorMessage = null;
            axios.get(`/api/league/${this.league}/get-data`)
                .then(response => {
                    console.log("API response:", response.data);
                    const data = response.data;
                    this.teams = data.teams || [];
                    this.fixtures = data.fixtures || [];
                    this.table = data.teams || [];
                    this.predictions = data.predictions || {};
                    this.currentWeek = data.currentWeek || 0;
                    this.totalWeeks = data.totalWeeks || 6;
                    this.isSeasonComplete = data.isSeasonComplete || false;
                    this.initialized = data.fixtures.length > 0;
                })
                .catch(error => {
                    this.errorMessage = 'Fetching data: ' + error.message;
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        setupLeague() {
            this.loading = true;
            this.errorMessage = null;

            axios.post(`/api/league/${this.league}/generate-fixtures`)
                .then(response => {
                    console.log("Setup API response:", response.data);
                    return this.fetchData();
                })
                .catch(error => {
                    this.errorMessage = 'League setup error: ' + error.message;
                })
                .finally(() => {
                    if (this.loading) {
                        this.loading = false;
                    }
                });
        },
        playWeek() {
            this.loading = true;
            this.errorMessage = null;

            axios.post(`/api/league/${this.league}/simulate-week`)
                .then(response => {
                    return this.fetchData();
                })
                .catch(error => {
                    this.errorMessage = 'Play week error: ' + error.message;
                })
                .finally(() => {
                    if (this.loading) {
                        this.loading = false;
                    }
                });
        },
        playAllWeeks() {
            this.loading = true;
            this.errorMessage = null;

            axios.post(`/api/league/${this.league}/simulate-all`)
                .then(response => {
                    return this.fetchData();
                })
                .catch(error => {
                    this.errorMessage = 'Play all weeks error: ' + error.message;
                })
                .finally(() => {
                    if (this.loading) {
                        this.loading = false;
                    }
                });
        },
        resetLeague() {
            if (!confirm('Are you sure to reset league?')) return;

            this.loading = true;
            this.errorMessage = null;

            axios.post(`/api/league/${this.league}/reset`)
                .then(response => {
                    alert("League has been reset!");
                    return this.fetchData();
                })
                .catch(error => {
                    this.errorMessage = 'League reset error: ' + error.message;
                })
                .finally(() => {
                    if (this.loading) {
                        this.loading = false;
                    }
                });
        },
        getFixturesByWeek(week) {
            return this.fixtures.filter(fixture => fixture.week === parseInt(week));
        },
        getTeamById(id) {
            return this.teams.find(team => team.id === id);
        },
        getWinProbability(teamName) {
            return this.predictions && this.predictions[teamName] ? this.predictions[teamName] + '%' : '0%';
        }
    }
});
