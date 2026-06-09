<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm } from '@inertiajs/svelte';

    interface PostCategory {
        id: number;
        name: string;
        slug: string;
        posts_count: number;
    }

    let {
        posts,
        categories = [],
        tab = 'posts',
    }: {
        posts: {
            data: Array<{
                id: number;
                title: string;
                category: string | null;
                status: string;
                cover_image: string | null;
                created_at: string;
                user?: { name: string };
            }>;
            total: number;
        };
        categories?: PostCategory[];
        tab?: 'posts' | 'categories';
    } = $props();

    const categoryForm = useForm({ name: '' });
    const editForm = useForm({ name: '' });

    let editingId = $state<number | null>(null);

    function switchTab(next: 'posts' | 'categories'): void {
        router.get(
            '/dashboard/posts',
            { tab: next },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function submitCategory(): void {
        categoryForm.post('/dashboard/posts/categories', {
            preserveScroll: true,
            onSuccess: () => categoryForm.reset(),
        });
    }

    function startEdit(category: PostCategory): void {
        editingId = category.id;
        editForm.name = category.name;
    }

    function cancelEdit(): void {
        editingId = null;
        editForm.reset();
    }

    function submitEdit(categoryId: number): void {
        editForm.put(`/dashboard/posts/categories/${categoryId}`, {
            preserveScroll: true,
            onSuccess: () => {
                editingId = null;
                editForm.reset();
            },
        });
    }

    function deleteCategory(category: PostCategory): void {
        if (category.posts_count > 0) {
            return;
        }

        if (!confirm(`Xóa danh mục "${category.name}"?`)) {
            return;
        }

        router.delete(`/dashboard/posts/categories/${category.id}`, { preserveScroll: true });
    }
</script>

<svelte:head>
    <title>Quản lý Truyền thông - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-gutter mb-stack-lg">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface">Quản lý Truyền thông</h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant">Phát triển nội dung số cho Xứ Đoàn.</p>
                </div>
                {#if tab === 'posts'}
                    <Link
                        href="/dashboard/posts/create"
                        class="flex items-center justify-center gap-2 bg-tertiary text-on-tertiary px-8 py-3 rounded-xl font-title-md text-[18px] duolingo-shadow-primary active:translate-y-1 active:shadow-none transition-all"
                    >
                        <span class="material-symbols-outlined">add_circle</span>
                        Tạo bài viết mới
                    </Link>
                {/if}
            </div>

            <div class="flex gap-2 p-1 bg-surface-container-high w-fit rounded-full mb-stack-lg">
                <button
                    type="button"
                    class="px-8 py-2 rounded-full font-label-bold text-label-bold transition-all {tab === 'posts'
                        ? 'bg-surface-container-lowest text-primary shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-variant'}"
                    onclick={() => switchTab('posts')}
                >
                    Tất cả bài viết
                </button>
                <button
                    type="button"
                    class="px-8 py-2 rounded-full font-label-bold text-label-bold transition-all {tab === 'categories'
                        ? 'bg-surface-container-lowest text-primary shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-variant'}"
                    onclick={() => switchTab('categories')}
                >
                    Danh mục
                </button>
            </div>

            {#if tab === 'posts'}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-gutter">
                    <div class="xl:col-span-12 flex flex-col md:flex-row gap-4 glass-card p-4 rounded-lg">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                            <input
                                class="w-full pl-12 pr-4 py-3 bg-surface-container-low border-none rounded-xl focus:ring-2 focus:ring-primary-container transition-all"
                                placeholder="Tìm kiếm bài viết..."
                                type="text"
                            />
                        </div>
                        <div class="flex gap-4">
                            <select
                                class="bg-surface-container-low border-none rounded-xl px-6 py-3 font-label-bold text-on-surface-variant focus:ring-2 focus:ring-primary-container min-w-[180px]"
                            >
                                <option>Mọi danh mục</option>
                                {#each categories as category (category.id)}
                                    <option>{category.name}</option>
                                {/each}
                            </select>
                            <button
                                type="button"
                                class="bg-surface-variant text-on-surface-variant p-3 rounded-xl hover:bg-outline-variant transition-all"
                            >
                                <span class="material-symbols-outlined">filter_list</span>
                            </button>
                        </div>
                    </div>

                    <div class="xl:col-span-8">
                        <div class="glass-card rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-surface-container-high">
                                        <tr>
                                            <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant">Tiêu đề</th>
                                            <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant">Danh mục</th>
                                            <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant">Trạng thái</th>
                                            <th class="px-6 py-4 font-label-bold text-label-bold text-on-surface-variant text-right">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface-variant">
                                        {#each posts.data as post (post.id)}
                                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                                <td class="px-6 py-5">
                                                    <div class="flex gap-3 items-center">
                                                        <div class="w-12 h-12 rounded-lg bg-surface-container-high flex-shrink-0">
                                                            {#if post.cover_image}
                                                                <img alt="" class="w-full h-full object-cover rounded-lg" src={post.cover_image} />
                                                            {:else}
                                                                <div class="w-full h-full flex items-center justify-center text-outline">
                                                                    <span class="material-symbols-outlined">image</span>
                                                                </div>
                                                            {/if}
                                                        </div>
                                                        <div>
                                                            <p class="font-label-bold text-on-surface line-clamp-1">{post.title}</p>
                                                            <p class="text-[12px] text-outline">
                                                                Bởi: {post.user?.name || 'Ẩn danh'} • {new Date(post.created_at).toLocaleDateString('vi-VN')}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <span class="px-3 py-1 bg-primary-container text-on-primary-container rounded-full text-[12px] font-bold">
                                                        {post.category || 'Không phân loại'}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-5">
                                                    <div class="flex items-center gap-1.5 {post.status === 'published' ? 'text-success-600' : 'text-on-surface-variant'}">
                                                        <div class="w-2 h-2 rounded-full {post.status === 'published' ? 'bg-green-500' : 'bg-outline'}"></div>
                                                        <span class="text-[13px] font-bold {post.status === 'published' ? 'text-green-700' : ''}">
                                                            {post.status === 'published' ? 'Đã đăng' : 'Bản nháp'}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-5 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" class="p-2 hover:bg-surface-variant rounded-lg transition-all text-secondary">
                                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                                        </button>
                                                        <button type="button" class="p-2 hover:bg-error-container rounded-lg transition-all text-error">
                                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        {:else}
                                            <tr>
                                                <td colspan="4" class="px-6 py-10 text-center text-on-surface-variant">
                                                    Chưa có bài viết nào. Hãy tạo bài viết đầu tiên!
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-4 space-y-gutter">
                        <div class="bg-primary text-on-primary p-6 rounded-lg shadow-lg relative overflow-hidden">
                            <div class="relative z-10">
                                <h3 class="font-title-md text-[18px] mb-4 opacity-90">Tổng quan tháng này</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white/10 p-3 rounded-xl backdrop-blur-md">
                                        <p class="text-[12px] font-bold opacity-80 uppercase tracking-wider">Bài viết</p>
                                        <p class="text-headline-lg font-display-lg mt-1">{posts.total}</p>
                                    </div>
                                    <div class="bg-white/10 p-3 rounded-xl backdrop-blur-md">
                                        <p class="text-[12px] font-bold opacity-80 uppercase tracking-wider">Danh mục</p>
                                        <p class="text-headline-lg font-display-lg mt-1">{categories.length}</p>
                                    </div>
                                </div>
                            </div>
                            <span class="material-symbols-outlined absolute -bottom-4 -right-4 text-[120px] opacity-10 rotate-12">trending_up</span>
                        </div>
                    </div>
                </div>
            {:else}
                <div class="grid grid-cols-1 xl:grid-cols-12 gap-gutter">
                    <div class="xl:col-span-5">
                        <div class="glass-card rounded-lg p-6">
                            <h2 class="font-title-md text-primary mb-1 flex items-center gap-2">
                                <span class="material-symbols-outlined">add_circle</span>
                                Thêm danh mục
                            </h2>
                            <p class="text-sm text-on-surface-variant mb-6">Tạo danh mục mới để phân loại bài viết truyền thông.</p>
                            <form
                                class="flex flex-col sm:flex-row gap-3"
                                onsubmit={(event) => {
                                    event.preventDefault();
                                    submitCategory();
                                }}
                            >
                                <input
                                    bind:value={categoryForm.name}
                                    class="flex-1 bg-surface-container-low border-none rounded-xl px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary-container outline-none"
                                    placeholder="Tên danh mục mới..."
                                    type="text"
                                />
                                <button
                                    type="submit"
                                    class="bg-tertiary text-on-tertiary px-6 py-3 rounded-xl font-label-bold whitespace-nowrap disabled:opacity-60"
                                    disabled={categoryForm.processing}
                                >
                                    Thêm
                                </button>
                            </form>
                            {#if categoryForm.errors.name}
                                <p class="mt-2 text-sm text-error">{categoryForm.errors.name}</p>
                            {/if}
                        </div>
                    </div>

                    <div class="xl:col-span-7">
                        <div class="glass-card rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-surface-variant bg-surface-container-high">
                                <h2 class="font-title-md text-on-surface">Danh sách danh mục</h2>
                                <p class="text-sm text-on-surface-variant">{categories.length} danh mục đang hoạt động</p>
                            </div>
                            <div class="divide-y divide-surface-variant">
                                {#each categories as category (category.id)}
                                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                                        {#if editingId === category.id}
                                            <form
                                                class="flex flex-1 flex-col sm:flex-row gap-3"
                                                onsubmit={(event) => {
                                                    event.preventDefault();
                                                    submitEdit(category.id);
                                                }}
                                            >
                                                <input
                                                    bind:value={editForm.name}
                                                    class="flex-1 bg-surface-container-low border-none rounded-xl px-4 py-2.5 text-on-surface focus:ring-2 focus:ring-primary-container outline-none"
                                                    type="text"
                                                />
                                                <div class="flex gap-2">
                                                    <button
                                                        type="submit"
                                                        class="bg-primary text-on-primary px-4 py-2.5 rounded-xl font-label-bold text-sm"
                                                        disabled={editForm.processing}
                                                    >
                                                        Lưu
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="px-4 py-2.5 rounded-xl font-label-bold text-sm text-on-surface-variant hover:bg-surface-variant"
                                                        onclick={cancelEdit}
                                                    >
                                                        Hủy
                                                    </button>
                                                </div>
                                            </form>
                                            {#if editForm.errors.name}
                                                <p class="text-sm text-error">{editForm.errors.name}</p>
                                            {/if}
                                        {:else}
                                            <div class="flex-1 min-w-0">
                                                <p class="font-label-bold text-on-surface">{category.name}</p>
                                                <p class="text-sm text-on-surface-variant">
                                                    {category.posts_count} bài viết • /{category.slug}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="px-3 py-1 bg-primary-container/40 text-on-primary-container rounded-full text-xs font-bold">
                                                    {category.posts_count} bài
                                                </span>
                                                <button
                                                    type="button"
                                                    class="p-2 hover:bg-surface-variant rounded-lg transition-all text-secondary"
                                                    aria-label="Sửa danh mục"
                                                    onclick={() => startEdit(category)}
                                                >
                                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                                </button>
                                                <button
                                                    type="button"
                                                    class="p-2 rounded-lg transition-all {category.posts_count > 0
                                                        ? 'opacity-40 cursor-not-allowed text-outline'
                                                        : 'hover:bg-error-container text-error'}"
                                                    aria-label="Xóa danh mục"
                                                    disabled={category.posts_count > 0}
                                                    onclick={() => deleteCategory(category)}
                                                >
                                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                                </button>
                                            </div>
                                        {/if}
                                    </div>
                                {:else}
                                    <div class="px-6 py-12 text-center text-on-surface-variant">
                                        Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</DashboardLayout>
