import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Countries from '../views/Countries.vue';
import Teams from '../views/Teams.vue';
import Players from '../views/Players.vue';
import Venues from '../views/Venues.vue';
import Matches from '../views/Matches.vue';
import Analytics from '../views/Analytics.vue';
import Search from '../views/Search.vue';
import LiveMatches from '../views/LiveMatches.vue';
import LiveMatchDetail from '../views/LiveMatchDetail.vue';
import Predictions from '../views/Predictions.vue';

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home,
        meta: { title: 'Dashboard' }
    },
    {
        path: '/countries',
        name: 'Countries',
        component: Countries,
        meta: { title: 'Countries' }
    },
    {
        path: '/teams',
        name: 'Teams',
        component: Teams,
        meta: { title: 'Teams' }
    },
    {
        path: '/players',
        name: 'Players',
        component: Players,
        meta: { title: 'Players' }
    },
    {
        path: '/venues',
        name: 'Venues',
        component: Venues,
        meta: { title: 'Venues' }
    },
    {
        path: '/matches',
        name: 'Matches',
        component: Matches,
        meta: { title: 'Matches' }
    },
    {
        path: '/live-matches',
        name: 'LiveMatches',
        component: LiveMatches,
        meta: { title: 'Live Matches' }
    },
    {
        path: '/live-matches/:id',
        name: 'LiveMatchDetail',
        component: LiveMatchDetail,
        meta: { title: 'Live Match' }
    },
    {
        path: '/predictions',
        name: 'Predictions',
        component: Predictions,
        meta: { title: 'Predictions' }
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: Analytics,
        meta: { title: 'Analytics' }
    },
    {
        path: '/search',
        name: 'Search',
        component: Search,
        meta: { title: 'Search' }
    }
];

const router = createRouter({
    history: createWebHistory('/'),
    routes
});

// Update page title
router.beforeEach((to, from, next) => {
    document.title = `${to.meta.title} - Cricket League`;
    next();
});

export default router;
