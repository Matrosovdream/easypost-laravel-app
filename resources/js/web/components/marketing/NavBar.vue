<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';

const isOpen = ref(false);
const links = [
    { to: '/features', label: 'Features' },
    { to: '/pricing', label: 'Pricing' },
    { to: '/customers', label: 'Customers' },
    { to: '/about', label: 'About' },
    { to: '/blog', label: 'Blog' },
    { to: '/contact', label: 'Contact' },
];
</script>

<template>
    <header class="bg-white/85 backdrop-blur-md border-b border-ink-100 sticky top-0 z-40">
        <nav class="container mx-auto px-6 py-4 flex items-center justify-between">
            <router-link to="/" class="flex items-center gap-2.5 font-bold text-xl text-ink-900 group">
                <span class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-primary-600 text-white shadow-md shadow-primary-600/25 transition-transform group-hover:scale-105">
                    <i class="pi pi-send text-sm"></i>
                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-accent-500 ring-2 ring-white"></span>
                </span>
                <span class="tracking-tight">ShipDesk</span>
            </router-link>

            <div class="hidden lg:flex items-center gap-1 text-sm font-medium">
                <router-link
                    v-for="l in links"
                    :key="l.to"
                    :to="l.to"
                    class="px-3 py-2 rounded-lg text-ink-700 hover:text-primary-600 hover:bg-primary-50/60 transition-colors"
                    active-class="text-primary-700 bg-primary-50"
                >{{ l.label }}</router-link>
            </div>

            <div class="hidden lg:flex items-center gap-2">
                <router-link to="/portal/login" class="text-sm font-medium text-ink-700 hover:text-primary-600 px-3 py-2">
                    Sign in
                </router-link>
                <router-link to="/portal/register">
                    <Button label="Start free" icon="pi pi-arrow-right" icon-pos="right" rounded class="!bg-accent-500 !border-accent-500 hover:!bg-accent-600 hover:!border-accent-600 !font-semibold shadow-md shadow-accent-500/25" size="small" />
                </router-link>
            </div>

            <button
                class="lg:hidden text-ink-700 hover:text-ink-900 p-2 rounded-lg hover:bg-ink-50"
                aria-label="toggle menu"
                @click="isOpen = !isOpen"
            >
                <i :class="isOpen ? 'pi pi-times' : 'pi pi-bars'"></i>
            </button>
        </nav>

        <div
            v-if="isOpen"
            class="lg:hidden border-t border-ink-100 bg-white px-6 py-4 space-y-1"
        >
            <router-link
                v-for="l in links"
                :key="l.to"
                :to="l.to"
                class="block px-3 py-2 rounded-lg text-ink-700 hover:bg-primary-50"
                @click="isOpen = false"
            >{{ l.label }}</router-link>
            <div class="pt-3 mt-3 border-t border-ink-100 flex flex-col gap-2">
                <router-link to="/portal/login" @click="isOpen = false">
                    <Button label="Sign in" severity="secondary" outlined rounded class="w-full" />
                </router-link>
                <router-link to="/portal/register" @click="isOpen = false">
                    <Button label="Start free" rounded class="w-full !bg-accent-500 !border-accent-500 hover:!bg-accent-600 hover:!border-accent-600" />
                </router-link>
            </div>
        </div>
    </header>
</template>
