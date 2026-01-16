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
        return api.get('/stats');
    },

    // Countries
    getCountries(params = {}) {
        return api.get('/countries', { params });
    },

    // Teams
    getTeams(params = {}) {
        return api.get('/teams', { params });
    },

    getTeamDetails(id) {
        return api.get(`/teams/${id}`);
    },

    createTeam(data) {
        return api.post('/teams', data);
    },

    updateTeam(id, data) {
        return api.put(`/teams/${id}`, data);
    },

    deleteTeam(id) {
        return api.delete(`/teams/${id}`);
    },

    // Players
    getPlayers(params = {}) {
        return api.get('/players', { params });
    },

    getPlayerDetails(id) {
        return api.get(`/players/${id}`);
    },

    createPlayer(data) {
        return api.post('/players', data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    updatePlayer(id, data) {
        return api.post(`/players/${id}`, data, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    deletePlayer(id) {
        return api.delete(`/players/${id}`);
    },

    // Venues
    getVenues(params = {}) {
        return api.get('/venues', { params });
    },

    createVenue(data) {
        return api.post('/venues', data);
    },

    updateVenue(id, data) {
        return api.put(`/venues/${id}`, data);
    },

    deleteVenue(id) {
        return api.delete(`/venues/${id}`);
    },

    // Matches
    getMatches(params = {}) {
        return api.get('/matches', { params });
    },

    getMatchDetails(id) {
        return api.get(`/matches/${id}`);
    },

    createMatch(data) {
        return api.post('/matches', data);
    },

    updateMatch(id, data) {
        return api.put(`/matches/${id}`, data);
    },

    deleteMatch(id) {
        return api.delete(`/matches/${id}`);
    },

    // Search
    search(query, type = 'all', perPage = 10) {
        return api.get('/search', { 
            params: { q: query, type, per_page: perPage } 
        });
    }
};
