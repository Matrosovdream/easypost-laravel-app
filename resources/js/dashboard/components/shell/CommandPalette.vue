<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import { nav, type NavItem } from '@dashboard/config/nav';
import { useCan } from '@dashboard/composables/useCan';
import { useRouter } from 'vue-router';

const visible = ref(false);
const query = ref('');
const router = useRouter();
const { can, canAny } = useCan();

type Hit = { label: string; hint: string; to: string };

const allItems = computed<Hit[]>(() => {
    const hits: Hit[] = [];
    for (const section of nav) {
        for (const item of section.items) {
            if (!item.to) continue;
            if (item.right && !can(item.right)) continue;
            if (item.anyRight && !canAny(item.anyRight)) continue;
            hits.push({ label: item.label, hint: section.label, to: item.to });
        }
    }
    return hits;
});

const filtered = computed<Hit[]>(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return allItems.value.slice(0, 8);
    return allItems.value
        .filter((h) => h.label.toLowerCase().includes(q) || h.hint.toLowerCase().includes(q))
        .slice(0, 10);
});

function open(): void {
    visible.value = true;
    query.value = '';
}

function go(to: string): void {
    visible.value = false;
    router.push(to);
}

function onKey(e: KeyboardEvent): void {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        open();
    }
    if (e.key === 'Escape') visible.value = false;
}

onMounted(() => window.addEventListener('keydown', onKey));
onBeforeUnmount(() => window.removeEventListener('keydown', onKey));

defineExpose({ open });
</script>

<template>
    <Dialog v-model:visible="visible" :modal="true" :closable="false" :header="null" class="w-[30rem] max-w-full">
        <template #container>
            <div class="bg-white rounded-lg shadow-xl border border-surface-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-surface-200">
                    <InputText
                        v-model="query"
                        placeholder="Search pages, shipments, clients…"
                        class="w-full !border-0 !shadow-none !px-0 !py-1 focus:!ring-0"
                        autofocus
                    />
                </div>
                <ul class="max-h-80 overflow-y-auto py-2">
                    <li
                        v-for="hit in filtered"
                        :key="hit.to"
                        class="px-4 py-2 hover:bg-surface-50 cursor-pointer flex items-center justify-between"
                        @click="go(hit.to)"
                    >
                        <span class="text-sm text-surface-900">{{ hit.label }}</span>
                        <span class="text-xs text-surface-400">{{ hit.hint }}</span>
                    </li>
                    <li v-if="filtered.length === 0" class="px-4 py-6 text-center text-sm text-surface-500">
                        No results
                    </li>
                </ul>
                <div class="px-4 py-2 border-t border-surface-100 text-xs text-surface-400 flex items-center gap-3">
                    <span><kbd class="px-1.5 py-0.5 rounded bg-surface-100 text-surface-600">↵</kbd> to open</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-surface-100 text-surface-600">esc</kbd> to close</span>
                </div>
            </div>
        </template>
    </Dialog>
</template>
