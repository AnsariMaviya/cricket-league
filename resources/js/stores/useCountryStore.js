import { defineStore } from 'pinia';
import api from '../services/api';

export const useCountryStore = defineStore('country', {
    state: () => ({
        countries: [],
        currentCountry: null,
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
        async fetchCountries(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.getCountries(params);
                if (response.data.success) {
                    this.countries = response.data.data;
                    this.pagination = {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        per_page: response.data.per_page,
                        total: response.data.total
                    };
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch countries';
                console.error('Error fetching countries:', error);
            } finally {
                this.loading = false;
            }
        },

        async createCountry(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.post('/api/v1/countries', data);
                await this.fetchCountries();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create country';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateCountry(id, data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await window.axios.put(`/api/v1/countries/${id}`, data);
                await this.fetchCountries();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update country';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteCountry(id) {
            this.loading = true;
            this.error = null;
            try {
                await window.axios.delete(`/api/v1/countries/${id}`);
                await this.fetchCountries();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete country';
                throw error;
            } finally {
                this.loading = false;
            }
        }
    }
});
