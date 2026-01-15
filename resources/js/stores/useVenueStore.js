import { defineStore } from 'pinia';
import api from '../services/api';

export const useVenueStore = defineStore('venue', {
    state: () => ({
        venues: [],
        currentVenue: null,
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
        async fetchVenues(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.getVenues(params);
                if (response.data.success) {
                    this.venues = response.data.data.data;
                    this.pagination = {
                        current_page: response.data.data.current_page,
                        last_page: response.data.data.last_page,
                        per_page: response.data.data.per_page,
                        total: response.data.data.total
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch venues';
                console.error('Error fetching venues:', error);
            } finally {
                this.loading = false;
            }
        },

        async createVenue(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post('/venues', data);
                await this.fetchVenues();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create venue';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateVenue(id, data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.put(`/venues/${id}`, data);
                await this.fetchVenues();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update venue';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteVenue(id) {
            this.loading = true;
            this.error = null;
            try {
                await window.axios.delete(`/venues/${id}`);
                await this.fetchVenues();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete venue';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
