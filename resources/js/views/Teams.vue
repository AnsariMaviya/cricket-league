<template>
    <div class="teams">
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
        const teamStore = useTeamStore();
        const countryStore = useCountryStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const isEditing = ref(false);
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

        return {
            teamStore,
            columns,
            showModal,
            isEditing,
            formData,
            countries,
            openCreateModal,
            openEditModal,
            handleSubmit,
            confirmDelete,
            handlePageChange
        };
    }
}
</script>
