<template>
    <div class="countries">
        <DataTable
            title="Countries"
            :columns="columns"
            :data="countryStore.countries"
            :loading="countryStore.loading"
            :error="countryStore.error"
            :pagination="countryStore.pagination"
            item-key="country_id"
            empty-message="No countries found"
            @page-change="handlePageChange"
        >
            <template #header-actions>
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Country
                    </span>
                </button>
            </template>

            <template #cell-teams_count="{ value }">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                    {{ value }} teams
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
import { useCountryStore } from '../stores/useCountryStore';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Countries',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const countryStore = useCountryStore();
        const showModal = ref(false);
        const isEditing = ref(false);
        const formData = ref({
            name: '',
            short_name: ''
        });

        const columns = [
            { key: 'country_id', label: 'ID' },
            { key: 'name', label: 'Country Name' },
            { key: 'short_name', label: 'Short Name' },
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

        return {
            countryStore,
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
