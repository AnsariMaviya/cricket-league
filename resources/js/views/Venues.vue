<template>
    <div class="venues">
        <!-- Header with View Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Venues Management</h1>
                    <p class="text-gray-600 mt-2">Manage cricket stadiums and venues</p>
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
            <!-- Add Venue Button -->
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Venue
                    </span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="venueStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mb-4"></div>
                <p class="text-gray-600">Loading venues...</p>
            </div>

            <!-- Venues Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="venue in venueStore.venues" :key="venue.venue_id" 
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="bg-gradient-to-r from-orange-600 to-orange-700 text-white p-4">
                        <h2 class="text-xl font-semibold">{{ venue.name }}</h2>
                        <p class="text-orange-100 text-sm">{{ venue.city }}</p>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 mb-4">
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Capacity:</span> 
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium ml-1">
                                    {{ venue.capacity?.toLocaleString() || 0 }} seats
                                </span>
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Matches:</span> 
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium ml-1">
                                    {{ venue.matches_count || 0 }} matches
                                </span>
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Address:</span> {{ venue.address || 'N/A' }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="viewMatches(venue.venue_id)" 
                                    class="flex-1 bg-orange-600 hover:bg-orange-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                Matches
                            </button>
                            <button @click="openEditModal(venue)" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(venue)" 
                                    class="bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded text-sm transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!venueStore.loading && venueStore.venues.length === 0" class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No venues found.</p>
                <button @click="openCreateModal" class="text-blue-600 hover:underline mt-2 inline-block">
                    Add your first venue
                </button>
            </div>
        </div>

        <!-- Table View -->
        <div v-else>
            <DataTable
                title="Venues"
                :columns="columns"
                :data="venueStore.venues"
                :loading="venueStore.loading"
                :error="venueStore.error"
                :pagination="venueStore.pagination"
                item-key="venue_id"
                empty-message="No venues found"
                @page-change="handlePageChange"
            >
                <template #header-actions>
                    <button @click="openCreateModal" 
                            class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Venue
                        </span>
                    </button>
                </template>

            <template #cell-capacity="{ value }">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                    {{ value?.toLocaleString() || 0 }} seats
                </span>
            </template>

            <template #cell-matches_count="{ value }">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                    {{ value || 0 }} matches
                </span>
            </template>

            <template #cell-location="{ item }">
                <div class="text-sm">
                    <p class="text-gray-900">{{ item.city }}</p>
                    <p class="text-gray-500 text-xs">{{ item.address }}</p>
                </div>
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

        <!-- Create/Edit Modal -->
        <Modal v-model="showModal" :title="isEditing ? 'Edit Venue' : 'Add Venue'">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venue Name</label>
                    <input v-model="formData.name" 
                           type="text" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                           placeholder="Enter venue name">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input v-model="formData.city" 
                               type="text" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="Enter city">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <input v-model="formData.country" 
                               type="text" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="Enter country">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea v-model="formData.address" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                              placeholder="Enter full address"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                        <input v-model.number="formData.capacity" 
                               type="number" 
                               required
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="Seating capacity">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Established Year</label>
                        <input v-model.number="formData.established_year" 
                               type="number" 
                               min="1800"
                               :max="new Date().getFullYear()"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="Year established">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea v-model="formData.description" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                              placeholder="Venue description"></textarea>
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
                        :disabled="venueStore.loading"
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700 disabled:opacity-50">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useVenueStore } from '../stores/useVenueStore';
import { useToast } from '../composables/useToast';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Venues',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const router = useRouter();
        const venueStore = useVenueStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const isEditing = ref(false);
        const viewMode = ref('cards');
        const formData = ref({
            name: '',
            city: '',
            address: '',
            capacity: null,
            established_year: null,
            description: ''
        });

        const columns = [
            { key: 'venue_id', label: 'ID' },
            { key: 'name', label: 'Venue Name' },
            { key: 'location', label: 'Location' },
            { key: 'capacity', label: 'Capacity' },
            { key: 'matches_count', label: 'Matches' },
            { key: 'established_year', label: 'Est. Year' }
        ];

        onMounted(() => {
            venueStore.fetchVenues();
        });

        const openCreateModal = () => {
            isEditing.value = false;
            formData.value = { 
                name: '', city: '', country: '', address: '', 
                capacity: null, established_year: null, description: '' 
            };
            showModal.value = true;
        };

        const openEditModal = (venue) => {
            isEditing.value = true;
            formData.value = { ...venue };
            showModal.value = true;
        };

        const handleSubmit = async () => {
            try {
                if (isEditing.value) {
                    await venueStore.updateVenue(formData.value.venue_id, formData.value);
                    success('Venue updated successfully');
                } else {
                    await venueStore.createVenue(formData.value);
                    success('Venue created successfully');
                }
                showModal.value = false;
            } catch (err) {
                error(err.response?.data?.message || 'Operation failed');
            }
        };

        const confirmDelete = async (venue) => {
            if (confirm(`Are you sure you want to delete ${venue.name}?`)) {
                try {
                    await venueStore.deleteVenue(venue.venue_id);
                    success('Venue deleted successfully');
                } catch (err) {
                    error(err.response?.data?.message || 'Delete failed');
                }
            }
        };

        const handlePageChange = (page) => {
            venueStore.fetchVenues({ page });
        };

        const viewMatches = (venueId) => {
            console.log('🔍 DEBUG: Navigating to matches with venue_id:', venueId);
            router.push({ path: '/matches', query: { venue_id: venueId } });
        };

        return {
            venueStore,
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
            viewMatches
        };
    }
}
</script>
