<template>
    <div class="home">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-12">
            <div class="container mx-auto px-4">
                <h1 class="text-4xl font-bold mb-4">Cricket League Dashboard</h1>
                <p class="text-xl opacity-90">Welcome to your comprehensive cricket management system</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 -mt-8 px-4 mb-8">
            <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-lg shadow-lg p-6 border-t-4"
                 :class="stat.borderColor">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">{{ stat.label }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stat.value }}</p>
                    </div>
                    <div class="text-3xl opacity-50">{{ stat.icon }}</div>
                </div>
            </div>
        </div>

        <!-- Recent & Upcoming Matches -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-4 mb-8">
            <!-- Recent Matches -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Recent Matches</h2>
                <div v-if="loading.recent" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <div v-else-if="recentMatches.length === 0" class="text-gray-500 text-center py-8">
                    No recent matches found
                </div>
                <div v-else class="space-y-4">
                    <div v-for="match in recentMatches" :key="match.match_id" 
                         class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">{{ match.venue?.name }}</span>
                            <span class="text-xs px-2 py-1 rounded" 
                                  :class="getStatusClass(match.status)">
                                {{ match.status }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-sm">
                                <p class="font-medium">{{ match.firstTeam?.team_name }}</p>
                                <p class="text-gray-600">{{ match.first_team_score || 'Yet to bat' }}</p>
                            </div>
                            <div class="text-xs text-gray-500">VS</div>
                            <div class="text-sm text-right">
                                <p class="font-medium">{{ match.secondTeam?.team_name }}</p>
                                <p class="text-gray-600">{{ match.second_team_score || 'Yet to bat' }}</p>
                            </div>
                        </div>
                        <div v-if="match.outcome" class="mt-2 text-xs text-gray-600 italic">
                            {{ match.outcome }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Matches -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Upcoming Matches</h2>
                <div v-if="loading.upcoming" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                </div>
                <div v-else-if="upcomingMatches.length === 0" class="text-gray-500 text-center py-8">
                    No upcoming matches found
                </div>
                <div v-else class="space-y-4">
                    <div v-for="match in upcomingMatches" :key="match.match_id" 
                         class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">{{ match.venue?.name }}</span>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                {{ formatDate(match.match_date) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <div class="text-sm">
                                <p class="font-medium">{{ match.firstTeam?.team_name }}</p>
                                <p class="text-gray-600">{{ match.match_type }} • {{ match.overs }} overs</p>
                            </div>
                            <div class="text-xs text-gray-500">VS</div>
                            <div class="text-sm text-right">
                                <p class="font-medium">{{ match.secondTeam?.team_name }}</p>
                                <p class="text-gray-600">{{ match.match_type }} • {{ match.overs }} overs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6 mx-4">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <router-link to="/countries" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition-colors">
                    <div class="text-2xl mb-2">🌍</div>
                    <p class="text-sm font-medium">Manage Countries</p>
                </router-link>
                <router-link to="/teams" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition-colors">
                    <div class="text-2xl mb-2">👥</div>
                    <p class="text-sm font-medium">Manage Teams</p>
                </router-link>
                <router-link to="/players" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition-colors">
                    <div class="text-2xl mb-2">🏏</div>
                    <p class="text-sm font-medium">Manage Players</p>
                </router-link>
                <router-link to="/matches" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg text-center transition-colors">
                    <div class="text-2xl mb-2">⚡</div>
                    <p class="text-sm font-medium">Manage Matches</p>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import api from '../services/api';

export default {
    name: 'Home',
    data() {
        return {
            stats: [
                { label: 'Countries', value: 0, icon: '🌍', borderColor: 'border-blue-500' },
                { label: 'Teams', value: 0, icon: '👥', borderColor: 'border-green-500' },
                { label: 'Players', value: 0, icon: '🏏', borderColor: 'border-purple-500' },
                { label: 'Venues', value: 0, icon: '🏟️', borderColor: 'border-orange-500' },
                { label: 'Matches', value: 0, icon: '⚡', borderColor: 'border-red-500' }
            ],
            recentMatches: [],
            upcomingMatches: [],
            loading: {
                stats: true,
                recent: true,
                upcoming: true
            }
        }
    },
    async mounted() {
        await this.loadDashboardData();
    },
    methods: {
        async loadDashboardData() {
            try {
                // Load stats
                const statsResponse = await api.getStats();
                if (statsResponse.data.success) {
                    const data = statsResponse.data.data;
                    this.stats[0].value = data.countries;
                    this.stats[1].value = data.teams;
                    this.stats[2].value = data.players;
                    this.stats[3].value = data.venues;
                    this.stats[4].value = data.matches;
                }
                this.loading.stats = false;

                // Load recent matches
                const recentResponse = await api.getMatches({ status: 'completed', per_page: 5 });
                if (recentResponse.data.success) {
                    this.recentMatches = recentResponse.data.data.data;
                }
                this.loading.recent = false;

                // Load upcoming matches
                const upcomingResponse = await api.getMatches({ status: 'scheduled', per_page: 5 });
                if (upcomingResponse.data.success) {
                    this.upcomingMatches = upcomingResponse.data.data.data;
                }
                this.loading.upcoming = false;

            } catch (error) {
                console.error('Error loading dashboard data:', error);
                this.loading.stats = false;
                this.loading.recent = false;
                this.loading.upcoming = false;
            }
        },
        getStatusClass(status) {
            const classes = {
                'completed': 'bg-green-100 text-green-800',
                'live': 'bg-red-100 text-red-800',
                'scheduled': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        },
        formatDate(dateString) {
            if (!dateString) return 'TBD';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
        }
    }
}
</script>
