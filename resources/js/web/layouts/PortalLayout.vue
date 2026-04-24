<script setup lang="ts"></script>

<template>
    <div class="min-h-screen flex flex-col bg-surface-50">
        <header class="px-6 py-4 border-b border-surface-200 bg-white">
            <div class="container mx-auto flex items-center justify-between">
                <router-link to="/" class="flex items-center gap-2 font-bold text-xl text-surface-900">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-primary-500 text-white">
                        <i class="pi pi-send text-sm"></i>
                    </span>
                    ShipDesk
                </router-link>
                <router-link to="/" class="text-sm text-surface-500 hover:text-surface-900">
                    <i class="pi pi-arrow-left text-xs"></i> Back to site
                </router-link>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </main>

        <footer class="px-6 py-6 text-center text-xs text-surface-500 space-x-4 border-t border-surface-200 bg-white">
            <router-link to="/portal/terms" class="hover:text-surface-900">Terms</router-link>
            <router-link to="/portal/privacy" class="hover:text-surface-900">Privacy</router-link>
            <router-link to="/contact" class="hover:text-surface-900">Contact</router-link>
        </footer>
    </div>
</template>
