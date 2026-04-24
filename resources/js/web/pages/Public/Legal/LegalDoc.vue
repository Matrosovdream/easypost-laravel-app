<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import { useLegalDoc } from '@web/composables/useLegal';

const props = defineProps<{ slug: string; title: string; description: string }>();

const html = useLegalDoc(props.slug);

useSeo({ title: `${props.title} — ShipDesk`, description: props.description });
</script>

<template>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 max-w-3xl">
            <article v-if="html" class="prose-legal" v-html="html"></article>
            <p v-else class="text-surface-500">Document not available.</p>
        </div>
    </section>
</template>

<style scoped>
.prose-legal :deep(h1) {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--p-surface-900);
    margin-bottom: 1rem;
}
.prose-legal :deep(h2) {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--p-surface-900);
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}
.prose-legal :deep(p) {
    color: var(--p-surface-700);
    line-height: 1.7;
    margin-bottom: 1rem;
}
.prose-legal :deep(ul),
.prose-legal :deep(ol) {
    margin: 1rem 0 1rem 1.5rem;
}
.prose-legal :deep(li) {
    color: var(--p-surface-700);
    margin-bottom: 0.25rem;
    line-height: 1.6;
}
.prose-legal :deep(strong) {
    color: var(--p-surface-900);
    font-weight: 600;
}
.prose-legal :deep(a) {
    color: var(--p-primary-600);
    text-decoration: underline;
}
</style>
