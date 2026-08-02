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

    let { doanSinhs, stats, filters, classes }: { doanSinhs: any; stats: any; filters: any; classes: any[] } = $props();

    let search = $state(filters.search || '');
    let selectedClass = $state(filters.tntt_class_id || '');

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);
    let showQrModal = $state(false);
    let currentQr = $state<{name: string, username: string, token: string, branch: string, tntt_class_name: string, parent_phone?: string, academic_year?: string, png?: string} | null>(null);

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
        tntt_class_id: '',
        branch: '',
        dob: '',
        phone: '',
        parent_phone: '',
        address: '',
    });

    function applyFilters() {
        router.get('/dashboard/doan-sinh', { search, tntt_class_id: selectedClass }, { preserveState: true });
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
        form.tntt_class_id = ds.tntt_class_id ? String(ds.tntt_class_id) : '';
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

    function resetPasswordDoanSinh(ds: DoanSinh) {
        if (confirm(`Bạn có chắc muốn đặt lại mật khẩu về mặc định (password) cho: "${ds.name}"?`)) {
            router.post(`/dashboard/doan-sinh/${ds.id}/reset-password`, {}, {
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

    async function downloadCard() {
        const cardEl = document.getElementById('id-cards-wrapper');
        if (!cardEl) return;

        try {
            const { toPng } = await import('html-to-image');
            const dataUrl = await toPng(cardEl, {
                pixelRatio: 3,
                backgroundColor: '#ffffff',
            });
            const link = document.createElement('a');
            link.download = `The_TNTT_${currentQr?.name || 'DoanSinh'}.png`;
            link.href = dataUrl;
            link.click();
        } catch (error) {
            console.error('Failed to download card', error);
        }
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
                    <p class="text-on-surface-variant mt-1 text-sm">Danh sách, thông tin và thẻ giáo lý sinh.</p>
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
                        bind:value={selectedClass}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả các lớp</option>
                        {#each classes as cls}
                            <option value={cls.id}>{cls.name} ({stats.by_grade[cls.id] || 0})</option>
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
                            <th class="p-4 border-b border-outline-variant/10 text-center">Điểm</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">Thẻ</th>
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
                                    <div class="font-medium text-primary">{ds.tntt_class ? ds.tntt_class.name : 'Chưa xếp lớp'}</div>
                                    <div class="text-xs text-outline">{ds.tntt_class ? ds.tntt_class.branch : (ds.branch || '')}</div>
                                </td>
                                <td class="p-4 text-on-surface-variant">
                                    <div class="text-sm">{ds.phone || '-'}</div>
                                    {#if ds.parent_phone}
                                        <div class="text-xs text-outline mt-1" title="SĐT Phụ huynh">PH: {ds.parent_phone}</div>
                                    {/if}
                                </td>
                                <td class="p-4 text-center">
                                    <span class="font-bold text-primary px-2 py-1 bg-primary-container rounded-md">
                                        {ds.point_transactions_sum_points ?? 0}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button 
                                        onclick={() => viewQr(ds)}
                                        class="p-2 bg-surface-container hover:bg-surface-variant rounded-lg text-secondary transition-all"
                                        title="Xem Thẻ Thiếu Nhi"
                                    >
                                        <span class="material-symbols-outlined block">badge</span>
                                    </button>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {#if (page.props as any).auth?.is_super_admin}
                                            <button 
                                                onclick={() => resetPasswordDoanSinh(ds)}
                                                class="p-2 hover:bg-tertiary-container rounded-lg text-tertiary transition-all"
                                                title="Đặt lại mật khẩu"
                                            >
                                                <span class="material-symbols-outlined block text-[18px]">key</span>
                                            </button>
                                        {/if}
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
            
            <!-- Pagination -->
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
                            <select bind:value={form.tntt_class_id} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="" disabled>Chọn lớp...</option>
                                {#each classes as cls}
                                    <option value={String(cls.id)}>{cls.name}</option>
                                {/each}
                            </select>
                            {#if form.errors.tntt_class_id}<p class="text-xs text-error mt-1">{form.errors.tntt_class_id}</p>{/if}
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

<!-- Card Modal (Thẻ Thiếu Nhi) -->
{#if showQrModal && currentQr}
    <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col p-6 items-center">
            <h2 class="font-title-lg text-headline-sm text-[#004a99] mb-4">Thẻ Thiếu Nhi Thánh Thể</h2>

            <!-- ID Cards Wrapper -->
            <div id="id-cards-wrapper" class="flex flex-col md:flex-row gap-6 p-4 overflow-x-auto w-full items-center md:justify-center bg-white">
                <!-- Front Card -->
                <div class="id-card-container bg-white flex shrink-0" style="font-family: 'Inter', system-ui, sans-serif;">
                    <!-- Main Content -->
                    <main class="flex-grow p-4 flex flex-col h-full relative">
                        <div class="flex flex-col h-full">
                            <!-- QR Code Area -->
                            <section class="flex justify-center mb-3">
                                <div class="w-full aspect-[54/45] flex items-center justify-center rounded-xl overflow-hidden shadow-inner bg-gray-50 border border-gray-100">
                                    <img src={currentQr.png} alt="QR Code" class="w-3/4 h-3/4 object-contain" />
                                </div>
                            </section>
                            <!-- Member Info Fields -->
                            <section class="space-y-2 flex-grow">
                                <div class="space-y-0.5">
                                    <p class="card-label-text uppercase">Họ và Tên</p>
                                    <div class="card-info-field h-7 flex items-center">
                                        <span class="text-[11px] font-semibold text-slate-800">{currentQr.name}</span>
                                    </div>
                                </div>
                                <div class="space-y-0.5">
                                    <p class="card-label-text uppercase">Mã số TN</p>
                                    <div class="card-info-field h-7 flex items-center">
                                        <span class="text-[11px] font-semibold text-slate-800">{currentQr.username}</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="space-y-0.5">
                                        <p class="card-label-text uppercase">Ngành</p>
                                        <div class="card-info-field h-7 flex items-center">
                                            <span class="text-[11px] font-semibold text-slate-800">{currentQr.branch || currentQr.tntt_class_name}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-0.5">
                                        <p class="card-label-text uppercase">SĐT Phụ Huynh</p>
                                        <div class="card-info-field h-7 flex items-center">
                                            <span class="text-[11px] font-semibold text-slate-800">{currentQr.parent_phone || 'N/A'}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <!-- Footer -->
                            <footer class="mt-4 flex items-end justify-between">
                                <div class="flex-shrink-0">
                                    <h2 class="text-[14px] font-extrabold text-[#004a99] uppercase leading-tight tracking-tighter">Thiếu Nhi<br/>Thánh Thể</h2>
                                    <div class="mt-1 inline-block px-1.5 py-0.5 bg-[#004a99] text-white rounded text-[7px] font-bold tracking-wider uppercase">
                                        NIÊN KHÓA {currentQr.academic_year || '2024-2025'}
                                    </div>
                                </div>
                                <div class="w-12 h-12 relative flex items-center justify-center">
                                    <img alt="Logo TNTT" class="w-full h-full object-contain" src="/apple-touch-icon.png" />
                                </div>
                            </footer>
                        </div>
                    </main>
                    <!-- Sidebar -->
                    <aside class="card-sidebar flex items-center justify-center py-6">
                        <h1 class="card-vertical-text text-white text-[18px] font-extrabold whitespace-nowrap uppercase">
                            GIÁO XỨ TRÙ MẬT
                        </h1>
                    </aside>
                </div>

                <!-- Back Card -->
                <div class="id-card-container bg-white flex flex-col shrink-0 relative" style="font-family: 'Inter', system-ui, sans-serif;">
                    <!-- Top accent -->
                    <div class="h-8 w-full bg-[#004a99]"></div>
                    <div class="flex-grow p-4 flex flex-col">
                        <div class="text-center mb-3">
                            <h2 class="text-[#004a99] font-black text-lg uppercase tracking-tight">Thẻ Đoàn Sinh</h2>
                            <p class="text-[11px] font-bold text-gray-600 mt-1">{currentQr.name}</p>
                        </div>
                        
                        <div class="space-y-3 flex-grow">
                            <div class="bg-[#f0f5fa] p-3 rounded-lg border border-[#cce0ff]">
                                <p class="text-[9px] font-bold text-[#004a99] mb-1">TÀI KHOẢN HỆ THỐNG</p>
                                <p class="text-[11px] text-slate-700"><span class="font-bold w-16 inline-block">Mã số:</span> {currentQr.username}</p>
                                <p class="text-[11px] text-slate-700"><span class="font-bold w-16 inline-block">Mật khẩu:</span> password</p>
                            </div>
                            
                            <div>
                                <p class="text-[10px] font-bold text-[#004a99] mb-1.5 uppercase">Nội quy sử dụng thẻ:</p>
                                <ul class="text-[9px] text-gray-700 space-y-1.5 list-disc pl-3 leading-snug">
                                    <li>Đeo thẻ trong tất cả các buổi sinh hoạt, thánh lễ và học giáo lý.</li>
                                    <li>Bảo quản thẻ cẩn thận, không làm xước mã QR.</li>
                                    <li>Sử dụng thẻ để điểm danh và tích lũy điểm.</li>
                                    <li>Nếu mất thẻ, báo ngay cho Huynh Trưởng.</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mt-auto text-center border-t border-gray-100 pt-3 flex flex-col items-center justify-center">
                            <div class="w-8 h-8 opacity-20 mb-1">
                                <span class="material-symbols-outlined text-[32px]">qr_code_scanner</span>
                            </div>
                            <p class="text-[8.5px] text-gray-500 font-medium">Đăng nhập tại: https://tnttgxtrumat.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 w-full mt-5">
                <button onclick={() => showQrModal = false} class="flex-1 py-3 rounded-xl font-label-bold bg-surface-container hover:bg-surface-variant transition-colors">
                    Đóng
                </button>
                <button onclick={downloadCard} class="flex-1 py-3 rounded-xl font-label-bold bg-[#004a99] text-white hover:brightness-110 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Tải Thẻ PNG
                </button>
            </div>
        </div>
    </div>
{/if}

<style>
    .id-card-container {
        --scale: 4.8px;
        width: calc(72 * var(--scale));
        height: calc(112 * var(--scale));
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        border-radius: calc(3.5 * var(--scale));
    }

    .card-vertical-text {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        letter-spacing: 0.2em;
    }

    .card-sidebar {
        width: calc(13 * var(--scale));
        background-color: #004a99;
    }

    .card-label-text {
        font-size: 0.55rem;
        color: #6b7280;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .card-info-field {
        background-color: #f9fafb;
        border-bottom: 1.5px solid #004a99;
        border-radius: 2px 2px 0 0;
        padding: 0.2rem 0.4rem;
    }
</style>

