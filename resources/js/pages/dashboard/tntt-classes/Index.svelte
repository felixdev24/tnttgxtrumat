<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';

    interface TnttClass {
        id: number;
        name: string;
        branch: string;
        users_count?: number;
    }

    let { classes, filters }: { classes: any; filters: any } = $props();

    let search = $state(filters.search || '');
    let selectedBranch = $state(filters.branch || '');

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);

    const branches = ['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp', 'Trợ Úy', 'Huynh Trưởng'];

    const form = useForm({
        id: null as number | null,
        name: '',
        branch: '',
    });

    function applyFilters() {
        router.get('/dashboard/tntt-classes', { search, branch: selectedBranch }, { preserveState: true });
    }

    let filterTimeout: any;
    function handleSearch() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(applyFilters, 300);
    }

    function openAddModal() {
        isEditing = false;
        form.reset();
        form.id = null;
        showModal = true;
    }

    function openEditModal(c: TnttClass) {
        isEditing = true;
        form.reset();
        form.id = c.id;
        form.name = c.name;
        form.branch = c.branch;
        showModal = true;
    }

    function submitForm() {
        if (isEditing && form.id) {
            form.put(`/dashboard/tntt-classes/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/tntt-classes', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteClass(c: TnttClass) {
        if (confirm(`Bạn có chắc muốn xóa lớp: "${c.name}"? Hành động này không thể hoàn tác.`)) {
            router.delete(`/dashboard/tntt-classes/${c.id}`, {
                preserveScroll: true,
            });
        }
    }
</script>

<svelte:head>
    <title>Quản Lý Lớp / Ngành - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Quản Lý Lớp / Ngành</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Danh sách các lớp giáo lý và ngành tương ứng.</p>
                </div>
                <button
                    onclick={() => openAddModal()}
                    class="duolingo-shadow-primary bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined">add_circle</span>
                    Thêm Lớp
                </button>
            </div>

            {#if page.props.flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900/40 animate-fade-in shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{page.props.flash.success}</p>
                </div>
            {/if}

            {#if page.props.flash?.error}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-error-container/30 text-error rounded-xl border border-error/40 animate-fade-in shadow-sm">
                    <span class="material-symbols-outlined text-error">error</span>
                    <p class="text-sm font-label-bold">{page.props.flash.error}</p>
                </div>
            {/if}

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 w-full relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant">search</span>
                    <input 
                        type="text" 
                        bind:value={search}
                        oninput={handleSearch}
                        placeholder="Tìm theo tên lớp..." 
                        class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                    >
                </div>
                <div class="w-full md:w-64">
                    <select 
                        bind:value={selectedBranch}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả ngành</option>
                        {#each branches as branch}
                            <option value={branch}>{branch}</option>
                        {/each}
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl shadow-md overflow-hidden border border-outline-variant/10">
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="bg-surface-container-high/50 text-on-surface-variant text-sm font-label-bold">
                            <th class="p-4 border-b border-outline-variant/10">Tên Lớp</th>
                            <th class="p-4 border-b border-outline-variant/10">Ngành</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">Số lượng đoàn sinh</th>
                            <th class="p-4 border-b border-outline-variant/10 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each classes.data as c (c.id)}
                            <tr class="hover:bg-surface-container/30 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-on-surface text-base">{c.name}</div>
                                </td>
                                <td class="p-4">
                                    <span class="px-3 py-1 bg-surface-container rounded-full text-primary font-label-bold text-xs">
                                        {c.branch}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-on-surface-variant">{c.users_count || 0}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            onclick={() => openEditModal(c)}
                                            class="p-2 hover:bg-surface-variant rounded-lg text-primary transition-all"
                                            title="Sửa"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">edit</span>
                                        </button>
                                        <button 
                                            onclick={() => deleteClass(c)}
                                            class="p-2 hover:bg-error-container rounded-lg text-error transition-all"
                                            title="Xóa"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="4" class="p-8 text-center text-on-surface-variant">
                                    Không có lớp nào. Hãy tạo mới lớp học đầu tiên!
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            {#if classes.links && classes.links.length > 3}
                <div class="mt-6 flex justify-center gap-1">
                    {#each classes.links as link}
                        {#if link.url}
                            <Link 
                                href={link.url} 
                                class="px-3 py-1 rounded-md text-sm {link.active ? 'bg-primary text-on-primary font-bold' : 'bg-surface-container hover:bg-surface-variant'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="px-3 py-1 rounded-md text-sm text-outline opacity-50">
                                {@html link.label}
                            </span>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</DashboardLayout>

{#if showModal}
    <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-lg text-headline-sm text-primary">
                    {isEditing ? 'Sửa thông tin Lớp' : 'Thêm Lớp Mới'}
                </h2>
                <button onclick={() => showModal = false} class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined block">close</span>
                </button>
            </div>
            
            <div class="p-6">
                <form onsubmit={(e) => { e.preventDefault(); submitForm(); }} class="space-y-4">
                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Tên Lớp (VD: Khai Tâm 1A) <span class="text-error">*</span></label>
                        <input type="text" bind:value={form.name} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        {#if form.errors.name}<p class="text-xs text-error mt-1">{form.errors.name}</p>{/if}
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Thuộc Ngành <span class="text-error">*</span></label>
                        <select bind:value={form.branch} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="" disabled>Chọn ngành...</option>
                            {#each branches as branch}
                                <option value={branch}>{branch}</option>
                            {/each}
                        </select>
                        {#if form.errors.branch}<p class="text-xs text-error mt-1">{form.errors.branch}</p>{/if}
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 border-t border-outline-variant/10 bg-surface-container-lowest flex justify-end gap-3">
                <button onclick={() => showModal = false} class="px-6 py-2.5 rounded-xl font-label-bold text-on-surface hover:bg-surface-variant transition-colors">
                    Hủy
                </button>
                <button 
                    onclick={submitForm}
                    disabled={form.processing}
                    class="px-6 py-2.5 rounded-xl font-label-bold bg-primary text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-sm disabled:opacity-50"
                >
                    {isEditing ? 'Lưu thay đổi' : 'Tạo mới'}
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .animate-fade-in {
        animation: fadeIn 0.2s ease-out forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
