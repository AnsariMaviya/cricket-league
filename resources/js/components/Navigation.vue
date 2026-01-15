<template>
    <nav class="bg-gray-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <router-link to="/" class="text-xl font-bold text-white hover:text-gray-300">
                        🏏 Cricket League
                    </router-link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex space-x-6">
                    <router-link 
                        v-for="item in navItems" 
                        :key="item.name"
                        :to="item.path"
                        class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 hover:text-white transition-colors"
                        :class="{ 'bg-gray-900 text-white': $route.path === item.path }"
                    >
                        {{ item.name }}
                    </router-link>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button 
                        @click="toggleMobileMenu"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
                    >
                        <svg class="h-6 w-6" :class="{ 'hidden': mobileMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" :class="{ 'hidden': !mobileMenuOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div :class="{ 'hidden': !mobileMenuOpen }" class="md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <router-link 
                        v-for="item in navItems" 
                        :key="item.name"
                        :to="item.path"
                        class="block px-3 py-2 rounded-md text-base font-medium hover:bg-gray-700 hover:text-white transition-colors"
                        :class="{ 'bg-gray-900 text-white': $route.path === item.path }"
                        @click="closeMobileMenu"
                    >
                        {{ item.name }}
                    </router-link>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
export default {
    name: 'Navigation',
    data() {
        return {
            mobileMenuOpen: false,
            navItems: [
                { name: 'Dashboard', path: '/' },
                { name: 'Countries', path: '/countries' },
                { name: 'Teams', path: '/teams' },
                { name: 'Players', path: '/players' },
                { name: 'Venues', path: '/venues' },
                { name: 'Matches', path: '/matches' },
                { name: 'Analytics', path: '/analytics' },
                { name: 'Search', path: '/search' }
            ]
        }
    },
    methods: {
        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },
        closeMobileMenu() {
            this.mobileMenuOpen = false;
        }
    }
}
</script>
