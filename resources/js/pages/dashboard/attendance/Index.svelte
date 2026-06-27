<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';

    interface AttendanceSession {
        id: number;
        title: string;
        session_date: string;
        grade_level: string;
        notes: string | null;
        status: 'upcoming' | 'in_progress' | 'completed';
        total_records: number;
        present_records: number;
    }

    let { sessions, stats, gradeLevels, filters }: { sessions: any; stats: any; gradeLevels: string[]; filters: any } = $props();

    let selectedGrade = $state(filters.grade_level || '');

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);

    const form = useForm({
        id: null as number | null,
        title: '',
        session_date: '',
        grade_level: '',
        notes: '',
        status: 'upcoming' as 'upcoming' | 'in_progress' | 'completed',
    });

    function applyFilters() {
        router.get('/dashboard/attendance', { grade_level: selectedGrade }, { preserveState: true });
    }

    function openAddModal() {
        isEditing = false;
        form.reset();
        form.id = null;
        form.session_date = new Date().toISOString().split('T')[0];
        showModal = true;
    }

    function openEditModal(session: AttendanceSession) {
        isEditing = true;
        form.reset();
        form.id = session.id;
        form.title = session.title;
        form.session_date = session.session_date.split('T')[0];
        form.grade_level = session.grade_level;
        form.notes = session.notes || '';
        form.status = session.status;
        showModal = true;
    }

    function submitForm() {
        if (isEditing && form.id) {
            form.put(`/dashboard/attendance/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/attendance', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteSession(session: AttendanceSession) {
        if (confirm(`Bạn có chắc muốn xóa phiên điểm danh: "${session.title}"? Dữ liệu điểm danh của phiên này sẽ bị xóa.`)) {
            router.delete(`/dashboard/attendance/${session.id}`, {
                preserveScroll: true,
            });
        }
    }

    const statusLabels = {
        upcoming: 'Sắp tới',
        in_progress: 'Đang diễn ra',
        completed: 'Hoàn tất',
    };

    const statusColors = {
        upcoming: 'bg-amber-100 text-amber-800 border-amber-200',
        in_progress: 'bg-blue-100 text-blue-800 border-blue-200',
        completed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    };

    function formatDate(dateStr: string) {
        return new Date(dateStr).toLocaleDateString('vi-VN');
    }
</script>

<svelte:head>
    <title>Điểm Danh - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Điểm Danh Đoàn Sinh</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Quản lý các phiên điểm danh và theo dõi chuyên cần.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/dashboard/attendance/stats" class="duolingo-shadow bg-surface-container hover:bg-surface-variant text-on-surface px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 transition-all shadow-sm">
                        <span class="material-symbols-outlined">bar_chart</span>
                        Thống kê
                    </Link>
                    <button
                        onclick={() => openAddModal()}
                        class="duolingo-shadow-primary bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                    >
                        <span class="material-symbols-outlined">add_circle</span>
                        Tạo phiên mới
                    </button>
                </div>
            </div>

            {#if page.props.flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{page.props.flash.success}</p>
                </div>
            {/if}

            <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-gutter">
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-primary shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-primary uppercase tracking-wide">Tổng Phiên Điểm Danh</span>
                            <span class="material-symbols-outlined text-primary opacity-80">event</span>
                        </div>
                        <p class="text-[32px] font-display-lg text-on-surface leading-tight">{stats.total_sessions}</p>
                    </div>
                </div>
                
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-emerald-500 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wide">Tỷ lệ có mặt (TB)</span>
                            <span class="material-symbols-outlined text-emerald-600 opacity-80">percent</span>
                        </div>
                        <p class="text-[32px] font-display-lg text-on-surface leading-tight">{stats.avg_attendance}%</p>
                    </div>
                </div>
                
                <div class="glass-card p-5 rounded-2xl border-l-4 border-l-amber-500 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">Sắp tới</span>
                            <span class="material-symbols-outlined text-amber-600 opacity-80">pending_actions</span>
                        </div>
                        <p class="text-[32px] font-display-lg text-on-surface leading-tight">{stats.upcoming_sessions}</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter flex items-center justify-between">
                <h2 class="font-title-md text-on-surface">Danh sách phiên</h2>
                <div class="w-full md:w-64">
                    <select 
                        bind:value={selectedGrade}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả các lớp</option>
                        {#each gradeLevels as grade}
                            <option value={grade}>{grade}</option>
                        {/each}
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="glass-card rounded-2xl shadow-md overflow-x-auto border border-outline-variant/10">
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-surface-container-high/50 text-on-surface-variant text-sm font-label-bold">
                            <th class="p-4 border-b border-outline-variant/10 w-16">Ngày</th>
                            <th class="p-4 border-b border-outline-variant/10">Tên Phiên & Lớp</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">Trạng Thái</th>
                            <th class="p-4 border-b border-outline-variant/10 text-center">Điểm Danh</th>
                            <th class="p-4 border-b border-outline-variant/10 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each sessions.data as session (session.id)}
                            <tr class="hover:bg-surface-container/30 transition-colors">
                                <td class="p-4">
                                    <div class="bg-surface-container text-center rounded-lg py-1 px-2 border border-outline-variant/20">
                                        <div class="text-xs text-outline font-bold uppercase">{new Date(session.session_date).toLocaleDateString('vi-VN', {weekday: 'short'})}</div>
                                        <div class="text-lg font-black text-on-surface">{new Date(session.session_date).getDate()}</div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <Link href={`/dashboard/attendance/${session.id}`} class="font-bold text-primary hover:underline text-[16px] block">
                                        {session.title}
                                    </Link>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-secondary-container text-on-secondary-container">
                                            Lớp: {session.grade_level}
                                        </span>
                                        {#if session.notes}
                                            <span class="text-xs text-outline-variant truncate max-w-xs">{session.notes}</span>
                                        {/if}
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border {statusColors[session.status]}">
                                        {statusLabels[session.status]}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    {#if session.total_records > 0}
                                        {@const rate = Math.round((session.present_records / session.total_records) * 100)}
                                        <div class="flex flex-col items-center">
                                            <span class="font-bold {rate >= 80 ? 'text-emerald-600' : rate >= 50 ? 'text-amber-600' : 'text-error'}">
                                                {session.present_records} / {session.total_records}
                                            </span>
                                            <div class="w-20 bg-surface-container-high rounded-full h-1.5 mt-1 overflow-hidden">
                                                <div class="h-full rounded-full {rate >= 80 ? 'bg-emerald-500' : rate >= 50 ? 'bg-amber-500' : 'bg-error'}" style="width: {rate}%"></div>
                                            </div>
                                        </div>
                                    {:else}
                                        <span class="text-outline-variant text-xs italic">Chưa có ds</span>
                                    {/if}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link 
                                            href={`/dashboard/attendance/${session.id}/scan`}
                                            class="p-2 hover:bg-primary-container rounded-lg text-primary transition-all flex items-center justify-center group"
                                            title="Quét QR"
                                        >
                                            <span class="material-symbols-outlined block text-[20px] group-hover:scale-110 transition-transform">qr_code_scanner</span>
                                        </Link>
                                        <button 
                                            onclick={() => openEditModal(session)}
                                            class="p-2 hover:bg-surface-variant rounded-lg text-on-surface-variant transition-all"
                                            title="Sửa"
                                        >
                                            <span class="material-symbols-outlined block text-[18px]">edit</span>
                                        </button>
                                        <button 
                                            onclick={() => deleteSession(session)}
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
                                    Không tìm thấy phiên điểm danh nào.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {#if sessions.links && sessions.links.length > 3}
                <div class="mt-6 flex justify-center gap-1">
                    {#each sessions.links as link}
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
        <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-lg text-headline-sm text-primary">
                    {isEditing ? 'Cập nhật phiên' : 'Tạo phiên điểm danh'}
                </h2>
                <button onclick={() => showModal = false} class="p-2 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined block">close</span>
                </button>
            </div>
            
            <div class="p-6">
                <form onsubmit={(e) => { e.preventDefault(); submitForm(); }} class="space-y-4">
                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Tên buổi sinh hoạt <span class="text-error">*</span></label>
                        <input type="text" bind:value={form.title} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        {#if form.errors.title}<p class="text-xs text-error mt-1">{form.errors.title}</p>{/if}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngày <span class="text-error">*</span></label>
                            <input type="date" bind:value={form.session_date} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            {#if form.errors.session_date}<p class="text-xs text-error mt-1">{form.errors.session_date}</p>{/if}
                        </div>
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Lớp <span class="text-error">*</span></label>
                            {#if isEditing}
                                <input type="text" value={form.grade_level} disabled class="w-full px-4 py-2 bg-surface-container-high rounded-xl border-none outline-none text-outline-variant" />
                            {:else}
                                <select bind:value={form.grade_level} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                    <option value="" disabled>Chọn lớp...</option>
                                    {#each gradeLevels as grade}
                                        <option value={grade}>{grade}</option>
                                    {/each}
                                </select>
                            {/if}
                            {#if form.errors.grade_level}<p class="text-xs text-error mt-1">{form.errors.grade_level}</p>{/if}
                        </div>
                    </div>

                    {#if isEditing}
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Trạng thái</label>
                            <select bind:value={form.status} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="upcoming">Sắp tới</option>
                                <option value="in_progress">Đang diễn ra</option>
                                <option value="completed">Hoàn tất</option>
                            </select>
                        </div>
                    {/if}

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ghi chú</label>
                        <textarea bind:value={form.notes} rows="3" class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
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
                    {isEditing ? 'Lưu thay đổi' : 'Tạo phiên'}
                </button>
            </div>
        </div>
    </div>
{/if}
