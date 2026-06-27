<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import QRCode from 'qrcode/lib/browser.js';

    interface DoanSinh {
        id: number;
        name: string;
        username: string;
        phone: string | null;
        parent_phone: string | null;
        grade_level: string | null;
        branch: string | null;
        dob: string | null;
        qr_token: string | null;
    }

    let { doanSinhs, stats, filters }: { doanSinhs: any; stats: any; filters: any } = $props();

    let search = $state(filters.search || '');
    let selectedGrade = $state(filters.grade_level || '');

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);
    let showQrModal = $state(false);
    let currentQr = $state<{svg: string, name: string, token: string, png?: string} | null>(null);

    const gradeLevels = [
        'Khai Tâm 1', 'Khai Tâm 2', 
        'Rước Lễ 1', 'Rước Lễ 2', 
        'Thêm Sức 1', 'Thêm Sức 2', 
        'Bao Đồng 1', 'Bao Đồng 2'
    ];

    const branches = ['Ấu', 'Thiếu', 'Nghĩa', 'Hiệp'];

    const form = useForm({
        id: null as number | null,
        name: '',
        username: '',
        grade_level: '',
        branch: '',
        dob: '',
        phone: '',
        parent_phone: '',
        address: '',
    });

    function applyFilters() {
        router.get('/dashboard/doan-sinh', { search, grade_level: selectedGrade }, { preserveState: true });
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

    function openEditModal(ds: DoanSinh) {
        isEditing = true;
        form.reset();
        form.id = ds.id;
        form.name = ds.name;
        form.username = ds.username;
        form.grade_level = ds.grade_level || '';
        form.branch = ds.branch || '';
        form.dob = ds.dob ? ds.dob.split('T')[0] : '';
        form.phone = ds.phone || '';
        form.parent_phone = ds.parent_phone || '';
        showModal = true;
    }

    function submitForm() {
        if (isEditing && form.id) {
            form.put(`/dashboard/doan-sinh/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/doan-sinh', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteDoanSinh(ds: DoanSinh) {
        if (confirm(`Bạn có chắc muốn xóa đoàn sinh: "${ds.name}"?`)) {
            router.delete(`/dashboard/doan-sinh/${ds.id}`, {
                preserveScroll: true,
            });
        }
    }

    async function viewQr(ds: DoanSinh) {
        try {
            const response = await fetch(`/dashboard/doan-sinh/${ds.id}/qr`);
            if (response.ok) {
                const data = await response.json();
                const pngDataUrl = await QRCode.toDataURL(data.token, {
                    width: 512,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#ffffff'
                    }
                });
                currentQr = { ...data, png: pngDataUrl };
                showQrModal = true;
            }
        } catch (error) {
            console.error("Failed to load QR code", error);
        }
    }

    function downloadQr() {
        if (!currentQr || !currentQr.png) return;

        const a = document.createElement('a');
        a.href = currentQr.png;
        a.download = `QR_${currentQr.name}.png`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>

<svelte:head>
    <title>Quản Lý Đoàn Sinh - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Quản Lý Đoàn Sinh</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Danh sách, thông tin và mã QR của các đoàn sinh.</p>
                </div>
                <button
                    onclick={() => openAddModal()}
                    class="duolingo-shadow-primary bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined">person_add</span>
                    Thêm Đoàn Sinh
                </button>
            </div>

            {#if page.props.flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900/40 animate-fade-in shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{page.props.flash.success}</p>
                </div>
            {/if}

            <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-gutter">
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-primary shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-primary uppercase tracking-wide">Tổng Đoàn Sinh</span>
                            <span class="material-symbols-outlined text-primary opacity-80">groups</span>
                        </div>
                        <p class="text-[32px] font-display-lg text-on-surface leading-tight">{stats.total}</p>
                    </div>
                </div>
                <!-- Additional stat cards could go here -->
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter flex flex-col md:flex-row gap-4 items-center">
                <div class="flex-1 w-full relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant">search</span>
                    <input 
                        type="text" 
                        bind:value={search}
                        oninput={handleSearch}
                        placeholder="Tìm theo tên, username, sđt..." 
                        class="w-full pl-10 pr-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                    >
                </div>
                <div class="w-full md:w-64">
                    <select 
                        bind:value={selectedGrade}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả các lớp</option>
                        {#each gradeLevels as grade}
                            <option value={grade}>{grade} ({stats.by_grade[grade] || 0})</option>
                        {/each}
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl shadow-md overflow-x-auto border border-outline-variant/10">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-surface-container-high/50 text-on-surface-variant text-sm font-label-bold">
                            <th class="p-4 border-b border-outline-variant/10">Họ & Tên</th>
                            <th class="p-4 border-b border-outline-variant/10">Lớp / Ngành</th>
                            <th class="p-4 border-b border-outline-variant/10">SĐT</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">QR Code</th>
                            <th class="p-4 border-b border-outline-variant/10 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each doanSinhs.data as ds (ds.id)}
                            <tr class="hover:bg-surface-container/30 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-on-surface">{ds.name}</div>
                                    <div class="text-xs text-outline">@{ds.username}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-primary">{ds.grade_level || 'Chưa xếp lớp'}</div>
                                    <div class="text-xs text-outline">{ds.branch || ''}</div>
                                </td>
                                <td class="p-4 text-on-surface-variant">
                                    {#if ds.phone}
                                        <div>📞 {ds.phone}</div>
                                    {/if}
                                    {#if ds.parent_phone}
                                        <div class="text-xs text-outline mt-0.5">Phụ huynh: {ds.parent_phone}</div>
                                    {/if}
                                </td>
                                <td class="p-4 text-center">
                                    <button 
                                        onclick={() => viewQr(ds)}
                                        class="p-2 bg-surface-container hover:bg-surface-variant rounded-lg text-secondary transition-all"
                                        title="Xem QR Code"
                                    >
                                        <span class="material-symbols-outlined block">qr_code_2</span>
                                    </button>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            onclick={() => openEditModal(ds)}
                                            class="p-2 hover:bg-surface-variant rounded-lg text-primary transition-all"
                                            title="Sửa"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">edit</span>
                                        </button>
                                        <button 
                                            onclick={() => deleteDoanSinh(ds)}
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
                                <td colspan="5" class="p-8 text-center text-on-surface-variant">
                                    Không tìm thấy đoàn sinh nào.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination could go here if needed based on doanSinhs.links -->
            {#if doanSinhs.links && doanSinhs.links.length > 3}
                <div class="mt-6 flex justify-center gap-1">
                    {#each doanSinhs.links as link}
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
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-lg text-headline-sm text-primary">
                    {isEditing ? 'Sửa thông tin Đoàn Sinh' : 'Thêm Đoàn Sinh Mới'}
                </h2>
                <button onclick={() => showModal = false} class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined block">close</span>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1">
                <form onsubmit={(e) => { e.preventDefault(); submitForm(); }} class="space-y-4">
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Lớp Giáo Lý <span class="text-error">*</span></label>
                            <select bind:value={form.grade_level} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled>Chọn lớp...</option>
                                {#each gradeLevels as grade}
                                    <option value={grade}>{grade}</option>
                                {/each}
                            </select>
                            {#if form.errors.grade_level}<p class="text-xs text-error mt-1">{form.errors.grade_level}</p>{/if}
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngành <span class="text-error">*</span></label>
                            <select bind:value={form.branch} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled>Chọn ngành...</option>
                                {#each branches as branch}
                                    <option value={branch}>{branch}</option>
                                {/each}
                            </select>
                            {#if form.errors.branch}<p class="text-xs text-error mt-1">{form.errors.branch}</p>{/if}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">SĐT Cá Nhân</label>
                            <input type="text" bind:value={form.phone} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">SĐT Phụ Huynh</label>
                            <input type="text" bind:value={form.parent_phone} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngày sinh</label>
                        <input type="date" bind:value={form.dob} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
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

{#if showQrModal && currentQr}
    <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-sm overflow-hidden flex flex-col p-8 text-center items-center">
            <h2 class="font-title-lg text-headline-sm text-primary mb-1">QR Code Điểm Danh</h2>
            <p class="text-on-surface-variant font-label-bold mb-5">{currentQr.name}</p>
            
            <div class="bg-white p-4 rounded-xl shadow-inner mb-4 w-52 h-52 flex items-center justify-center qr-container border border-outline-variant/20">
                <img src={currentQr.png} alt="QR Code" class="w-full h-full object-contain" />
            </div>

            <p class="text-xs text-on-surface-variant mb-5 bg-surface-container px-3 py-2 rounded-lg flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[14px] text-primary">info</span>
                Tải về dạng <strong class="text-primary">PNG</strong> để quét bằng điện thoại hoặc máy quét QR
            </p>
            
            <div class="flex gap-3 w-full">
                <button onclick={() => showQrModal = false} class="flex-1 py-3 rounded-xl font-label-bold bg-surface-container hover:bg-surface-variant transition-colors">
                    Đóng
                </button>
                <button onclick={downloadQr} class="flex-1 py-3 rounded-xl font-label-bold bg-primary text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Tải PNG
                </button>
            </div>
        </div>
    </div>
{/if}


<style>
    :global(.qr-container svg) {
        width: 100%;
        height: 100%;
    }
</style>
