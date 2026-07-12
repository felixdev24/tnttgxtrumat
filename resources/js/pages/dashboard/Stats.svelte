<script lang="ts">
    import DashboardLayout from '../../layouts/DashboardLayout.svelte';
    import { Link, page } from '@inertiajs/svelte';

    interface MonthlyStat {
        label: string;
        count: number;
    }

    interface BranchStat {
        branch: string;
        count: number;
    }

    interface Activity {
        type: string;
        user_name: string;
        user_role: string;
        action: string;
        category: string;
        status: string;
        time: string;
    }

    let {
        summary,
        monthlyGrowth,
        branchDistribution,
        totalForPie,
        recentActivity,
    }: {
        summary: {
            totalDoanSinh: number;
            growthPercent: number;
            postsThisMonth: number;
            upcomingEvents: number;
            attendanceRate: number;
        };
        monthlyGrowth: MonthlyStat[];
        branchDistribution: BranchStat[];
        totalForPie: number;
        recentActivity: Activity[];
    } = $props();

    // Chart helpers
    const maxGrowth = $derived(Math.max(...monthlyGrowth.map(m => m.count), 1));

    // Donut chart colors
    const branchColors: Record<string, string> = {
        'Ấu': '#004a99',
        'Thiếu': '#bc0000',
        'Nghĩa': '#737783',
        'Hiệp': '#283649',
    };

    function getBranchColor(branch: string): string {
        return branchColors[branch] || '#ffb4a8';
    }

    // Generate SVG donut segments
    function getDonutSegments() {
        if (!branchDistribution || branchDistribution.length === 0) return [];

        const total = branchDistribution.reduce((acc, b) => acc + b.count, 0);
        if (total === 0) return [];

        let offset = 0;
        const circumference = 2 * Math.PI * 42; // radius = 42%

        return branchDistribution.map(b => {
            const pct = b.count / total;
            const dashLength = pct * circumference;
            const dashGap = circumference - dashLength;
            const seg = {
                branch: b.branch,
                count: b.count,
                percent: Math.round(pct * 100),
                dashArray: `${dashLength} ${dashGap}`,
                dashOffset: -offset,
                color: getBranchColor(b.branch),
            };
            offset += dashLength;
            return seg;
        });
    }

    const donutSegments = $derived(getDonutSegments());

    function getInitials(name: string): string {
        return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    }

    const statusColors: Record<string, {bg: string, text: string, dot: string}> = {
        'Công khai': { bg: 'bg-emerald-100 dark:bg-emerald-950/30', text: 'text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500' },
        'Nháp': { bg: 'bg-amber-100 dark:bg-amber-950/30', text: 'text-amber-700 dark:text-amber-400', dot: 'bg-amber-500' },
        'Cập nhật': { bg: 'bg-blue-100 dark:bg-blue-950/30', text: 'text-blue-700 dark:text-blue-400', dot: 'bg-blue-500' },
        'Đã xóa': { bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-700 dark:text-gray-400', dot: 'bg-gray-500' },
    };

    function getStatusStyle(status: string) {
        return statusColors[status] || statusColors['Nháp'];
    }
</script>

<svelte:head>
    <title>Thống kê Tổng quan - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Thống kê Tổng quan</h1>
                    <p class="text-on-surface-variant text-sm mt-1">Dữ liệu cập nhật theo thời gian thực cho Xứ Đoàn Trù Mật.</p>
                </div>
                <div class="flex gap-3">
                    <a
                        href="/dashboard/attendance/stats/export"
                        class="px-4 py-2.5 bg-surface-container-highest dark:bg-zinc-700 text-on-surface-variant font-semibold rounded-xl flex items-center gap-2 hover:bg-surface-container-high transition-all text-sm"
                    >
                        <span class="material-symbols-outlined text-[18px]">download</span>
                        Tải báo cáo
                    </a>
                    <Link
                        href="/dashboard/posts/create"
                        class="px-4 py-2.5 bg-secondary text-white font-semibold rounded-xl flex items-center gap-2 hover:brightness-90 transition-all shadow-lg shadow-red-900/10 active:scale-95 text-sm"
                    >
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        Tạo bài viết mới
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Students -->
                <div class="bg-primary text-white p-6 rounded-2xl stat-card-shadow flex flex-col justify-between relative overflow-hidden h-32">
                    <div class="z-10">
                        <p class="text-xs font-bold uppercase tracking-wide opacity-80">Tổng Đoàn sinh</p>
                        <h2 class="text-3xl font-bold mt-1">{summary.totalDoanSinh}</h2>
                    </div>
                    <span class="material-symbols-outlined absolute -right-4 -bottom-4 text-8xl opacity-10">group</span>
                    {#if summary.growthPercent > 0}
                        <div class="z-10 flex items-center text-xs gap-1 mt-2 font-medium">
                            <span class="bg-white/20 px-1.5 py-0.5 rounded text-white">+{summary.growthPercent}% tháng này</span>
                        </div>
                    {/if}
                </div>

                <!-- Monthly Posts -->
                <div class="glass-card p-6 rounded-2xl stat-card-shadow flex flex-col justify-between h-32 border border-outline-variant/20">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Bài viết tháng này</p>
                        <h2 class="text-3xl font-bold text-on-surface mt-1">{summary.postsThisMonth}</h2>
                    </div>
                    {#if summary.postsThisMonth > 0}
                        <div class="flex items-center text-xs gap-1 font-medium text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-sm">trending_up</span>
                            <span>Đang hoạt động tốt</span>
                        </div>
                    {/if}
                </div>

                <!-- Upcoming Events -->
                <div class="glass-card p-6 rounded-2xl stat-card-shadow flex flex-col justify-between h-32 border border-outline-variant/20">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Hoạt động sắp tới</p>
                        <h2 class="text-3xl font-bold text-on-surface mt-1">{summary.upcomingEvents}</h2>
                    </div>
                    {#if summary.upcomingEvents > 0}
                        <div class="flex items-center text-xs gap-1 font-medium text-amber-600 dark:text-amber-400">
                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                            <span>Trong 30 ngày tới</span>
                        </div>
                    {/if}
                </div>

                <!-- Attendance -->
                <div class="glass-card p-6 rounded-2xl stat-card-shadow flex flex-col justify-between h-32 border border-outline-variant/20">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-on-surface-variant">Điểm danh gần nhất</p>
                        <h2 class="text-3xl font-bold text-on-surface mt-1">{summary.attendanceRate}%</h2>
                    </div>
                    <div class="w-full bg-surface-container-high dark:bg-zinc-700 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-1000" style="width: {summary.attendanceRate}%"></div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Bar Chart: Monthly Growth -->
                <div class="lg:col-span-2 glass-card p-6 rounded-2xl stat-card-shadow border border-outline-variant/20">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-on-surface">Tăng trưởng Đoàn sinh</h3>
                            <p class="text-sm text-on-surface-variant">Biểu đồ 6 tháng gần nhất</p>
                        </div>
                    </div>
                    <div class="h-64 flex items-end justify-between gap-3 relative">
                        <!-- Grid lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                            <div class="border-t border-outline-variant/20 w-full h-0"></div>
                            <div class="border-t border-outline-variant/20 w-full h-0"></div>
                            <div class="border-t border-outline-variant/20 w-full h-0"></div>
                            <div class="border-t border-outline-variant/30 w-full h-0"></div>
                        </div>
                        <!-- Bars -->
                        {#each monthlyGrowth as item, i}
                            {@const heightPct = maxGrowth > 0 ? Math.max((item.count / maxGrowth) * 100, 5) : 5}
                            {@const isLast = i === monthlyGrowth.length - 1}
                            <div class="flex-1 flex flex-col items-center group relative">
                                <div
                                    class="w-full rounded-t-lg transition-all duration-500 relative cursor-pointer {isLast ? 'bg-primary hover:bg-primary-container' : 'bg-primary/20 hover:bg-primary/40'}"
                                    style="height: {heightPct}%"
                                >
                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-on-surface text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                        {item.count}
                                    </div>
                                </div>
                                <span class="mt-4 text-[11px] font-bold uppercase tracking-wider {isLast ? 'text-primary' : 'text-on-surface-variant'}">{item.label}</span>
                            </div>
                        {/each}
                    </div>
                </div>

                <!-- Donut Chart: Branch Distribution -->
                <div class="glass-card p-6 rounded-2xl stat-card-shadow border border-outline-variant/20">
                    <h3 class="text-lg font-bold text-on-surface mb-1">Cơ cấu Ngành</h3>
                    <p class="text-sm text-on-surface-variant mb-6">Phân bổ đoàn sinh theo ngành</p>

                    <div class="flex flex-col items-center gap-6">
                        <!-- Donut SVG -->
                        <div class="relative w-48 h-48 flex items-center justify-center">
                            <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 100 100">
                                {#each donutSegments as seg}
                                    <circle
                                        cx="50" cy="50" r="42"
                                        fill="transparent"
                                        stroke={seg.color}
                                        stroke-width="12"
                                        stroke-dasharray={seg.dashArray}
                                        stroke-dashoffset={seg.dashOffset}
                                        class="transition-all duration-1000"
                                    />
                                {/each}
                                {#if donutSegments.length === 0}
                                    <circle cx="50" cy="50" r="42" fill="transparent" stroke="#e0e3e5" stroke-width="12" />
                                {/if}
                            </svg>
                            <div class="text-center z-10">
                                <span class="text-2xl font-bold text-primary block">{totalForPie}</span>
                                <span class="text-xs font-semibold text-on-surface-variant">Đoàn sinh</span>
                            </div>
                        </div>

                        <!-- Legend -->
                        <div class="w-full grid grid-cols-2 gap-3">
                            {#each donutSegments as seg}
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {seg.color}"></div>
                                    <span class="text-xs font-semibold text-on-surface-variant">{seg.branch} ({seg.percent}%)</span>
                                </div>
                            {/each}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <div class="glass-card rounded-2xl stat-card-shadow border border-outline-variant/20 overflow-hidden">
                <div class="p-6 border-b border-outline-variant/20 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-on-surface">Hoạt động Gần đây</h3>
                    <Link href="/dashboard/posts" class="text-primary font-semibold text-sm hover:underline">Xem tất cả</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[700px]">
                        <thead class="bg-surface-container/50 dark:bg-zinc-800/50 border-b border-outline-variant/20">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Người thực hiện</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Hành động</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Danh mục</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Thời gian</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            {#each recentActivity as activity}
                                {@const style = getStatusStyle(activity.status)}
                                <tr class="hover:bg-surface-container/30 dark:hover:bg-zinc-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-fixed dark:bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                                                {getInitials(activity.user_name)}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-sm text-on-surface">{activity.user_name}</p>
                                                <p class="text-[10px] text-on-surface-variant">{activity.user_role}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-on-surface">{activity.action}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-surface-container-high dark:bg-zinc-700 rounded text-[11px] font-bold text-on-surface-variant uppercase">{activity.category}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-on-surface-variant">{activity.time}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {style.bg} {style.text} text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full {style.dot}"></span>
                                            {activity.status}
                                        </span>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-on-surface-variant">
                                        <span class="material-symbols-outlined text-4xl mb-2 block opacity-30">history</span>
                                        <p class="text-sm">Chưa có hoạt động nào gần đây.</p>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</DashboardLayout>

<style>
    .stat-card-shadow {
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.05);
    }
</style>
