<script setup lang="ts">
const year = new Date().getFullYear();

const columns = [
    {
        heading: 'Product',
        links: [
            { to: '/features', label: 'Features' },
            { to: '/pricing', label: 'Pricing' },
            { to: '/customers', label: 'Customers' },
            { to: '/track/EZ2000000002', label: 'Tracking demo' },
        ],
    },
    {
        heading: 'Company',
        links: [
            { to: '/about', label: 'About' },
            { to: '/blog', label: 'Blog' },
            { to: '/contact', label: 'Contact' },
        ],
    },
    {
        heading: 'Legal',
        links: [
            { to: '/portal/terms', label: 'Terms' },
            { to: '/portal/privacy', label: 'Privacy' },
            { to: '/portal/dpa', label: 'DPA' },
        ],
    },
];
</script>

<template>
    <footer class="relative bg-ink-950 text-ink-300 overflow-hidden">
        <div class="absolute inset-0 pointer-events-none opacity-50">
            <div class="absolute -top-40 -left-20 w-96 h-96 rounded-full bg-primary-800/30 blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 py-16 relative">
            <div class="grid md:grid-cols-5 gap-10">
                <div class="md:col-span-2">
                    <router-link to="/" class="flex items-center gap-2.5 font-bold text-xl text-white">
                        <span class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl bg-primary-600 text-white shadow-lg shadow-primary-600/40">
                            <i class="pi pi-send text-sm"></i>
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-accent-500 ring-2 ring-ink-950"></span>
                        </span>
                        <span class="tracking-tight">ShipDesk</span>
                    </router-link>
                    <p class="mt-5 text-sm leading-relaxed max-w-sm text-ink-400">
                        Role-based shipping for real teams. Built on EasyPost. Built for the way you actually work.
                    </p>

                    <div class="mt-6 flex items-center gap-3">
                        <a href="#" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-ink-300 hover:bg-white/10 hover:text-white transition-colors" aria-label="Twitter">
                            <i class="pi pi-twitter"></i>
                        </a>
                        <a href="#" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-ink-300 hover:bg-white/10 hover:text-white transition-colors" aria-label="LinkedIn">
                            <i class="pi pi-linkedin"></i>
                        </a>
                        <a href="#" class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 text-ink-300 hover:bg-white/10 hover:text-white transition-colors" aria-label="GitHub">
                            <i class="pi pi-github"></i>
                        </a>
                    </div>
                </div>

                <div v-for="col in columns" :key="col.heading">
                    <h4 class="text-white font-semibold text-xs uppercase tracking-[0.18em]">{{ col.heading }}</h4>
                    <ul class="mt-4 space-y-3">
                        <li v-for="l in col.links" :key="l.to">
                            <router-link :to="l.to" class="text-sm text-ink-400 hover:text-white transition-colors">{{ l.label }}</router-link>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
                <p class="text-ink-500">&copy; {{ year }} ShipDesk. All rights reserved.</p>
                <p class="text-ink-500">Built on EasyPost · Laravel · Vue</p>
            </div>
        </div>
    </footer>
</template>
