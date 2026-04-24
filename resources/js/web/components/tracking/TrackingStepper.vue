<script setup lang="ts">
type Status = 'pre_transit' | 'in_transit' | 'out_for_delivery' | 'delivered' | 'unknown';

const props = defineProps<{ status: Status }>();

const steps: { value: Status; label: string; icon: string }[] = [
    { value: 'pre_transit', label: 'Labeled', icon: 'pi-tag' },
    { value: 'in_transit', label: 'In transit', icon: 'pi-truck' },
    { value: 'out_for_delivery', label: 'Out for delivery', icon: 'pi-send' },
    { value: 'delivered', label: 'Delivered', icon: 'pi-check-circle' },
];

function indexOf(s: Status): number {
    const i = steps.findIndex((step) => step.value === s);
    return i === -1 ? 0 : i;
}
</script>

<template>
    <div class="flex items-center justify-between w-full">
        <template v-for="(step, i) in steps" :key="step.value">
            <div class="flex flex-col items-center text-center flex-1 min-w-0">
                <div
                    class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                    :class="i <= indexOf(props.status)
                        ? 'bg-primary-500 text-white'
                        : 'bg-surface-200 text-surface-500'"
                >
                    <i :class="`pi ${step.icon}`"></i>
                </div>
                <div
                    class="mt-2 text-xs font-medium truncate w-full"
                    :class="i <= indexOf(props.status) ? 'text-surface-900' : 'text-surface-500'"
                >
                    {{ step.label }}
                </div>
            </div>
            <div
                v-if="i < steps.length - 1"
                class="h-0.5 flex-1 mx-1 -mt-5 rounded"
                :class="i < indexOf(props.status) ? 'bg-primary-500' : 'bg-surface-200'"
            ></div>
        </template>
    </div>
</template>
