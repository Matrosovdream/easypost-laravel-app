<script setup lang="ts">
defineProps<{
    eyebrow: string;
    title: string;
    body: string;
    bullets: string[];
    reverse?: boolean;
    image?: string;
}>();
</script>

<template>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div
                class="grid lg:grid-cols-2 gap-14 items-center"
                :class="reverse ? 'lg:[&>div:first-child]:order-2' : ''"
            >
                <div>
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold tracking-wider uppercase bg-accent-50 text-accent-700 border border-accent-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-500"></span>
                        {{ eyebrow }}
                    </p>
                    <h2 class="mt-4 text-3xl lg:text-4xl font-bold text-ink-900 tracking-tight leading-tight">{{ title }}</h2>
                    <p class="mt-4 text-lg text-ink-600 leading-relaxed">{{ body }}</p>
                    <ul class="mt-6 space-y-3">
                        <li
                            v-for="b in bullets"
                            :key="b"
                            class="flex items-start gap-3 text-ink-700"
                        >
                            <span class="mt-0.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white flex-shrink-0">
                                <i class="pi pi-check text-[10px]"></i>
                            </span>
                            <span>{{ b }}</span>
                        </li>
                    </ul>
                </div>

                <div class="relative">
                    <div class="absolute -top-6 -left-6 w-28 h-28 rounded-full bg-primary-100/70 blur-2xl"></div>
                    <div class="absolute -bottom-6 -right-6 w-28 h-28 rounded-full bg-accent-100/70 blur-2xl"></div>

                    <div class="relative aspect-[5/4] rounded-2xl bg-gradient-to-br from-primary-50 via-white to-accent-50 border border-ink-100 card-lift-lg overflow-hidden">
                        <img v-if="image" :src="image" :alt="title" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full p-6 flex flex-col justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-accent-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span class="ml-3 text-xs font-mono text-ink-400">shipdesk.app</span>
                            </div>

                            <div class="space-y-3">
                                <div class="bg-white rounded-xl p-4 border border-ink-100 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-50 text-primary-600">
                                                <i class="pi pi-box text-sm"></i>
                                            </span>
                                            <div>
                                                <div class="text-xs font-semibold text-ink-900">Order #A-48201</div>
                                                <div class="text-[10px] text-ink-400">3.2 lb · NY → CA</div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold text-primary-700">$8.24</span>
                                    </div>
                                </div>

                                <div class="bg-white rounded-xl p-4 border border-ink-100 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-accent-50 text-accent-600">
                                                <i class="pi pi-check-circle text-sm"></i>
                                            </span>
                                            <div>
                                                <div class="text-xs font-semibold text-ink-900">Label printed</div>
                                                <div class="text-[10px] text-emerald-600">USPS Priority · saved $2.17</div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono text-ink-400">12:04</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-ink-500 px-1">
                                    <span>Rate-shop latency</span>
                                    <span class="font-semibold text-primary-700">54 ms</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
