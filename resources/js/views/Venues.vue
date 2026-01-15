<template>
    <div class="venues">
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
        const venueStore = useVenueStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const isEditing = ref(false);
        const formData = ref({
            name: '',
            city: '',
            country: '',
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

        return {
            venueStore,
            columns,
            showModal,
            isEditing,
            formData,
            openCreateModal,
            openEditModal,
            handleSubmit,
            confirmDelete,
            handlePageChange
        };
    }
}
</script>
