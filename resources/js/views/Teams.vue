<template>
    <div class="teams">
        <!-- Header with View Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Teams Management</h1>
                    <p class="text-gray-600 mt-2">Manage cricket teams and their players</p>
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
            <!-- Add Team Button -->
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Team
                    </span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="teamStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
                <p class="text-gray-600">Loading teams...</p>
            </div>

            <!-- Teams Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="team in teamStore.teams" :key="team.team_id" 
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4">
                        <h2 class="text-xl font-semibold">{{ team.team_name }}</h2>
                        <p class="text-green-100 text-sm">{{ team.country?.name ?? 'N/A' }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-gray-600 text-sm mb-4">{{ team.players_count || 0 }} Players</p>
                        <p v-if="team.in_match" class="text-gray-500 text-xs mb-4">{{ team.in_match }}</p>
                        <div class="flex space-x-2">
                            <button @click="viewPlayers(team.team_id)" 
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                Players
                            </button>
                            <button @click="openEditModal(team)" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(team)" 
                                    class="bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded text-sm transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!teamStore.loading && teamStore.teams.length === 0" class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No teams found.</p>
                <button @click="openCreateModal" class="text-blue-600 hover:underline mt-2 inline-block">
                    Add your first team
                </button>
            </div>
        </div>

        <!-- Pagination for Card View -->
        <div v-if="viewMode === 'card' && teamStore.teams.length > 0" class="mt-6 flex justify-center">
            <nav class="flex items-center gap-2">
                <button 
                    @click="handlePageChange(teamStore.pagination.current_page - 1)"
                    :disabled="teamStore.pagination.current_page === 1"
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                
                <template v-for="page in getPageNumbers()" :key="page">
                    <button 
                        v-if="page !== '...'"
                        @click="handlePageChange(page)"
                        :class="[
                            'px-3 py-2 border rounded-md',
                            page === teamStore.pagination.current_page 
                                ? 'bg-green-600 text-white border-green-600' 
                                : 'border-gray-300 hover:bg-gray-50'
                        ]">
                        {{ page }}
                    </button>
                    <span v-else class="px-2">...</span>
                </template>
                
                <button 
                    @click="handlePageChange(teamStore.pagination.current_page + 1)"
                    :disabled="teamStore.pagination.current_page === teamStore.pagination.last_page"
                    class="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </nav>
        </div>

        <!-- Table View -->
        <div v-else>
            <DataTable
                title="Teams"
                :columns="columns"
                :data="teamStore.teams"
                :loading="teamStore.loading"
                :error="teamStore.error"
                :pagination="teamStore.pagination"
                item-key="team_id"
                empty-message="No teams found"
                @page-change="handlePageChange"
            >
                <template #header-actions>
                    <button @click="openCreateModal" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Team
                        </span>
                    </button>
                </template>

            <template #cell-country="{ item }">
                <span class="text-gray-900">{{ item.country?.name }}</span>
            </template>

            <template #cell-players_count="{ value }">
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                    {{ value || 0 }} players
                </span>
            </template>

            <template #actions="{ item }">
                <div class="flex gap-2 justify-end">
                    <button @click="openEditModal(item)" 
                            class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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

        <Modal v-model="showModal" :title="isEditing ? 'Edit Team' : 'Add Team'">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Team Name</label>
                    <input v-model="formData.team_name" 
                           type="text" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Enter team name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                    <select v-model="formData.country_id" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Country</option>
                        <option v-for="country in countries" :key="country.country_id" :value="country.country_id">
                            {{ country.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">League/Tournament</label>
                    <input v-model="formData.in_match" 
                           type="text"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="e.g., IPL, BBL">
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
                        :disabled="teamStore.loading"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTeamStore } from '../stores/useTeamStore';
import { useCountryStore } from '../stores/useCountryStore';
import { useToast } from '../composables/useToast';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Teams',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const router = useRouter();
        const teamStore = useTeamStore();
        const countryStore = useCountryStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const isEditing = ref(false);
        const viewMode = ref('card');
        const countries = ref([]);
        const formData = ref({
            team_name: '',
            country_id: '',
            in_match: ''
        });

        const columns = [
            { key: 'team_id', label: 'ID' },
            { key: 'team_name', label: 'Team Name' },
            { key: 'country', label: 'Country' },
            { key: 'in_match', label: 'League' },
            { key: 'players_count', label: 'Players' }
        ];

        onMounted(async () => {
            await teamStore.fetchTeams();
            const response = await countryStore.fetchCountries();
            countries.value = countryStore.countries;
        });

        const openCreateModal = () => {
            isEditing.value = false;
            formData.value = { team_name: '', country_id: '', in_match: '' };
            showModal.value = true;
        };

        const openEditModal = (team) => {
            isEditing.value = true;
            formData.value = { ...team };
            showModal.value = true;
        };

        const handleSubmit = async () => {
            try {
                if (isEditing.value) {
                    await teamStore.updateTeam(formData.value.team_id, formData.value);
                    success('Team updated successfully');
                } else {
                    await teamStore.createTeam(formData.value);
                    success('Team created successfully');
                }
                showModal.value = false;
            } catch (err) {
                error(err.response?.data?.message || 'Operation failed');
            }
        };

        const confirmDelete = async (team) => {
            if (confirm(`Are you sure you want to delete ${team.team_name}?`)) {
                try {
                    await teamStore.deleteTeam(team.team_id);
                    success('Team deleted successfully');
                } catch (err) {
                    error(err.response?.data?.message || 'Delete failed');
                }
            }
        };

        const handlePageChange = (page) => {
            teamStore.fetchTeams({ page });
        };

        const getPageNumbers = () => {
            const pages = [];
            const current = teamStore.pagination.current_page;
            const last = teamStore.pagination.last_page;
            
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

        const viewPlayers = (teamId) => {
            console.log('🔍 DEBUG: Navigating to players with team_id:', teamId);
            router.push({ path: '/players', query: { team_id: teamId } });
        };

        return {
            teamStore,
            columns,
            showModal,
            isEditing,
            viewMode,
            formData,
            countries,
            openCreateModal,
            openEditModal,
            handleSubmit,
            confirmDelete,
            handlePageChange,
            viewPlayers,
            getPageNumbers
        };
    }
}
</script>
