<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    let { posts, categories, filters } = $props<{
        posts: any;
        categories: string[];
        filters: {
            category?: string;
            search?: string;
        };
    }>();

    let searchQuery = $state(filters.search || '');
    let activeCategory = $state(filters.category || '');

    function handleSearch() {
        router.get('/hoat-dong', {
            search: searchQuery,
            category: activeCategory
        }, { preserveState: true, preserveScroll: true });
    }

    function setCategory(category: string) {
        activeCategory = category;
        handleSearch();
    }

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
    <title>Truyền thông & Hoạt động - Thiếu Nhi Thánh Thể</title>
</svelte:head>

<main class="mt-24 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pb-section-gap">
    <!-- Hero Section -->
    <section class="mb-section-gap">
        <div class="text-center max-w-3xl mx-auto space-y-stack-md">
            <h2 class="font-display-lg text-display-lg text-primary dark:text-primary-fixed-dim">Gắn kết & Lan tỏa</h2>
            <p class="font-body-lg text-body-lg text-on-surface-variant">Cập nhật những hoạt động mới nhất, những hình ảnh rạng rỡ và những video tràn đầy sức trẻ của phong trào Thiếu Nhi Thánh Thể Việt Nam.</p>
        </div>
    </section>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        
        <!-- Sidebar / Filter Column -->
        <aside class="lg:col-span-4 space-y-gutter">
            <div class="glass-card p-stack-md rounded-xl">
                <h3 class="font-title-md text-title-md text-secondary mb-4">Tìm kiếm</h3>
                <div class="relative">
                    <input 
                        type="text" 
                        bind:value={searchQuery}
                        onkeydown={(e) => e.key === 'Enter' && handleSearch()}
                        placeholder="Tìm bài viết..."
                        class="w-full bg-surface-container-lowest dark:bg-surface-dim rounded-xl px-4 py-3 pl-10 border border-outline-variant/30 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"
                    >
                    <span class="material-symbols-outlined absolute left-3 top-3.5 text-outline">search</span>
                </div>
            </div>

            <div class="glass-card p-stack-md rounded-xl">
                <h3 class="font-title-md text-title-md text-secondary mb-4">Danh mục</h3>
                <div class="flex flex-col gap-2">
                    <button 
                        onclick={() => setCategory('')}
                        class="text-left px-4 py-2 rounded-lg transition-colors {activeCategory === '' ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-surface-container-high text-on-surface-variant'}"
                    >
                        Tất cả thông báo
                    </button>
                    {#each categories as category}
                        <button 
                            onclick={() => setCategory(category)}
                            class="text-left px-4 py-2 rounded-lg transition-colors {activeCategory === category ? 'bg-primary/10 text-primary font-bold' : 'hover:bg-surface-container-high text-on-surface-variant'}"
                        >
                            {category}
                        </button>
                    {/each}
                </div>
            </div>
        </aside>

        <!-- Main Content Column -->
        <section class="lg:col-span-8 space-y-gutter">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-title-md text-title-md text-secondary">
                    {activeCategory ? activeCategory : 'Thông báo mới'}
                </h3>
            </div>

            {#if posts.data.length === 0}
                <div class="glass-card p-stack-lg rounded-xl text-center py-20">
                    <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">article</span>
                    <h4 class="font-title-md text-on-surface">Không tìm thấy bài viết nào</h4>
                    <p class="text-on-surface-variant mt-2">Thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm của bạn.</p>
                    {#if activeCategory || searchQuery}
                        <button 
                            onclick={() => { searchQuery = ''; setCategory(''); }}
                            class="mt-6 px-6 py-2 bg-primary/10 text-primary rounded-full font-label-bold hover:bg-primary/20 transition-colors"
                        >
                            Xóa bộ lọc
                        </button>
                    {/if}
                </div>
            {:else}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter masonry-grid">
                    {#each posts.data as post}
                        <!-- News Card -->
                        <div class="glass-card rounded-xl hover:shadow-lg transition-all duration-300 group overflow-hidden flex flex-col h-full masonry-item bg-white dark:bg-surface-container">
                            <Link href={`/hoat-dong/${post.slug}`} class="block relative h-48 overflow-hidden shrink-0">
                                <img 
                                    src={post.cover_image ? `/storage/${post.cover_image}` : defaultImage} 
                                    alt={post.title}
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                                />
                                {#if post.category}
                                    <div class="absolute top-4 left-4 bg-white/90 dark:bg-surface-dim/90 backdrop-blur-sm px-3 py-1 rounded-full text-[12px] font-label-bold text-primary shadow-sm">
                                        {post.category}
                                    </div>
                                {/if}
                            </Link>
                            <div class="p-stack-md flex flex-col flex-grow">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-[12px] text-outline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                        {formatDate(post.created_at)}
                                    </span>
                                </div>
                                <Link href={`/hoat-dong/${post.slug}`}>
                                    <h4 class="font-title-md text-[18px] leading-tight text-on-surface group-hover:text-primary transition-colors mb-2 line-clamp-2">
                                        {post.title}
                                    </h4>
                                </Link>
                                <p class="text-body-md text-on-surface-variant text-[14px] line-clamp-3 mb-4 flex-grow">
                                    {@html post.content.replace(/<[^>]*>?/gm, '').substring(0, 150)}...
                                </p>
                                
                                <div class="mt-auto pt-4 border-t border-outline-variant/20 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        {#if post.user?.avatar}
                                            <img src={post.user.avatar} alt={post.user.name} class="w-6 h-6 rounded-full" />
                                        {:else}
                                            <div class="w-6 h-6 rounded-full bg-primary/20 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-[12px] text-primary">person</span>
                                            </div>
                                        {/if}
                                        <span class="text-[12px] text-on-surface-variant">{post.user?.name || 'Tác giả'}</span>
                                    </div>
                                    <Link href={`/hoat-dong/${post.slug}`} class="text-primary text-[14px] font-label-bold flex items-center gap-1 hover:underline">
                                        Đọc tiếp <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>

                <!-- Pagination -->
                {#if posts.links && posts.links.length > 3}
                    <div class="flex justify-center mt-stack-lg pt-stack-md">
                        <div class="flex items-center gap-2 bg-white dark:bg-surface-dim px-4 py-2 rounded-full shadow-sm">
                            {#each posts.links as link}
                                <Link 
                                    href={link.url || '#'} 
                                    class="w-10 h-10 flex items-center justify-center rounded-full transition-colors {link.active ? 'bg-primary text-white font-bold' : link.url ? 'hover:bg-surface-container-high text-on-surface-variant' : 'text-outline opacity-50 cursor-not-allowed'}"
                                >
                                    {@html link.label.includes('Previous') ? '<span class="material-symbols-outlined text-sm">arrow_back_ios_new</span>' : link.label.includes('Next') ? '<span class="material-symbols-outlined text-sm">arrow_forward_ios</span>' : link.label}
                                </Link>
                            {/each}
                        </div>
                    </div>
                {/if}
            {/if}
        </section>
    </div>
</main>

<style>
    .masonry-grid {
        align-items: start;
    }
</style>
