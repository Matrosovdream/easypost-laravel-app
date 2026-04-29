<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useSeo } from '@web/composables/useSeo';
import { useBlogPost } from '@web/composables/useBlog';
import Button from 'primevue/button';

const route = useRoute();
const slug = computed(() => String(route.params.slug ?? ''));
const post = computed(() => useBlogPost(slug.value));

useSeo({
    title: post.value ? `${post.value.title} — ShipDesk` : 'Blog post — ShipDesk',
    description: post.value?.excerpt,
    ogImage: post.value?.ogImage,
});

function formatDate(iso: string): string {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}
</script>

<template>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 max-w-3xl">
            <div v-if="!post" class="text-center py-24">
                <h1 class="text-2xl font-bold text-surface-900">Post not found</h1>
                <router-link to="/blog" class="inline-block mt-6">
                    <Button label="Back to blog" severity="secondary" outlined />
                </router-link>
            </div>

            <template v-else>
                <router-link to="/blog" class="text-sm text-primary-600 hover:underline">
                    <i class="pi pi-arrow-left text-xs"></i> Back to blog
                </router-link>

                <div class="text-xs text-surface-500 mt-6">
                    {{ formatDate(post.date) }} · {{ post.author ?? 'ShipDesk team' }}
                </div>
                <h1 class="mt-2 text-4xl font-bold text-surface-900 leading-tight">{{ post.title }}</h1>
                <p v-if="post.excerpt" class="mt-4 text-lg text-surface-600">{{ post.excerpt }}</p>

                <article class="prose-blog mt-10" v-html="post.bodyHtml"></article>

                <div class="mt-16 pt-8 border-t border-surface-200 text-center">
                    <p class="text-surface-600">Want to see this in action?</p>
                    <router-link to="/portal/register">
                        <Button label="Start free trial" class="mt-4" />
                    </router-link>
                </div>
            </template>
        </div>
    </section>
</template>

<style scoped>
.prose-blog :deep(h2) {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--p-surface-900);
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}
.prose-blog :deep(h3) {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--p-surface-900);
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}
.prose-blog :deep(p) {
    color: var(--p-surface-700);
    line-height: 1.7;
    margin-bottom: 1rem;
}
.prose-blog :deep(ul),
.prose-blog :deep(ol) {
    margin: 1rem 0 1rem 1.5rem;
}
.prose-blog :deep(li) {
    color: var(--p-surface-700);
    margin-bottom: 0.25rem;
    line-height: 1.6;
}
.prose-blog :deep(strong) {
    color: var(--p-surface-900);
    font-weight: 600;
}
.prose-blog :deep(a) {
    color: var(--p-primary-600);
    text-decoration: underline;
}
.prose-blog :deep(code) {
    background: var(--p-surface-100);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}
</style>
