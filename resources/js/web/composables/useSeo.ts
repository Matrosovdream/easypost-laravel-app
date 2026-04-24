import { useHead } from '@unhead/vue';

type Seo = {
    title?: string;
    description?: string;
    ogImage?: string;
    noindex?: boolean;
};

export function useSeo(seo: Seo) {
    useHead({
        title: seo.title,
        meta: [
            seo.description ? { name: 'description', content: seo.description } : null,
            seo.description ? { property: 'og:description', content: seo.description } : null,
            seo.title ? { property: 'og:title', content: seo.title } : null,
            seo.ogImage ? { property: 'og:image', content: seo.ogImage } : null,
            seo.noindex ? { name: 'robots', content: 'noindex,nofollow' } : null,
        ].filter((m): m is { name?: string; property?: string; content: string } => m !== null),
    });
}
