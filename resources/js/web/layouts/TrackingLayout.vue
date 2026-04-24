<script setup lang="ts">
import { useHead } from '@unhead/vue';

useHead({
    meta: [{ name: 'robots', content: 'noindex,nofollow' }],
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-surface-50">
        <header class="bg-white border-b border-surface-200 px-6 py-4">
            <div class="max-w-2xl mx-auto flex items-center justify-between">
                <router-link to="/" class="flex items-center gap-2 font-bold text-surface-900">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-primary-500 text-white">
                        <i class="pi pi-send text-xs"></i>
                    </span>
                    ShipDesk
                </router-link>
                <a href="https://shipdesk.local" class="text-xs text-surface-500 hover:text-surface-900">
                    Powered by ShipDesk
                </a>
            </div>
        </header>

        <main class="flex-1 max-w-2xl mx-auto w-full p-6">
            <slot />
        </main>

        <footer class="text-center text-xs text-surface-500 py-4">
            Need help? <a href="mailto:help@shipdesk.local" class="underline">Contact support</a>
        </footer>
    </div>
</template>
