<template>
    <div class="analytics">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Analytics Dashboard</h1>
                    <p class="text-gray-600 mt-2">Comprehensive cricket league statistics and insights</p>
                </div>
                <div class="flex gap-2">
                    <button @click="viewMode = 'cards'" 
                            :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h1a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V6a2 2 0 012-2H4zM16 6a2 2 0 012-2h1a2 2 0 012 2v10a2 2 0 01-2 2h-1a2 2 0 01-2-2V6a2 2 0 012-2h4zM12 6a2 2 0 012-2h1a2 2 0 012 2v10a2 2 0 01-2 2h-1a2 2 0 01-2-2V6a2 2 0 012-2h4z" />
                        </svg>
                        Card View
                    </button>
                    <button @click="viewMode = 'table'" 
                            :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-4 py-2 rounded-md transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14" />
                        </svg>
                        Table View
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in stats" :key="stat.label" 
                 class="bg-white rounded-lg shadow-lg p-6 border-t-4"
                 :class="stat.borderColor">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">{{ stat.label }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ stat.value }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ stat.change }}</p>
                    </div>
                    <div class="text-3xl opacity-50">{{ stat.icon }}</div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div v-if="viewMode === 'cards'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Teams by Country Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Teams by Country</h2>
                <div v-if="loading.charts" class="h-64 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <DoughnutChart v-else :data="teamsByCountryData" :options="chartOptions" />
            </div>

            <!-- Players by Role Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Players by Role</h2>
                <div v-if="loading.charts" class="h-64 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
                </div>
                <BarChart v-else :key="playersByRoleData.datasets[0].data.length" :data="playersByRoleData" :options="chartOptions" />
            </div>

            <!-- Matches by Status Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Matches by Status</h2>
                <div v-if="loading.charts" class="h-64 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                </div>
                <PieChart v-else :data="matchesByStatusData" :options="chartOptions" />
            </div>

            <!-- Venue Capacity Chart -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Top Venues by Capacity</h2>
                <div v-if="loading.charts" class="h-64 flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                </div>
                <BarChart v-else :data="venueCapacityData" :options="horizontalBarOptions" />
            </div>
        </div>

        <!-- Table View -->
        <div v-else class="space-y-6 mb-8">
            <!-- Teams by Country Table -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Teams by Country</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(count, country) in teamsByCountryData.datasets[0].data" :key="country">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ teamsByCountryData.labels[teamsByCountryData.datasets[0].data.indexOf(count)] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ((count / teamsByCountryData.datasets[0].data.reduce((a, b) => a + b, 0)) * 100).toFixed(1) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Players by Role Table -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Players by Role</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Players Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(count, role) in playersByRoleData.datasets[0].data" :key="role">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ playersByRoleData.labels[playersByRoleData.datasets[0].data.indexOf(count)] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ((count / playersByRoleData.datasets[0].data.reduce((a, b) => a + b, 0)) * 100).toFixed(1) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Matches by Status Table -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Matches by Status</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matches Count</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(count, status) in matchesByStatusData.datasets[0].data" :key="status">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ matchesByStatusData.labels[matchesByStatusData.datasets[0].data.indexOf(count)] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ((count / matchesByStatusData.datasets[0].data.reduce((a, b) => a + b, 0)) * 100).toFixed(1) }}%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Venue Capacity Table -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold mb-4 text-gray-800">Top Venues by Capacity</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(capacity, venue) in venueCapacityData.datasets[0].data" :key="venue">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ venueCapacityData.labels[venueCapacityData.datasets[0].data.indexOf(capacity)] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ capacity?.toLocaleString() || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <!-- City would need to be added to the data -->
                                    -
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Recent Activity</h2>
            <div v-if="loading.activity" class="h-32 flex items-center justify-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>
            <div v-else class="space-y-3">
                <div v-for="activity in recentActivity" :key="activity.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full" :class="activity.color"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                            <p class="text-xs text-gray-500">{{ activity.description }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ activity.time }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Bar } from 'vue-chartjs';
import { Pie } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement
} from 'chart.js';
import api from '../services/api';
import { useToast } from '../composables/useToast';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement
);

export default {
    name: 'Analytics',
    components: {
        DoughnutChart: Doughnut,
        BarChart: Bar,
        PieChart: Pie
    },
    setup() {
        const { success, error } = useToast();
        const loading = ref({
            stats: true,
            charts: true,
            activity: true
        });
        const stats = ref([]);
        const recentActivity = ref([]);
        const viewMode = ref('cards');
        
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 2,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        };

        const horizontalBarOptions = {
            ...chartOptions,
            indexAxis: 'y',
        };

        const teamsByCountryData = ref({
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'
                ]
            }]
        });

        const playersByRoleData = ref({
            labels: [],
            datasets: [{
                label: 'Players',
                data: [],
                backgroundColor: '#10B981'
            }]
        });

        const matchesByStatusData = ref({
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']
            }]
        });

        const venueCapacityData = ref({
            labels: [],
            datasets: [{
                label: 'Capacity',
                data: [],
                backgroundColor: '#F59E0B'
            }]
        });

        onMounted(async () => {
            await loadAnalytics();
        });

        const loadAnalytics = async () => {
            try {
                // Load stats
                const statsResponse = await api.getStats();
                if (statsResponse.data.success) {
                    const data = statsResponse.data.data;
                    stats.value = [
                        { label: 'Countries', value: data.countries, icon: '🌍', borderColor: 'border-blue-500', change: '+12%' },
                        { label: 'Teams', value: data.teams, icon: '👥', borderColor: 'border-green-500', change: '+8%' },
                        { label: 'Players', value: data.players, icon: '🏏', borderColor: 'border-purple-500', change: '+15%' },
                        { label: 'Matches', value: data.matches, icon: '⚡', borderColor: 'border-red-500', change: '+20%' }
                    ];
                }
                loading.value.stats = false;

                // Load chart data
                await loadChartData();
                loading.value.charts = false;

                // Load recent activity
                loadRecentActivity();
                loading.value.activity = false;

            } catch (err) {
                error('Failed to load analytics data');
                console.error(err);
            }
        };

        const loadChartData = async () => {
            try {
                // Teams by country
                const teamsResponse = await api.getTeams({ per_page: 100 });
                if (teamsResponse.data.success) {
                    const teams = teamsResponse.data.data;
                    const countryCounts = {};
                    teams.forEach(team => {
                        const country = team.country?.name || 'Unknown';
                        countryCounts[country] = (countryCounts[country] || 0) + 1;
                    });
                    
                    teamsByCountryData.value = {
                        labels: Object.keys(countryCounts),
                        datasets: [{
                            data: Object.values(countryCounts),
                            backgroundColor: [
                                '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'
                            ]
                        }]
                    };
                }

                // Players by role
                const playersResponse = await api.getPlayers({ per_page: 100 });
                if (playersResponse.data.success) {
                    const players = playersResponse.data;
                    const roleCounts = {};
                    players.forEach(player => {
                        const role = player.role || 'Unknown';
                        roleCounts[role] = (roleCounts[role] || 0) + 1;
                    });
                    
                    playersByRoleData.value = {
                        labels: Object.keys(roleCounts),
                        datasets: [{
                            label: 'Players',
                            data: Object.values(roleCounts),
                            backgroundColor: '#10B981'
                        }]
                    };
                }

                // Matches by status
                const matchesResponse = await api.getMatches({ per_page: 100 });
                if (matchesResponse.data.success) {
                    const matches = matchesResponse.data;
                    const statusCounts = {};
                    matches.forEach(match => {
                        const status = match.status || 'Unknown';
                        statusCounts[status] = (statusCounts[status] || 0) + 1;
                    });
                    
                    matchesByStatusData.value = {
                        labels: Object.keys(statusCounts),
                        datasets: [{
                            data: Object.values(statusCounts),
                            backgroundColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']
                        }]
                    };
                }

                // Venue capacity
                const venuesResponse = await api.getVenues({ per_page: 100 });
                if (venuesResponse.data.success) {
                    const venues = venuesResponse.data;
                    const topVenues = venues
                        .filter(v => v.capacity)
                        .sort((a, b) => b.capacity - a.capacity)
                        .slice(0, 5);
                    
                    venueCapacityData.value = {
                        labels: topVenues.map(v => v.name),
                        datasets: [{
                            label: 'Capacity',
                            data: topVenues.map(v => v.capacity),
                            backgroundColor: '#F59E0B'
                        }]
                    };
                }

            } catch (err) {
                console.error('Error loading chart data:', err);
            }
        };

        const loadRecentActivity = () => {
            // Simulated recent activity data
            recentActivity.value = [
                { id: 1, title: 'New Team Added', description: 'Mumbai Indians joined the league', color: 'bg-green-500', time: '2 hours ago' },
                { id: 2, title: 'Match Completed', description: 'CSK vs RCB - CSK won by 5 wickets', color: 'bg-blue-500', time: '4 hours ago' },
                { id: 3, title: 'Player Transferred', description: 'John Doe moved to Team A', color: 'bg-purple-500', time: '6 hours ago' },
                { id: 4, title: 'Venue Updated', description: 'Eden Gardens capacity increased', color: 'bg-orange-500', time: '1 day ago' }
            ];
        };

        return {
            loading,
            stats,
            recentActivity,
            viewMode,
            chartOptions,
            horizontalBarOptions,
            teamsByCountryData,
            playersByRoleData,
            matchesByStatusData,
            venueCapacityData
        };
    }
}
</script>
