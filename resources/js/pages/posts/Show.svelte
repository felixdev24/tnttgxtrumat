<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    let { post } = $props<{
        post: any;
    }>();

    function formatDate(dateString: string) {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).format(date);
    }

    // Default image if cover_image is null
    const defaultImage = "https://lh3.googleusercontent.com/aida-public/AB6AXuAqdDcDCk2qLStSnA-lZRzrEp-LkI2_KhBRH-xE9dQeTU7wxDXSpzZyH08uwatiCbI08rjest95qedeXGasoIe9aw8Y9JwrZUXvLXlX9qVQ_ky-sW50G0YTUgBycUf3yp2L19lPyCEnL-CsiuMSNbZ3SGrZPvCl8L7gMS1jWgP5-c1tuM2LzkmwO3srPMyXu06tq3vMBsepetE_pDt5YWbzxlUk8H27lMTUF5rpQfhb7a9Yr0HO8bc7Q4HV8LNq-dBkIJ6OeMw7rDs";
</script>

<svelte:head>
    <title>{post.title} - Thiếu Nhi Thánh Thể</title>
</svelte:head>

<main class="mt-24 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pb-section-gap">
    <article class="max-w-4xl mx-auto">
        <!-- Navigation Breadcrumb -->
        <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-6">
            <Link href="/hoat-dong" class="hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Trở về Hoạt động
            </Link>
        </nav>

        <!-- Header -->
        <header class="mb-stack-lg space-y-4">
            {#if post.category}
                <div class="inline-block bg-primary/10 text-primary font-label-bold px-3 py-1 rounded-full text-sm">
                    {post.category}
                </div>
            {/if}
            
            <h1 class="font-display-lg text-4xl md:text-5xl text-on-surface">{post.title}</h1>
            
            <div class="flex items-center gap-4 text-on-surface-variant pt-4">
                <div class="flex items-center gap-2">
                    {#if post.user?.avatar}
                        <img src={post.user.avatar} alt={post.user.name} class="w-10 h-10 rounded-full" />
                    {:else}
                        <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">person</span>
                        </div>
                    {/if}
                    <div class="flex flex-col">
                        <span class="font-label-bold text-on-surface">{post.user?.name || 'Tác giả'}</span>
                        <span class="text-[12px] flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                            {formatDate(post.created_at)}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Cover Image -->
        <div class="rounded-2xl overflow-hidden mb-stack-lg shadow-sm">
            <img 
                src={post.cover_image ? `/storage/${post.cover_image}` : defaultImage} 
                alt={post.title}
                class="w-full h-auto max-h-[500px] object-cover"
            />
        </div>

        <!-- Content -->
        <div class="prose prose-lg dark:prose-invert max-w-none 
                    prose-headings:font-headline-lg prose-headings:text-primary 
                    prose-a:text-tertiary prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-xl prose-img:shadow-sm">
            {@html post.content}
        </div>

        <!-- Tags -->
        {#if post.tags && post.tags.length > 0}
            <div class="mt-section-gap pt-stack-md border-t border-outline-variant/30">
                <h4 class="font-title-md text-secondary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">sell</span>
                    Thẻ
                </h4>
                <div class="flex flex-wrap gap-2">
                    {#each post.tags as tag}
                        <span class="bg-surface-container-high text-on-surface-variant px-3 py-1 rounded-full text-sm font-label-bold">
                            {tag}
                        </span>
                    {/each}
                </div>
            </div>
        {/if}
    </article>
</main>

<style>
    /* Styling for Tiptap editor content */
    :global(.prose img) {
        margin-left: auto;
        margin-right: auto;
    }
    :global(.prose blockquote) {
        border-left-color: #42617d;
        background-color: rgba(66, 97, 125, 0.05);
        padding: 1rem;
        border-radius: 0 0.5rem 0.5rem 0;
        font-style: italic;
    }
</style>
