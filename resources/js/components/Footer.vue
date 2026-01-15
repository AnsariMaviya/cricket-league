<template>
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Cricket League</h3>
                    <p class="text-gray-300 text-sm">
                        A comprehensive cricket management system with real-time updates, 
                        advanced analytics, and modern web technologies.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><router-link to="/" class="text-gray-300 hover:text-white">Dashboard</router-link></li>
                        <li><router-link to="/analytics" class="text-gray-300 hover:text-white">Analytics</router-link></li>
                        <li><router-link to="/search" class="text-gray-300 hover:text-white">Search</router-link></li>
                    </ul>
                </div>

                <!-- Technologies -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Technologies</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Laravel</span>
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Vue.js</span>
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">Tailwind CSS</span>
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">MySQL</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; {{ currentYear }} Cricket League Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'Footer',
    computed: {
        currentYear() {
            return new Date().getFullYear();
        }
    }
}
</script>
