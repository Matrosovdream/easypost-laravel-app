import MarkdownIt from 'markdown-it';

const md = new MarkdownIt({ html: false, linkify: true, typographer: true });

type Frontmatter = {
    title: string;
    date: string;
    author?: string;
    excerpt?: string;
    ogImage?: string;
};

export type BlogPost = Frontmatter & {
    slug: string;
    bodyHtml: string;
    bodyMd: string;
};

const modules = import.meta.glob('/resources/js/web/content/blog/*.md', {
    query: '?raw',
    import: 'default',
    eager: true,
}) as Record<string, string>;

function parseFrontmatter(raw: string): { fm: Frontmatter; body: string } {
    const match = raw.match(/^---\n([\s\S]*?)\n---\n([\s\S]*)$/);
    if (!match) return { fm: {} as Frontmatter, body: raw };
    const lines = match[1].split('\n');
    const fm: Record<string, string> = {};
    for (const line of lines) {
        const idx = line.indexOf(':');
        if (idx < 0) continue;
        const key = line.slice(0, idx).trim();
        let value = line.slice(idx + 1).trim();
        if (value.startsWith('"') && value.endsWith('"')) value = value.slice(1, -1);
        fm[key] = value;
    }
    return { fm: fm as unknown as Frontmatter, body: match[2] };
}

function slugFromPath(path: string): string {
    return path.split('/').pop()!.replace(/\.md$/, '');
}

const posts: BlogPost[] = Object.entries(modules)
    .map(([path, raw]) => {
        const { fm, body } = parseFrontmatter(raw);
        return {
            ...fm,
            slug: slugFromPath(path),
            bodyMd: body,
            bodyHtml: md.render(body),
        };
    })
    .sort((a, b) => (a.date < b.date ? 1 : -1));

export function useBlogPosts(): BlogPost[] {
    return posts;
}

export function useBlogPost(slug: string): BlogPost | undefined {
    return posts.find((p) => p.slug === slug);
}
