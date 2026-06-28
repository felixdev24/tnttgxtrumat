<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, page } from '@inertiajs/svelte';

    let { topMembers, classes, filters }: { topMembers: any[]; classes: any[]; filters: any } = $props();

    let selectedClass = $state(filters.tntt_class_id || '');

    function applyFilters() {
        router.get('/dashboard/attendance/stats', { tntt_class_id: selectedClass }, { preserveState: true });
    }
</script>

<svelte:head>
    <title>Thống Kê Điểm Danh - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            
            <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-4">
                <Link href="/dashboard/attendance" class="hover:underline">Điểm Danh</Link>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span class="text-primary font-medium">Thống Kê</span>
            </nav>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Thống Kê Điểm Danh</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Theo dõi chuyên cần và xếp hạng đoàn sinh.</p>
                </div>
                <a
                    href="/dashboard/attendance/stats/export{selectedClass ? '?tntt_class_id=' + selectedClass : ''}"
                    class="duolingo-shadow-primary bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-title-md text-[14px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined text-[20px]">download</span>
                    Xuất Báo Cáo Chuyên Cần
                </a>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter flex items-center justify-between">
                <h2 class="font-title-md text-on-surface">Lọc dữ liệu</h2>
                <div class="w-full md:w-64">
                    <select 
                        bind:value={selectedClass}
                        onchange={applyFilters}
                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm font-label-bold"
                    >
                        <option value="">Tất cả các lớp</option>
                        {#each classes as cls}
                            <option value={cls.id}>{cls.name}</option>
                        {/each}
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
                <!-- Top Attendance -->
                <div class="glass-card rounded-2xl shadow-md p-6 border border-outline-variant/10">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="material-symbols-outlined text-amber-500 text-[28px]">social_leaderboard</span>
                        <h2 class="font-title-lg text-on-surface">Bảng Xếp Hạng Chuyên Cần</h2>
                    </div>

                    <div class="space-y-4">
                        {#each topMembers as record, i}
                            <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-surface-container-lowest transition-colors">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm
                                    {i === 0 ? 'bg-amber-100 text-amber-600' : 
                                     i === 1 ? 'bg-zinc-200 text-zinc-600' : 
                                     i === 2 ? 'bg-orange-100 text-orange-600' : 'bg-surface-variant text-on-surface-variant'}"
                                >
                                    #{i + 1}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-label-bold text-on-surface">{record.user.name}</h4>
                                    <p class="text-xs text-outline-variant">Lớp: {record.user.tntt_class?.name || 'Chưa xếp lớp'}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-emerald-600">{record.count}</span>
                                    <span class="text-xs text-outline block">lần có mặt</span>
                                </div>
                            </div>
                        {:else}
                            <div class="text-center py-8 text-on-surface-variant">
                                Chưa có đủ dữ liệu thống kê.
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- Chart Placeholder -->
                <div class="glass-card rounded-2xl shadow-md p-6 border border-outline-variant/10 flex flex-col items-center justify-center text-center min-h-[400px]">
                    <span class="material-symbols-outlined text-[64px] text-primary/30 mb-4">monitoring</span>
                    <h3 class="font-title-lg text-on-surface mb-2">Biểu đồ đang cập nhật</h3>
                    <p class="text-sm text-on-surface-variant max-w-sm">
                        Hệ thống đang thu thập thêm dữ liệu điểm danh để tạo biểu đồ tỷ lệ chuyên cần theo thời gian.
                    </p>
                </div>
            </div>

        </div>
    </div>
</DashboardLayout>
