<template>
    <div id="app">
        <Navigation />
        <main class="container mx-auto px-4 py-8">
            <router-view />
        </main>
        <Footer />
        <Toast />
    </div>
</template>

<script>
import Navigation from './components/Navigation.vue';
import Footer from './components/Footer.vue';
import Toast from './components/ui/Toast.vue';

export default {
    name: 'App',
    components: {
        Navigation,
        Footer,
        Toast
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
