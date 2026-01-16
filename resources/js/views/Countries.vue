<template>
    <div class="countries">
        <!-- Header with View Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Countries Management</h1>
                    <p class="text-gray-600 mt-2">Manage cricket participating countries</p>
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

        <!-- Card View -->
        <div v-if="viewMode === 'cards'">
            <!-- Add Country Button -->
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Country
                    </span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="countryStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-gray-600">Loading countries...</p>
            </div>

            <!-- Countries Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="country in countryStore.countries" :key="country.country_id" 
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">{{ country.name }}</h2>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                {{ country.short_name }}
                            </span>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">{{ country.teams_count }} Teams</p>
                        <div class="flex space-x-2">
                            <button @click="viewTeams(country.country_id)" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                View Teams
                            </button>
                            <button @click="openEditModal(country)" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(country)" 
                                    class="bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded text-sm transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!countryStore.loading && countryStore.countries.length === 0" class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No countries found.</p>
                <button @click="openCreateModal" class="text-blue-600 hover:underline mt-2 inline-block">
                    Add your first country
                </button>
            </div>
        </div>

        <!-- Table View -->
        <div v-else class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-800">Countries List</h2>
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Country
                    </span>
                </button>
            </div>
            
            <div v-if="countryStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <p class="text-gray-600">Loading countries...</p>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teams</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="country in countryStore.countries" :key="country.country_id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ country.country_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ country.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                    {{ country.short_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    {{ country.teams_count }} teams
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button @click="openEditModal(country)" 
                                            class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete(country)" 
                                            class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal v-model="showModal" :title="isEditing ? 'Edit Country' : 'Add Country'">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Country Name</label>
                    <input v-model="formData.name" 
                           type="text" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter country name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Short Name</label>
                    <input v-model="formData.short_name" 
                           type="text" 
                           required
                           maxlength="3"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., IND">
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
                        :disabled="countryStore.loading"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCountryStore } from '../stores/useCountryStore';
import { useToast } from '../composables/useToast';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Countries',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const router = useRouter();
        const countryStore = useCountryStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const isEditing = ref(false);
        const viewMode = ref('cards');
        const formData = ref({
            name: '',
            short_name: ''
        });

        const columns = [
            { key: 'country_id', label: 'ID' },
            { key: 'name', label: 'Country Name' },
            { key: 'short_name', label: 'Short Code' },
            { key: 'teams_count', label: 'Teams' }
        ];

        onMounted(() => {
            countryStore.fetchCountries();
        });

        const openCreateModal = () => {
            isEditing.value = false;
            formData.value = { name: '', short_name: '' };
            showModal.value = true;
        };

        const openEditModal = (country) => {
            isEditing.value = true;
            formData.value = { ...country };
            showModal.value = true;
        };

        const handleSubmit = async () => {
            try {
                if (isEditing.value) {
                    await countryStore.updateCountry(formData.value.country_id, formData.value);
                } else {
                    await countryStore.createCountry(formData.value);
                }
                showModal.value = false;
            } catch (error) {
                console.error('Error saving country:', error);
            }
        };

        const confirmDelete = async (country) => {
            if (confirm(`Are you sure you want to delete ${country.name}?`)) {
                try {
                    await countryStore.deleteCountry(country.country_id);
                } catch (error) {
                    console.error('Error deleting country:', error);
                }
            }
        };

        const handlePageChange = (page) => {
            countryStore.fetchCountries({ page });
        };

        const viewTeams = (countryId) => {
            // Navigate to teams page with country filter
            console.log('🔍 DEBUG: Navigating to teams with country_id:', countryId);
            router.push({ path: '/teams', query: { country_id: countryId } });
        };

        return {
            countryStore,
            columns,
            showModal,
            isEditing,
            viewMode,
            formData,
            openCreateModal,
            openEditModal,
            handleSubmit,
            confirmDelete,
            handlePageChange,
            viewTeams
        };
    }
}
</script>
