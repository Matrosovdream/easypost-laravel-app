<script setup lang="ts">
import { useSeo } from '@web/composables/useSeo';
import { useBlogPosts } from '@web/composables/useBlog';

useSeo({
    title: 'Blog — ShipDesk',
    description: 'Essays on shipping operations, 3PL margin, role-based workflows, and what we are learning in production.',
});

const posts = useBlogPosts();

function formatDate(iso: string): string {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}
</script>

<template>
    <section class="bg-gradient-to-b from-surface-50 to-white py-16">
        <div class="container mx-auto px-6 max-w-3xl text-center">
            <h1 class="text-4xl lg:text-5xl font-bold text-surface-900">The ShipDesk blog</h1>
            <p class="mt-4 text-lg text-surface-600">
                Essays from the team on shipping ops, 3PL margin, and what we're learning in production.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 max-w-4xl">
            <div class="grid md:grid-cols-2 gap-8">
                <article
                    v-for="post in posts"
                    :key="post.slug"
                    class="rounded-xl border border-surface-200 p-6 hover:shadow-md hover:border-primary-200 transition-all"
                >
                    <div class="aspect-video rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center mb-5">
                        <i class="pi pi-book text-4xl text-primary-400"></i>
                    </div>
                    <div class="text-xs text-surface-500">
                        {{ formatDate(post.date) }} · {{ post.author ?? 'ShipDesk team' }}
                    </div>
                    <h2 class="mt-2 text-xl font-semibold text-surface-900 leading-tight">
                        <router-link :to="`/blog/${post.slug}`" class="hover:text-primary-600">
                            {{ post.title }}
                        </router-link>
                    </h2>
                    <p class="mt-3 text-surface-600 text-sm leading-relaxed">{{ post.excerpt }}</p>
                    <router-link
                        :to="`/blog/${post.slug}`"
                        class="mt-4 inline-flex items-center gap-1 text-sm text-primary-600 hover:underline"
                    >
                        Read post <i class="pi pi-arrow-right text-xs"></i>
                    </router-link>
                </article>
            </div>
        </div>
    </section>
</template>
