import { defineStore } from 'pinia';
import api from '../services/api';

export const useMatchStore = defineStore('match', {
    state: () => ({
        matches: [],
        currentMatch: null,
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
        async fetchMatches(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.getMatches(params);
                if (response.data.success) {
                    this.matches = response.data.data;
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        per_page: response.data.per_page,
                        total: response.data.total
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch matches';
                console.error('Error fetching matches:', error);
            } finally {
                this.loading = false;
            }
        },

        async createMatch(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post('/api/v1/matches', data);
                await this.fetchMatches();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create match';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateMatch(id, data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.put(`/api/v1/matches/${id}`, data);
                await this.fetchMatches();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update match';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteMatch(id) {
            this.loading = true;
            this.error = null;
            try {
                await window.axios.delete(`/api/v1/matches/${id}`);
                await this.fetchMatches();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete match';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
