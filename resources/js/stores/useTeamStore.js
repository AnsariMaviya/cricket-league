import { defineStore } from 'pinia';
import api from '../services/api';

export const useTeamStore = defineStore('team', {
    state: () => ({
        teams: [],
        currentTeam: null,
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
        async fetchTeams(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.getTeams(params);
                if (response.data.success) {
                    this.teams = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch teams';
                console.error('Error fetching teams:', error);
            } finally {
                this.loading = false;
            }
        },

        async createTeam(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post('/teams', data);
                await this.fetchTeams();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create team';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateTeam(id, data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.put(`/teams/${id}`, data);
                await this.fetchTeams();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update team';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteTeam(id) {
            this.loading = true;
            this.error = null;
            try {
                await window.axios.delete(`/teams/${id}`);
                await this.fetchTeams();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete team';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
