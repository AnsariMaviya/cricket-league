<template>
    <div class="search">
        <!-- Search Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Advanced Search</h1>
            
            <!-- Search Form -->
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input 
                            v-model="searchQuery"
                            @input="handleSearch"
                            type="text" 
                            placeholder="Search countries, teams, players, venues, matches..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        >
                    </div>
                    <select v-model="searchType" @change="handleSearch" 
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="all">All Types</option>
                        <option value="countries">Countries</option>
                        <option value="teams">Teams</option>
                        <option value="players">Players</option>
                        <option value="venues">Venues</option>
                        <option value="matches">Matches</option>
                    </select>
                    <button @click="handleSearch" 
                            class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>

                <!-- Advanced Filters -->
                <div class="flex flex-wrap gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select v-model="filters.country" @change="handleSearch" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Countries</option>
                            <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                                {{ country.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                        <select v-model="filters.team" @change="handleSearch" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Teams</option>
                            <option v-for="team in teams" :key="team.team_id" :value="team.team_id">
                                {{ team.team_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select v-model="filters.role" @change="handleSearch" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Roles</option>
                            <option value="Batsman">Batsman</option>
                            <option value="Bowler">Bowler</option>
                            <option value="All-rounder">All-rounder</option>
                            <option value="Wicket-keeper">Wicket-keeper</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="filters.status" @change="handleSearch" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="live">Live</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <button @click="clearFilters" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600 mb-4"></div>
                <p class="text-gray-600">Searching...</p>
            </div>

            <div v-else-if="searchQuery && results.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-gray-600">No results found for "{{ searchQuery }}"</p>
                <p class="text-gray-500 text-sm mt-2">Try adjusting your search terms or filters</p>
            </div>

            <div v-else-if="!searchQuery" class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-gray-600">Enter a search term to get started</p>
                <p class="text-gray-500 text-sm mt-2">Search across countries, teams, players, venues, and matches</p>
            </div>

            <div v-else>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">
                        Found {{ results.length }} results for "{{ searchQuery }}"
                    </h2>
                    <div class="flex gap-2">
                        <button @click="groupByType = !groupByType" 
                                class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50">
                            {{ groupByType ? 'List View' : 'Group by Type' }}
                        </button>
                    </div>
                </div>

                <!-- Grouped Results -->
                <div v-if="groupByType" class="space-y-6">
                    <div v-for="(group, type) in groupedResults" :key="type">
                        <h3 class="text-md font-semibold text-gray-700 mb-3 capitalize">
                            {{ type }} ({{ group.length }})
                        </h3>
                        <div class="space-y-2">
                            <div v-for="result in group" :key="result.id" 
                                 class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer"
                                 @click="navigateToResult(result)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                             :class="getTypeColor(type)">
                                            <span class="text-white text-sm font-bold">{{ getTypeIcon(type) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ result.name }}</h4>
                                            <p class="text-sm text-gray-600">{{ result.description }}</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Results -->
                <div v-else class="space-y-2">
                    <div v-for="result in results" :key="result.id" 
                         class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow cursor-pointer"
                         @click="navigateToResult(result)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                     :class="getTypeColor(result.type)">
                                    <span class="text-white text-sm font-bold">{{ getTypeIcon(result.type) }}</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ result.name }}</h4>
                                    <p class="text-sm text-gray-600">{{ result.description }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 capitalize">
                                    {{ result.type }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useCountryStore } from '../stores/useCountryStore';
import { useTeamStore } from '../stores/useTeamStore';
import { usePlayerStore } from '../stores/usePlayerStore';
import { useVenueStore } from '../stores/useVenueStore';
import { useMatchStore } from '../stores/useMatchStore';
import { useToast } from '../composables/useToast';
import api from '../services/api';

export default {
    name: 'Search',
    setup() {
        const router = useRouter();
        const { success, error } = useToast();
        
        const countryStore = useCountryStore();
        const teamStore = useTeamStore();
        const playerStore = usePlayerStore();
        const venueStore = useVenueStore();
        const matchStore = useMatchStore();

        const searchQuery = ref('');
        const searchType = ref('all');
        const groupByType = ref(true);
        const loading = ref(false);
        const results = ref([]);
        const countries = ref([]);
        const teams = ref([]);
        
        const filters = ref({
            country: '',
            team: '',
            role: '',
            status: ''
        });

        const groupedResults = computed(() => {
            const groups = {};
            results.value.forEach(result => {
                if (!groups[result.type]) {
                    groups[result.type] = [];
                }
                groups[result.type].push(result);
            });
            return groups;
        });

        onMounted(async () => {
            await loadReferenceData();
        });

        const loadReferenceData = async () => {
            await Promise.all([
                countryStore.fetchCountries({ per_page: 100 }),
                teamStore.fetchTeams({ per_page: 100 })
            ]);
            countries.value = countryStore.countries;
            teams.value = teamStore.teams;
        };

        const handleSearch = async () => {
            if (!searchQuery.value.trim()) {
                results.value = [];
                return;
            }

            loading.value = true;
            try {
                const searchParams = {
                    q: searchQuery.value,
                    type: searchType.value,
                    per_page: 20,
                    ...filters.value
                };

                const response = await api.search(searchQuery.value, searchType.value, 20);
                if (response.data.success) {
                    results.value = response.data.data.map(item => ({
                        id: item.id || item[`${item.type}_id`],
                        name: item.name || item.team_name || item.venue_name,
                        type: item.type || 'unknown',
                        description: getDescription(item),
                        data: item
                    }));
                }
            } catch (err) {
                error('Search failed');
                console.error(err);
            } finally {
                loading.value = false;
            }
        };

        const getDescription = (item) => {
            if (item.type === 'country') {
                return `${item.short_name} • ${item.teams_count || 0} teams`;
            } else if (item.type === 'team') {
                return `${item.country?.name || 'Unknown'} • ${item.in_match || 'No league'}`;
            } else if (item.type === 'player') {
                return `${item.team?.team_name || 'No team'} • ${item.role}`;
            } else if (item.type === 'venue') {
                return `${item.city}, ${item.country} • ${item.capacity?.toLocaleString() || 0} seats`;
            } else if (item.type === 'match') {
                return `${item.firstTeam?.team_name} vs ${item.secondTeam?.team_name} • ${item.status}`;
            }
            return '';
        };

        const getTypeColor = (type) => {
            const colors = {
                country: 'bg-blue-500',
                team: 'bg-green-500',
                player: 'bg-purple-500',
                venue: 'bg-orange-500',
                match: 'bg-red-500'
            };
            return colors[type] || 'bg-gray-500';
        };

        const getTypeIcon = (type) => {
            const icons = {
                country: '🌍',
                team: '👥',
                player: '🏏',
                venue: '🏟️',
                match: '⚡'
            };
            return icons[type] || '?';
        };

        const navigateToResult = (result) => {
            const routes = {
                country: `/countries`,
                team: `/teams`,
                player: `/players`,
                venue: `/venues`,
                match: `/matches`
            };
            
            const route = routes[result.type];
            if (route) {
                router.push(route);
            }
        };

        const clearFilters = () => {
            filters.value = {
                country: '',
                team: '',
                role: '',
                status: ''
            };
            handleSearch();
        };

        // Watch for search query changes with debouncing
        let searchTimeout;
        watch(searchQuery, () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                handleSearch();
            }, 500);
        });

        return {
            searchQuery,
            searchType,
            groupByType,
            loading,
            results,
            groupedResults,
            countries,
            teams,
            filters,
            handleSearch,
            navigateToResult,
            clearFilters,
            getTypeColor,
            getTypeIcon
        };
    }
}
</script>
