<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';

    interface HuynhTruong {
        id: number;
        name: string;
        username: string;
        email: string | null;
        phone: string | null;
        branch: string | null;
        rank: string | null;
        dob: string | null;
        address: string | null;
        years_of_activity: number | null;
    }

    let {
        huynhTruongs,
        stats,
        branches: branchList,
        filters,
    }: {
        huynhTruongs: any;
        stats: any;
        branches: string[];
        filters: any;
    } = $props();

    let search = $state(filters.search || '');
    let selectedBranch = $state(filters.branch || '');
    let showModal = $state(false);
    let isEditing = $state(false);
    let showPasswordResetConfirm = $state<number | null>(null);

    const allBranches = ['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp'];
    const ranks = [
        'Huynh Trưởng Cấp I',
        'Huynh Trưởng Cấp II',
        'Huynh Trưởng Cấp III',
        'Dự Trưởng',
        'Trợ Tá',
    ];

    const form = useForm({
        id: null as number | null,
        name: '',
        username: '',
        email: '',
        branch: '',
        rank: '',
        dob: '',
        phone: '',
        address: '',
        years_of_activity: '' as string | number,
    });

    let filterTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get('/dashboard/huynh-truong', { search, branch: selectedBranch }, { preserveState: true });
    }

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

    function openEditModal(ht: HuynhTruong) {
        isEditing = true;
        form.reset();
        form.id = ht.id;
        form.name = ht.name;
        form.username = ht.username;
        form.email = ht.email || '';
        form.branch = ht.branch || '';
        form.rank = ht.rank || '';
        form.dob = ht.dob ? ht.dob.split('T')[0] : '';
        form.phone = ht.phone || '';
        form.address = ht.address || '';
        form.years_of_activity = ht.years_of_activity ?? '';
        showModal = true;
    }

    function submitForm() {
        if (isEditing && form.id) {
            form.put(`/dashboard/huynh-truong/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/huynh-truong', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteHuynhTruong(ht: HuynhTruong) {
        if (confirm(`Bạn có chắc muốn xóa huynh trưởng: "${ht.name}"?\nTài khoản này sẽ bị xóa vĩnh viễn.`)) {
            router.delete(`/dashboard/huynh-truong/${ht.id}`, { preserveScroll: true });
        }
    }

    function resetPassword(ht: HuynhTruong) {
        if (confirm(`Đặt lại mật khẩu của "${ht.name}" về "password"?`)) {
            router.post(`/dashboard/huynh-truong/${ht.id}/reset-password`, {}, { preserveScroll: true });
        }
    }
</script>

<svelte:head>
    <title>Quản Lý Huynh Trưởng - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Quản Lý Huynh Trưởng</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Danh sách và thông tin các huynh trưởng trong xứ đoàn.</p>
                </div>
                <button
                    onclick={() => openAddModal()}
                    class="duolingo-shadow-primary bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined">person_add</span>
                    Thêm Huynh Trưởng
                </button>
            </div>

            <!-- Flash Message -->
            {#if (page.props as any).flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{(page.props as any).flash.success}</p>
                </div>
            {/if}

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter mb-gutter">
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-primary shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-primary uppercase tracking-wide">Tổng số</span>
                        <span class="material-symbols-outlined text-primary opacity-80">supervisor_account</span>
                    </div>
                    <p class="text-[32px] font-display-lg text-on-surface leading-tight">{stats.total}</p>
                    <p class="text-xs text-on-surface-variant mt-1">Huynh trưởng</p>
                </div>
                {#each allBranches as branch}
                    <div class="glass-card p-5 rounded-2xl border-l-4 border-l-secondary shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-secondary uppercase tracking-wide">Ngành {branch}</span>
                            <span class="material-symbols-outlined text-secondary opacity-80">group</span>
                        </div>
                        <p class="text-[28px] font-display-lg text-on-surface leading-tight">{stats.by_branch[branch] || 0}</p>
                        <p class="text-xs text-on-surface-variant mt-1">Huynh trưởng</p>
                    </div>
                {/each}
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 w-full relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant">search</span>
                    <input
                        type="text"
                        bind:value={search}
                        oninput={handleSearch}
                        placeholder="Tìm theo tên, username, email, sđt..."
                        class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                    />
                </div>
                <div class="w-full md:w-52">
                    <select
                        bind:value={selectedBranch}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả các ngành</option>
                        {#each allBranches as branch}
                            <option value={branch}>Ngành {branch} ({stats.by_branch[branch] || 0})</option>
                        {/each}
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl shadow-md overflow-x-auto border border-outline-variant/10">
                <table class="w-full text-left border-collapse min-w-[750px]">
                    <thead>
                        <tr class="bg-surface-container-high/50 text-on-surface-variant text-sm font-label-bold">
                            <th class="p-4 border-b border-outline-variant/10">Họ & Tên</th>
                            <th class="p-4 border-b border-outline-variant/10">Ngành / Cấp bậc</th>
                            <th class="p-4 border-b border-outline-variant/10">Liên hệ</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">Năm HĐ</th>
                            <th class="p-4 border-b border-outline-variant/10 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each huynhTruongs.data as ht (ht.id)}
                            <tr class="hover:bg-surface-container/30 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm flex-shrink-0">
                                            {ht.name.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <div class="font-bold text-on-surface">{ht.name}</div>
                                            <div class="text-xs text-outline">@{ht.username}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    {#if ht.branch}
                                        <span class="inline-block px-2 py-0.5 bg-secondary-container text-on-secondary-container rounded-full text-xs font-bold mb-1">
                                            Ngành {ht.branch}
                                        </span>
                                    {/if}
                                    {#if ht.rank}
                                        <div class="text-xs text-on-surface-variant">{ht.rank}</div>
                                    {/if}
                                </td>
                                <td class="p-4 text-on-surface-variant">
                                    {#if ht.phone}
                                        <div class="flex items-center gap-1 text-xs"><span class="material-symbols-outlined text-[14px]">phone</span>{ht.phone}</div>
                                    {/if}
                                    {#if ht.email}
                                        <div class="flex items-center gap-1 text-xs mt-0.5"><span class="material-symbols-outlined text-[14px]">email</span>{ht.email}</div>
                                    {/if}
                                </td>
                                <td class="p-4 text-center">
                                    {#if ht.years_of_activity != null}
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-container/40 text-on-primary-container rounded-full text-xs font-bold">
                                            {ht.years_of_activity} năm
                                        </span>
                                    {:else}
                                        <span class="text-outline text-xs">—</span>
                                    {/if}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            onclick={() => openEditModal(ht)}
                                            class="p-2 hover:bg-surface-variant rounded-lg text-primary transition-all"
                                            title="Sửa thông tin"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">edit</span>
                                        </button>
                                        <button
                                            onclick={() => resetPassword(ht)}
                                            class="p-2 hover:bg-surface-variant rounded-lg text-secondary transition-all"
                                            title="Đặt lại mật khẩu"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">lock_reset</span>
                                        </button>
                                        <button
                                            onclick={() => deleteHuynhTruong(ht)}
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
                                <td colspan="5" class="p-12 text-center text-on-surface-variant">
                                    <span class="material-symbols-outlined text-4xl opacity-30 mb-2 block">supervisor_account</span>
                                    Không tìm thấy huynh trưởng nào.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {#if huynhTruongs.links && huynhTruongs.links.length > 3}
                <div class="mt-6 flex justify-center gap-1">
                    {#each huynhTruongs.links as link}
                        {#if link.url}
                            <Link
                                href={link.url}
                                class="px-3 py-1 rounded-md text-sm {link.active ? 'bg-primary text-on-primary font-bold' : 'bg-surface-container hover:bg-surface-variant'}"
                            >
                                {@html link.label}
                            </Link>
                        {:else}
                            <span class="px-3 py-1 rounded-md text-sm text-outline opacity-50">{@html link.label}</span>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</DashboardLayout>

<!-- Add/Edit Modal -->
{#if showModal}
    <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-lg text-headline-sm text-primary">
                    {isEditing ? 'Sửa thông tin Huynh Trưởng' : 'Thêm Huynh Trưởng Mới'}
                </h2>
                <button
                    onclick={() => (showModal = false)}
                    class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors"
                >
                    <span class="material-symbols-outlined block">close</span>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <form
                    onsubmit={(e) => {
                        e.preventDefault();
                        submitForm();
                    }}
                    class="space-y-4"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Họ & Tên <span class="text-error">*</span></label>
                            <input type="text" bind:value={form.name} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            {#if form.errors.name}<p class="text-xs text-error mt-1">{form.errors.name}</p>{/if}
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Username <span class="text-error">*</span></label>
                            <input type="text" bind:value={form.username} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            {#if form.errors.username}<p class="text-xs text-error mt-1">{form.errors.username}</p>{/if}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Email</label>
                        <input type="email" bind:value={form.email} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="email@example.com" />
                        {#if form.errors.email}<p class="text-xs text-error mt-1">{form.errors.email}</p>{/if}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngành <span class="text-error">*</span></label>
                            <select bind:value={form.branch} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled>Chọn ngành...</option>
                                {#each allBranches as branch}
                                    <option value={branch}>Ngành {branch}</option>
                                {/each}
                            </select>
                            {#if form.errors.branch}<p class="text-xs text-error mt-1">{form.errors.branch}</p>{/if}
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Cấp bậc</label>
                            <select bind:value={form.rank} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="">-- Chưa xác định --</option>
                                {#each ranks as rank}
                                    <option value={rank}>{rank}</option>
                                {/each}
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">SĐT</label>
                            <input type="text" bind:value={form.phone} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Số năm hoạt động</label>
                            <input type="number" bind:value={form.years_of_activity} min="0" class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngày sinh</label>
                            <input type="date" bind:value={form.dob} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Địa chỉ</label>
                            <input type="text" bind:value={form.address} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                    </div>

                    {#if !isEditing}
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-[18px]">info</span>
                            Mật khẩu mặc định sẽ là <strong>"password"</strong>. Huynh trưởng cần đổi mật khẩu sau khi đăng nhập.
                        </div>
                    {/if}
                </form>
            </div>

            <div class="px-6 py-4 border-t border-outline-variant/10 bg-surface-container-lowest flex justify-end gap-3">
                <button onclick={() => (showModal = false)} class="px-6 py-2.5 rounded-xl font-label-bold text-on-surface hover:bg-surface-variant transition-colors">
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
