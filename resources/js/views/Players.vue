<template>
    <div class="players">
        <!-- Header with View Toggle -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Players Management</h1>
                    <p class="text-gray-600 mt-2">Manage cricket players and their profiles</p>
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
            <!-- Add Player Button -->
            <div class="flex justify-end mb-6">
                <button @click="openCreateModal" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Player
                    </span>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="playerStore.loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mb-4"></div>
                <p class="text-gray-600">Loading players...</p>
            </div>

            <!-- Players Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="player in playerStore.players" :key="player.player_id" 
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white p-4">
                        <h2 class="text-xl font-semibold">{{ player.first_name }} {{ player.last_name }}</h2>
                        <p class="text-purple-100 text-sm">{{ player.team?.team_name ?? 'No Team' }}</p>
                    </div>
                    <div class="p-4">
                        <div class="space-y-2 mb-4">
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Role:</span> 
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium ml-1">
                                    {{ player.role }}
                                </span>
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Age:</span> {{ calculateAge(player.dob) }} years
                            </p>
                            <p class="text-gray-600 text-sm">
                                <span class="font-medium">Country:</span> {{ player.country?.name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="openShowModal(player)" 
                                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white text-center py-2 px-3 rounded text-sm transition">
                                View
                            </button>
                            <button @click="openEditModal(player)" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-3 rounded text-sm transition">
                                Edit
                            </button>
                            <button @click="confirmDelete(player)" 
                                    class="bg-red-100 hover:bg-red-200 text-red-600 py-2 px-3 rounded text-sm transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!playerStore.loading && playerStore.players.length === 0" class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No players found.</p>
                <button @click="openCreateModal" class="text-blue-600 hover:underline mt-2 inline-block">
                    Add your first player
                </button>
            </div>
        </div>

        <!-- Table View -->
        <div v-else>
            <DataTable
                title="Players"
                :columns="columns"
                :data="playerStore.players"
                :loading="playerStore.loading"
                :error="playerStore.error"
                :pagination="playerStore.pagination"
                item-key="player_id"
                empty-message="No players found"
                @page-change="handlePageChange"
            >
                <template #header-actions>
                    <button @click="openCreateModal" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Player
                        </span>
                    </button>
                </template>

            <template #cell-team="{ item }">
                <span class="text-gray-900">{{ item.team?.team_name }}</span>
            </template>

            <template #cell-role="{ value }">
                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full text-xs font-medium">
                    {{ value }}
                </span>
            </template>

            <template #cell-age="{ item }">
                <span class="text-gray-600">{{ calculateAge(item.dob) }} years</span>
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
        <Modal v-model="showModal" :title="isEditing ? 'Edit Player' : 'Add Player'" size="lg">
            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Player Name</label>
                        <input v-model="formData.name" 
                               type="text" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Enter player name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                        <input v-model="formData.dob" 
                               type="date" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Team</label>
                        <select v-model="formData.team_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Team</option>
                            <option v-for="team in teams" :key="team.team_id" :value="team.team_id">
                                {{ team.team_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select v-model="formData.role" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Role</option>
                            <option value="Batsman">Batsman</option>
                            <option value="Bowler">Bowler</option>
                            <option value="All-rounder">All-rounder</option>
                            <option value="Wicket-keeper">Wicket-keeper</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Batting Style</label>
                        <select v-model="formData.batting_style" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Batting Style</option>
                            <option value="Right-handed">Right-handed</option>
                            <option value="Left-handed">Left-handed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bowling Style</label>
                        <select v-model="formData.bowling_style" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Bowling Style</option>
                            <option value="Right-arm fast">Right-arm fast</option>
                            <option value="Left-arm fast">Left-arm fast</option>
                            <option value="Right-arm spin">Right-arm spin</option>
                            <option value="Left-arm spin">Left-arm spin</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                    <input type="file" 
                           @change="handleFileUpload"
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <div v-if="imagePreview" class="mt-2">
                        <img :src="imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded">
                    </div>
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
                        :disabled="playerStore.loading"
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:opacity-50">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
            </template>
        </Modal>

        <!-- Show Modal -->
        <Modal v-model="showShowModal" title="Player Details" size="lg">
            <div v-if="currentPlayer" class="space-y-4">
                <div class="flex items-center space-x-4">
                    <img v-if="currentPlayer.profile_image" 
                         :src="getImageUrl(currentPlayer.profile_image)" 
                         :alt="currentPlayer.name"
                         class="h-24 w-24 object-cover rounded-full">
                    <div v-else class="h-24 w-24 bg-gray-200 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 0 3 3 0 000 6zm-7 9a7 7 0 1114 0H3a7 7 0 01-7-7z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">{{ currentPlayer.name }}</h3>
                        <p class="text-gray-600">{{ currentPlayer.team?.team_name }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-gray-500">Role</span>
                        <p class="font-medium">{{ currentPlayer.role }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Age</span>
                        <p class="font-medium">{{ calculateAge(currentPlayer.dob) }} years</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Batting Style</span>
                        <p class="font-medium">{{ currentPlayer.batting_style || 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Bowling Style</span>
                        <p class="font-medium">{{ currentPlayer.bowling_style || 'N/A' }}</p>
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
import { useRouter } from 'vue-router';
import { usePlayerStore } from '../stores/usePlayerStore';
import { useTeamStore } from '../stores/useTeamStore';
import { useToast } from '../composables/useToast';
import DataTable from '../components/ui/DataTable.vue';
import Modal from '../components/ui/Modal.vue';

export default {
    name: 'Players',
    components: {
        DataTable,
        Modal
    },
    setup() {
        const router = useRouter();
        const playerStore = usePlayerStore();
        const teamStore = useTeamStore();
        const { success, error } = useToast();
        const showModal = ref(false);
        const showShowModal = ref(false);
        const isEditing = ref(false);
        const viewMode = ref('cards');
        const teams = ref([]);
        const currentPlayer = ref(null);
        const imagePreview = ref(null);
        const formData = ref({
            name: '',
            team_id: '',
            dob: '',
            role: '',
            batting_style: '',
            bowling_style: '',
            profile_image: null
        });

        const columns = [
            { key: 'player_id', label: 'ID' },
            { key: 'name', label: 'Name' },
            { key: 'team', label: 'Team' },
            { key: 'role', label: 'Role' },
            { key: 'age', label: 'Age' },
            { key: 'batting_style', label: 'Batting' },
            { key: 'bowling_style', label: 'Bowling' }
        ];

        onMounted(async () => {
            await playerStore.fetchPlayers();
            await teamStore.fetchTeams();
            teams.value = teamStore.teams;
        });

        const openCreateModal = () => {
            isEditing.value = false;
            formData.value = { 
                name: '', team_id: '', dob: '', role: '', 
                batting_style: '', bowling_style: '', profile_image: null 
            };
            imagePreview.value = null;
            showModal.value = true;
        };

        const openEditModal = (player) => {
            isEditing.value = true;
            formData.value = { ...player, profile_image: null };
            imagePreview.value = player.profile_image ? getImageUrl(player.profile_image) : null;
            showModal.value = true;
        };

        const openShowModal = (player) => {
            currentPlayer.value = player;
            showShowModal.value = true;
        };

        const handleFileUpload = (event) => {
            const file = event.target.files[0];
            if (file) {
                formData.value.profile_image = file;
                imagePreview.value = URL.createObjectURL(file);
            }
        };

        const handleSubmit = async () => {
            try {
                const data = new FormData();
                Object.keys(formData.value).forEach(key => {
                    if (formData.value[key] !== null) {
                        data.append(key, formData.value[key]);
                    }
                });

                if (isEditing.value) {
                    await playerStore.updatePlayer(formData.value.player_id, data);
                    success('Player updated successfully');
                } else {
                    await playerStore.createPlayer(data);
                    success('Player created successfully');
                }
                showModal.value = false;
            } catch (err) {
                error(err.response?.data?.message || 'Operation failed');
            }
        };

        const confirmDelete = async (player) => {
            if (confirm(`Are you sure you want to delete ${player.name}?`)) {
                try {
                    await playerStore.deletePlayer(player.player_id);
                    success('Player deleted successfully');
                } catch (err) {
                    error(err.response?.data?.message || 'Delete failed');
                }
            }
        };

        const handlePageChange = (page) => {
            playerStore.fetchPlayers({ page });
        };

        const calculateAge = (dob) => {
            if (!dob) return 'N/A';
            const today = new Date();
            const birthDate = new Date(dob);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age;
        };

        const getImageUrl = (path) => {
            if (!path) return null;
            return path.startsWith('http') ? path : `/storage/${path}`;
        };

        return {
            playerStore,
            columns,
            showModal,
            showShowModal,
            isEditing,
            viewMode,
            formData,
            teams,
            currentPlayer,
            imagePreview,
            openCreateModal,
            openEditModal,
            openShowModal,
            handleFileUpload,
            handleSubmit,
            confirmDelete,
            handlePageChange,
            calculateAge,
            getImageUrl
        };
    }
}
</script>
