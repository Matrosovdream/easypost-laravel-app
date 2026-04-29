<script setup lang="ts">
import NavBar from '@web/components/marketing/NavBar.vue';
import MarketingFooter from '@web/components/marketing/MarketingFooter.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <NavBar />
        <main class="flex-1">
            <slot />
        </main>
        <MarketingFooter />
    </div>
</template>
