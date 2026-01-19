import { defineStore } from 'pinia';
import api from '../services/api';

export const usePlayerStore = defineStore('player', {
    state: () => ({
        players: [],
        currentPlayer: null,
        loading: false,
        error: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: 0
        }
    }),

    actions: {
        async fetchPlayers(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.getPlayers(params);
                if (response.data.success) {
                    this.players = response.data.data;
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        per_page: response.data.per_page,
                        total: response.data.total
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch players';
                console.error('Error fetching players:', error);
            } finally {
                this.loading = false;
            }
        },

        async createPlayer(formData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post('/api/v1/players', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                await this.fetchPlayers();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create player';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updatePlayer(id, formData) {
            this.loading = true;
            this.error = null;
            try {
                formData.append('_method', 'PUT');
                const response = await window.axios.post(`/api/v1/players/${id}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                await this.fetchPlayers();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update player';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deletePlayer(id) {
            this.loading = true;
            this.error = null;
            try {
                await window.axios.delete(`/api/v1/players/${id}`);
                await this.fetchPlayers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete player';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
