<template>
    <div class="matches">
        <!-- Header with View Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Matches Management</h1>
                    <p class="text-gray-600 mt-2">Manage cricket matches and schedules</p>
                </div>
                <div class="flex gap-2">
                    <button @click="viewMode = 'card'" 
                            :class="viewMode === 'card' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
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

        <!-- Card View -->
        <div v-if="viewMode === 'card'">
            <!-- Add Match Button -->
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Match
                    </span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="matchStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mb-4"></div>
                <p class="text-gray-600">Loading matches...</p>
            </div>

            <!-- Matches Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="match in matchStore.matches" :key="match.match_id" 
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h2 class="text-lg font-semibold">{{ match.firstTeam?.team_name }}</h2>
                            <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                                {{ match.first_team_score || 'Yet to bat' }}
                            </span>
                        </div>
                        <div class="text-center text-2xl font-bold mb-2">VS</div>
                        <div class="flex justify-between items-center">
                            <span class="px-2 py-1 bg-white bg-opacity-20 rounded-full text-xs font-medium">
                                {{ match.second_team_score || 'Yet to bat' }}
                            </span>
                            <h2 class="text-lg font-semibold">{{ match.secondTeam?.team_name }}</h2>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 mb-4">
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Venue:</span> {{ match.venue?.name || 'TBA' }}
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Date:</span> {{ formatDate(match.match_date) }}
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 rounded-full text-xs font-medium ml-1" :class="getStatusClass(match.status)">
                                    {{ match.status }}
                                </span>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button v-if="match.status === 'completed'" 
                                    @click="viewScorecard(match.match_id)" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                📊 Scorecard
                            </button>
                            <button v-if="match.status === 'scheduled' || match.status === 'live'" 
                                    @click="goToLiveMatch(match.match_id)" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                {{ match.status === 'live' ? '🔴 Watch Live' : '▶️ Simulate' }}
                            </button>
                            <button @click="openEditModal(match)" 
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(match)" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!matchStore.loading && matchStore.matches.length === 0" class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No matches found.</p>
                <button @click="openCreateModal" class="text-blue-600 hover:underline mt-2 inline-block">
                    Add your first match
                </button>
            </div>
        </div>

        <!-- Pagination for Card View -->
        <div v-if="viewMode === 'card' && matchStore.matches.length > 0" class="mt-6 flex justify-center">
            <nav class="flex items-center gap-2">
                <button 
                    @click="handlePageChange(matchStore.pagination.current_page - 1)"
                    :disabled="matchStore.pagination.current_page === 1"
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                
                <template v-for="page in getPageNumbers()" :key="page">
                    <button 
                        v-if="page !== '...'"
                        @click="handlePageChange(page)"
                        :class="[
                            'px-3 py-2 border rounded-md',
                            page === matchStore.pagination.current_page 
                                ? 'bg-red-600 text-white border-red-600' 
                                : 'border-gray-300 hover:bg-gray-50'
                        ]">
                        {{ page }}
                    </button>
                    <span v-else class="px-2">...</span>
                </template>
                
                <button 
                    @click="handlePageChange(matchStore.pagination.current_page + 1)"
                    :disabled="matchStore.pagination.current_page === matchStore.pagination.last_page"
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </nav>
        </div>

        <!-- Table View -->
        <div v-else>
            <DataTable
                title="Matches"
                :columns="columns"
                :data="matchStore.matches"
                :loading="matchStore.loading"
                :error="matchStore.error"
                :pagination="matchStore.pagination"
                item-key="match_id"
                empty-message="No matches found"
                @page-change="handlePageChange"
            >
                <template #header-actions>
                    <button @click="openCreateModal" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Match
                        </span>
                    </button>
                </template>

            <template #cell-teams="{ item }">
                <div class="text-sm">
                    <p class="font-medium">{{ item.firstTeam?.team_name }}</p>
                    <p class="text-gray-500">vs</p>
                    <p class="font-medium">{{ item.secondTeam?.team_name }}</p>
                </div>
            </template>

            <template #cell-venue="{ item }">
                <div class="text-sm">
                    <p class="text-gray-900">{{ item.venue?.name }}</p>
                    <p class="text-gray-500 text-xs">{{ formatDate(item.match_date) }}</p>
                </div>
            </template>

            <template #cell-score="{ item }">
                <div class="text-sm">
                    <p class="font-medium">{{ item.first_team_score || 'Yet to bat' }}</p>
                    <p class="text-gray-500">{{ item.second_team_score || 'Yet to bat' }}</p>
                </div>
            </template>

            <template #cell-status="{ value }">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(value)">
                    {{ value }}
                </span>
            </template>

            <template #actions="{ item }">
                <div class="flex gap-2 justify-end">
                    <button @click="openShowModal(item)" 
                            class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                    <button @click="openEditModal(item)" 
                            class="text-green-600 hover:text-green-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button v-if="item.status === 'scheduled'" @click="startLiveMatch(item)" 
                            class="text-orange-600 hover:text-orange-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                    <button @click="confirmDelete(item)" 
                            class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>
        </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Modal v-model="showModal" :title="isEditing ? 'Edit Match' : 'Add Match'" size="lg">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Team</label>
                        <select v-model="formData.first_team_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Team</option>
                            <option v-for="team in teams" :key="team.team_id" :value="team.team_id">
                                {{ team.team_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Second Team</label>
                        <select v-model="formData.second_team_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Team</option>
                            <option v-for="team in teams" :key="team.team_id" :value="team.team_id">
                                {{ team.team_name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                        <select v-model="formData.venue_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Venue</option>
                            <option v-for="venue in venues" :key="venue.venue_id" :value="venue.venue_id">
                                {{ venue.name }} ({{ venue.capacity }} seats)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Match Type</label>
                        <select v-model="formData.match_type" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Type</option>
                            <option value="T20">T20</option>
                            <option value="ODI">ODI</option>
                            <option value="Test">Test</option>
                            <option value="The Hundred">The Hundred</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Match Date</label>
                        <input v-model="formData.match_date" 
                               type="datetime-local" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Overs</label>
                        <input v-model.number="formData.overs" 
                               type="number" 
                               required
                               min="1"
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                               placeholder="Number of overs">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="formData.description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Match description"></textarea>
                </div>
            </form>

            <template #footer>
                <button @click="showModal = false" 
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button @click="handleSubmit" 
                        type="submit"
                        :disabled="matchStore.loading"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
            </template>
        </Modal>

        <!-- Show Modal -->
        <Modal v-model="showShowModal" title="Match Details" size="lg">
            <div v-if="currentMatch" class="space-y-4">
                <!-- Match Header -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <div class="text-center flex-1">
                            <h3 class="text-lg font-bold">{{ currentMatch.firstTeam?.team_name }}</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ currentMatch.first_team_score || 'Yet to bat' }}</p>
                        </div>
                        <div class="text-center">
                            <span class="px-3 py-1 rounded-full text-sm font-medium" :class="getStatusClass(currentMatch.status)">
                                {{ currentMatch.status }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ formatDate(currentMatch.match_date) }}</p>
                        </div>
                        <div class="text-center flex-1">
                            <h3 class="text-lg font-bold">{{ currentMatch.secondTeam?.team_name }}</h3>
                            <p class="text-2xl font-bold text-green-600">{{ currentMatch.second_team_score || 'Yet to bat' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Match Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Venue</span>
                        <p class="font-medium">{{ currentMatch.venue?.name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Match Type</span>
                        <p class="font-medium">{{ currentMatch.match_type }} • {{ currentMatch.overs }} overs</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Toss</span>
                        <p class="font-medium">{{ currentMatch.toss_winner || 'Not decided' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Result</span>
                        <p class="font-medium">{{ currentMatch.outcome || 'Match in progress' }}</p>
                    </div>
                </div>

                <!-- Live Score Update (if live) -->
                <div v-if="currentMatch.status === 'live'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-bold text-yellow-800 mb-2">Live Score Update</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Team Score</label>
                            <input v-model="liveScore.first_team_score" 
                                   type="text" 
                                   placeholder="e.g., 150/5 (15.2 overs)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Second Team Score</label>
                            <input v-model="liveScore.second_team_score" 
                                   type="text" 
                                   placeholder="e.g., 120/3 (12.5 overs)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Match Status</label>
                        <select v-model="liveScore.status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <option value="live">Live</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Result</label>
                        <input v-model="liveScore.outcome" 
                               type="text" 
                               placeholder="e.g., Team A won by 5 wickets"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button @click="updateLiveScore" 
                                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Update Score
                        </button>
                        <button @click="endMatch" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            End Match
                        </button>
                    </div>
                </div>
            </div>

            <template #footer>
                <button @click="showShowModal = false" 
                        type="button"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Close
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useMatchStore } from '../stores/useMatchStore';
import { useTeamStore } from '../stores/useTeamStore';
import { useVenueStore } from '../stores/useVenueStore';
import { useToast } from '../composables/useToast';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Matches',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const route = useRoute();
        const matchStore = useMatchStore();
        const teamStore = useTeamStore();
        const venueStore = useVenueStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const showShowModal = ref(false);
        const isEditing = ref(false);
        const viewMode = ref('card');
        const teams = ref([]);
        const venues = ref([]);
        const currentMatch = ref(null);
        const liveScore = ref({
            first_team_score: '',
            second_team_score: '',
            status: 'live',
            outcome: ''
        });
        const formData = ref({
            first_team_id: '',
            second_team_id: '',
            venue_id: '',
            match_type: '',
            match_date: '',
            overs: 20,
            description: ''
        });

        const columns = [
            { key: 'match_id', label: 'ID' },
            { key: 'teams', label: 'Teams' },
            { key: 'venue', label: 'Venue & Date' },
            { key: 'score', label: 'Score' },
            { key: 'status', label: 'Status' },
            { key: 'match_type', label: 'Type' }
        ];

        onMounted(async () => {
            // Check for venue_id filter in query params
            const venueId = route.query.venue_id;
            const params = venueId ? { venue_id: venueId } : {};
            
            await matchStore.fetchMatches(params);
            await teamStore.fetchTeams({ per_page: 100 });
            await venueStore.fetchVenues({ per_page: 100 });
            teams.value = teamStore.teams;
            venues.value = venueStore.venues;
        });

        const openCreateModal = () => {
            isEditing.value = false;
            formData.value = { 
                first_team_id: '', second_team_id: '', venue_id: '', 
                match_type: '', match_date: '', overs: 20, description: '' 
            };
            showModal.value = true;
        };

        const openEditModal = (match) => {
            isEditing.value = true;
            formData.value = { ...match };
            showModal.value = true;
        };

        const openShowModal = (match) => {
            currentMatch.value = match;
            liveScore.value = {
                first_team_score: match.first_team_score || '',
                second_team_score: match.second_team_score || '',
                status: match.status || 'live',
                outcome: match.outcome || ''
            };
            showShowModal.value = true;
        };

        const startLiveMatch = async (match) => {
            try {
                // Call the proper start endpoint to initialize the match
                const response = await window.axios.post(`/api/v1/live-matches/${match.match_id}/start`);
                if (response.data.success) {
                    success('Match started successfully!');
                    await matchStore.fetchMatches();
                    // Redirect to live match page
                    window.location.href = `/live-matches/${match.match_id}`;
                }
            } catch (err) {
                error(err.response?.data?.message || 'Failed to start match');
            }
        };

        const updateLiveScore = async () => {
            try {
                await matchStore.updateMatch(currentMatch.value.match_id, liveScore.value);
                success('Score updated successfully!');
                await matchStore.fetchMatches();
            } catch (err) {
                error(err.response?.data?.message || 'Failed to update score');
            }
        };

        const endMatch = async () => {
            try {
                await matchStore.updateMatch(currentMatch.value.match_id, { 
                    status: 'completed',
                    ...liveScore.value
                });
                success('Match ended successfully!');
                showShowModal.value = false;
                await matchStore.fetchMatches();
            } catch (err) {
                error(err.response?.data?.message || 'Failed to end match');
            }
        };

        const handleSubmit = async () => {
            try {
                if (isEditing.value) {
                    await matchStore.updateMatch(formData.value.match_id, formData.value);
                    success('Match updated successfully');
                } else {
                    await matchStore.createMatch(formData.value);
                    success('Match created successfully');
                }
                showModal.value = false;
            } catch (err) {
                error(err.response?.data?.message || 'Operation failed');
            }
        };

        const confirmDelete = async (match) => {
            const matchName = `${match.firstTeam?.team_name} vs ${match.secondTeam?.team_name}`;
            if (confirm(`Are you sure you want to delete ${matchName}?`)) {
                try {
                    await matchStore.deleteMatch(match.match_id);
                    success('Match deleted successfully');
                } catch (err) {
                    error(err.response?.data?.message || 'Delete failed');
                }
            }
        };

        const handlePageChange = (page) => {
            matchStore.fetchMatches({ page });
        };

        const getPageNumbers = () => {
            const pages = [];
            const current = matchStore.pagination.current_page;
            const last = matchStore.pagination.last_page;
            
            if (last <= 7) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 3) {
                    for (let i = 1; i <= 5; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                } else if (current >= last - 2) {
                    pages.push(1);
                    pages.push('...');
                    for (let i = last - 4; i <= last; i++) pages.push(i);
                } else {
                    pages.push(1);
                    pages.push('...');
                    for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                    pages.push('...');
                    pages.push(last);
                }
            }
            return pages;
        };

        const getStatusClass = (status) => {
            const classes = {
                'completed': 'bg-green-100 text-green-800',
                'live': 'bg-red-100 text-red-800',
                'scheduled': 'bg-blue-100 text-blue-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        };

        const goToLiveMatch = (matchId) => {
            window.location.href = `/live-matches/${matchId}`;
        };

        const viewScorecard = (matchId) => {
            window.location.href = `/live-matches/${matchId}`;
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'TBD';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        return {
            matchStore,
            columns,
            showModal,
            showShowModal,
            isEditing,
            viewMode,
            formData,
            teams,
            venues,
            currentMatch,
            liveScore,
            openCreateModal,
            openEditModal,
            openShowModal,
            startLiveMatch,
            updateLiveScore,
            endMatch,
            handleSubmit,
            confirmDelete,
            handlePageChange,
            goToLiveMatch,
            viewScorecard,
            formatDate,
            getStatusClass,
            getPageNumbers
        };
    }
}
</script>
