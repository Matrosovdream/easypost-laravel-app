import MarkdownIt from 'markdown-it';

const md = new MarkdownIt({ html: false, linkify: true, typographer: true });

const modules = import.meta.glob('/resources/js/web/content/legal/*.md', {
    query: '?raw',
    import: 'default',
    eager: true,
}) as Record<string, string>;

const docs: Record<string, string> = {};
for (const [path, raw] of Object.entries(modules)) {
    const slug = path.split('/').pop()!.replace(/\.md$/, '');
    docs[slug] = md.render(raw);
}

export function useLegalDoc(slug: string): string | undefined {
    return docs[slug];
}
