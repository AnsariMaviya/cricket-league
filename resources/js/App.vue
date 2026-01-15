<template>
    <div id="app">
        <Navigation />
        <main class="container mx-auto px-4 py-8">
            <router-view />
        </main>
        <Footer />
    </div>
</template>

<script>
import Navigation from './components/Navigation.vue';
import Footer from './components/Footer.vue';
import router from './router';

export default {
    name: 'App',
    components: {
        Navigation,
        Footer
    }
}
</script>

<style>
#app {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1;
}
</style>
