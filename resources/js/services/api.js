import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    withCredentials: true
});

// Add CSRF token to all requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    api.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Response interceptor for error handling
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Handle unauthorized
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default {
    // Dashboard
    getStats() {
        return api.get('/api/v1/stats');
    },

    // Countries
    getCountries(params = {}) {
        return api.get('/api/v1/countries', { params });
    },

    // Teams
    getTeams(params = {}) {
        return api.get('/api/v1/teams', { params });
    },

    getTeamDetails(id) {
        return api.get(`/api/v1/teams/${id}`);
    },

    // Players
    getPlayers(params = {}) {
        return api.get('/api/v1/players', { params });
    },

    getPlayerDetails(id) {
        return api.get(`/api/v1/players/${id}`);
    },

    // Venues
    getVenues(params = {}) {
        return api.get('/api/v1/venues', { params });
    },

    // Matches
    getMatches(params = {}) {
        return api.get('/api/v1/matches', { params });
    },

    getMatchDetails(id) {
        return api.get(`/api/v1/matches/${id}`);
    },

    // Search
    search(query, type = 'all', perPage = 10) {
        return api.get('/api/v1/search', { 
            params: { q: query, type, per_page: perPage } 
        });
    }
};
