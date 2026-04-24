<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    icon?: string;
    trend?: string;
    tone?: 'neutral' | 'positive' | 'negative';
}>();
</script>

<template>
    <div class="bg-white rounded-xl border border-surface-200 p-5">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-surface-500 uppercase tracking-wider">{{ label }}</div>
                <div class="mt-2 text-3xl font-bold text-surface-900">{{ value }}</div>
                <div
                    v-if="trend"
                    class="mt-1 text-xs font-medium"
                    :class="{
                        'text-green-600': tone === 'positive',
                        'text-red-600': tone === 'negative',
                        'text-surface-500': !tone || tone === 'neutral',
                    }"
                >{{ trend }}</div>
            </div>
            <div v-if="icon" class="w-10 h-10 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                <i :class="`pi ${icon}`"></i>
            </div>
        </div>
    </div>
</template>
