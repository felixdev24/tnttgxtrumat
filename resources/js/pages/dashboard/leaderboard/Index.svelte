<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { router, page } from '@inertiajs/svelte';

    interface LeaderboardEntry {
        rank: number;
        user_id: number;
        name: string;
        username: string;
        avatar: string | null;
        branch: string | null;
        tntt_class_name: string | null;
        total_points: number;
    }

    interface MonthStat {
        label: string;
        attendance: number;
        activity: number;
        quiz: number;
    }

    let {
        leaderboard,
        monthlyStats,
        classes,
        filters,
    }: {
        leaderboard: LeaderboardEntry[];
        monthlyStats: MonthStat[];
        classes: any[];
        filters: any;
    } = $props();

    let selectedClass = $state(filters.tntt_class_id || '');
    let selectedType = $state(filters.type || 'total');
    let selectedPeriod = $state(filters.period || 'all');
    let selectedYear = $state(filters.year || new Date().getFullYear());
    let selectedMonth = $state(filters.month || new Date().getMonth() + 1);

    function applyFilters() {
        router.get(
            '/dashboard/leaderboard',
            {
                tntt_class_id: selectedClass,
                type: selectedType,
                period: selectedPeriod,
                year: selectedYear,
                month: selectedMonth,
            },
            { preserveState: true },
        );
    }

    const typeLabels: Record<string, string> = {
        total: 'Tổng điểm',
        attendance: 'Điểm chuyên cần',
        activity: 'Điểm sinh hoạt',
        quiz: 'Điểm đố vui',
    };

    const typeColors: Record<string, string> = {
        attendance: '#4ade80',
        activity: '#a78bfa',
        quiz: '#facc15',
    };

    const typeIcons: Record<string, string> = {
        total: 'emoji_events',
        attendance: 'how_to_reg',
        activity: 'groups',
        quiz: 'quiz',
    };

    const months = [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12',
    ];

    function getRankMedal(rank: number) {
        if (rank === 1) return '🥇';
        if (rank === 2) return '🥈';
        if (rank === 3) return '🥉';
        return `#${rank}`;
    }

    function getRankBg(rank: number) {
        if (rank === 1) return 'bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-amber-950/30 dark:to-yellow-950/30 border-l-4 border-l-amber-400';
        if (rank === 2) return 'bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-900/50 dark:to-gray-900/50 border-l-4 border-l-slate-400';
        if (rank === 3) return 'bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-950/30 dark:to-amber-950/30 border-l-4 border-l-orange-400';
        return '';
    }

    // Simple bar chart max
    const maxBarValue = $derived(
        Math.max(...monthlyStats.flatMap((m) => [m.attendance, m.activity, m.quiz]), 1),
    );

    const currentYear = new Date().getFullYear();
    const years = [currentYear - 1, currentYear, currentYear + 1];
</script>

<svelte:head>
    <title>Bảng Xếp Hạng - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">

            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-[36px]" style="font-variation-settings:'FILL' 1;">emoji_events</span>
                        Bảng Xếp Hạng Điểm
                    </h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Thống kê điểm chuyên cần, sinh hoạt và đố vui Kinh Thánh.</p>
                </div>
            </div>

            {#if page.props.flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{page.props.flash.success}</p>
                </div>
            {/if}

            <!-- Filter Bar -->
            <div class="glass-card rounded-2xl shadow-md p-4 mb-gutter">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <!-- Type filter -->
                    <div>
                        <label class="block text-xs font-label-bold text-on-surface-variant mb-1">Loại điểm</label>
                        <select
                            bind:value={selectedType}
                            onchange={applyFilters}
                            class="w-full px-3 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                            <option value="total">🏆 Tổng điểm</option>
                            <option value="attendance">✅ Chuyên cần</option>
                            <option value="activity">🎉 Sinh hoạt</option>
                            <option value="quiz">📖 Đố vui KB</option>
                        </select>
                    </div>

                    <!-- Period filter -->
                    <div>
                        <label class="block text-xs font-label-bold text-on-surface-variant mb-1">Khoảng thời gian</label>
                        <select
                            bind:value={selectedPeriod}
                            onchange={applyFilters}
                            class="w-full px-3 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                            <option value="all">Tất cả thời gian</option>
                            <option value="year">Theo năm</option>
                            <option value="month">Theo tháng</option>
                        </select>
                    </div>

                    <!-- Month/Year filters -->
                    {#if selectedPeriod === 'month'}
                        <div>
                            <label class="block text-xs font-label-bold text-on-surface-variant mb-1">Tháng</label>
                            <select
                                bind:value={selectedMonth}
                                onchange={applyFilters}
                                class="w-full px-3 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                            >
                                {#each months as m, i}
                                    <option value={i + 1}>{m}</option>
                                {/each}
                            </select>
                        </div>
                    {/if}

                    {#if selectedPeriod !== 'all'}
                        <div>
                            <label class="block text-xs font-label-bold text-on-surface-variant mb-1">Năm</label>
                            <select
                                bind:value={selectedYear}
                                onchange={applyFilters}
                                class="w-full px-3 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                            >
                                {#each years as y}
                                    <option value={y}>{y}</option>
                                {/each}
                            </select>
                        </div>
                    {/if}

                    <!-- Class filter -->
                    <div class="{selectedPeriod === 'month' ? 'col-span-2 md:col-span-1' : ''}">
                        <label class="block text-xs font-label-bold text-on-surface-variant mb-1">Lọc theo lớp</label>
                        <select
                            bind:value={selectedClass}
                            onchange={applyFilters}
                            class="w-full px-3 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 text-sm"
                        >
                            <option value="">Tất cả lớp</option>
                            {#each classes as cls}
                                <option value={cls.id}>{cls.name}</option>
                            {/each}
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
                <!-- Leaderboard table -->
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl shadow-md overflow-hidden border border-outline-variant/10">
                        <div class="px-6 py-4 bg-surface-container-lowest border-b border-outline-variant/10 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">{typeIcons[selectedType]}</span>
                            <h2 class="font-title-md text-on-surface">{typeLabels[selectedType]}</h2>
                            {#if selectedPeriod === 'month'}
                                <span class="ml-auto text-xs text-outline">Tháng {selectedMonth}/{selectedYear}</span>
                            {:else if selectedPeriod === 'year'}
                                <span class="ml-auto text-xs text-outline">Năm {selectedYear}</span>
                            {/if}
                        </div>

                        {#if leaderboard.length === 0}
                            <div class="p-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-[48px] opacity-30 block mb-2">leaderboard</span>
                                <p>Chưa có dữ liệu điểm nào.</p>
                                <p class="text-xs mt-1">Hãy hoàn thành phiên điểm danh để bắt đầu tính điểm.</p>
                            </div>
                        {:else}
                            <div class="divide-y divide-outline-variant/10">
                                {#each leaderboard as entry (entry.user_id)}
                                    <div class="flex items-center gap-4 px-4 py-3 hover:bg-surface-container/30 transition-colors {getRankBg(entry.rank)}">
                                        <!-- Rank -->
                                        <div class="w-10 text-center font-black text-lg {entry.rank <= 3 ? 'text-2xl' : 'text-on-surface-variant'}">
                                            {getRankMedal(entry.rank)}
                                        </div>

                                        <!-- Avatar -->
                                        <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center flex-shrink-0 overflow-hidden">
                                            {#if entry.avatar}
                                                <img src={entry.avatar} alt={entry.name} class="w-full h-full object-cover" />
                                            {:else}
                                                <span class="font-bold text-primary text-sm">{entry.name?.charAt(0) ?? '?'}</span>
                                            {/if}
                                        </div>

                                        <!-- Info -->
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-on-surface text-sm truncate">{entry.name}</p>
                                            <p class="text-xs text-on-surface-variant">
                                                {entry.tntt_class_name ?? 'Chưa có lớp'}
                                                {#if entry.branch}• {entry.branch}{/if}
                                            </p>
                                        </div>

                                        <!-- Points -->
                                        <div class="text-right flex-shrink-0">
                                            <div class="font-black text-xl text-primary tabular-nums">{entry.total_points}</div>
                                            <div class="text-xs text-on-surface-variant">điểm</div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Right: Monthly chart + point type breakdown -->
                <div class="space-y-gutter">
                    <!-- Monthly bar chart -->
                    <div class="glass-card rounded-2xl shadow-md p-5 border border-outline-variant/10">
                        <h3 class="font-title-md text-on-surface mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">bar_chart</span>
                            Điểm 6 tháng gần nhất
                        </h3>

                        <div class="space-y-3">
                            {#each monthlyStats as stat}
                                <div>
                                    <div class="text-xs text-on-surface-variant mb-1 font-label-bold">{stat.label}</div>
                                    <div class="space-y-1">
                                        <!-- Attendance bar -->
                                        {#if stat.attendance > 0}
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 text-[10px] text-on-surface-variant text-right">Chuyên cần</div>
                                                <div class="flex-1 bg-surface-container-high rounded-full h-3 overflow-hidden">
                                                    <div
                                                        class="h-full rounded-full bg-emerald-400 transition-all duration-500"
                                                        style="width: {Math.round((stat.attendance / maxBarValue) * 100)}%"
                                                    ></div>
                                                </div>
                                                <div class="w-8 text-[10px] font-bold text-emerald-600 tabular-nums">{stat.attendance}</div>
                                            </div>
                                        {/if}
                                        <!-- Activity bar -->
                                        {#if stat.activity > 0}
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 text-[10px] text-on-surface-variant text-right">Sinh hoạt</div>
                                                <div class="flex-1 bg-surface-container-high rounded-full h-3 overflow-hidden">
                                                    <div
                                                        class="h-full rounded-full bg-purple-400 transition-all duration-500"
                                                        style="width: {Math.round((stat.activity / maxBarValue) * 100)}%"
                                                    ></div>
                                                </div>
                                                <div class="w-8 text-[10px] font-bold text-purple-600 tabular-nums">{stat.activity}</div>
                                            </div>
                                        {/if}
                                        <!-- Quiz bar -->
                                        {#if stat.quiz > 0}
                                            <div class="flex items-center gap-2">
                                                <div class="w-16 text-[10px] text-on-surface-variant text-right">Đố vui</div>
                                                <div class="flex-1 bg-surface-container-high rounded-full h-3 overflow-hidden">
                                                    <div
                                                        class="h-full rounded-full bg-amber-400 transition-all duration-500"
                                                        style="width: {Math.round((stat.quiz / maxBarValue) * 100)}%"
                                                    ></div>
                                                </div>
                                                <div class="w-8 text-[10px] font-bold text-amber-600 tabular-nums">{stat.quiz}</div>
                                            </div>
                                        {/if}
                                        {#if stat.attendance === 0 && stat.activity === 0 && stat.quiz === 0}
                                            <div class="text-xs text-outline-variant italic">Không có điểm</div>
                                        {/if}
                                    </div>
                                </div>
                            {/each}
                        </div>

                        <!-- Legend -->
                        <div class="flex gap-3 mt-4 flex-wrap">
                            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-emerald-400"></div><span class="text-[10px] text-on-surface-variant">Chuyên cần</span></div>
                            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-purple-400"></div><span class="text-[10px] text-on-surface-variant">Sinh hoạt</span></div>
                            <div class="flex items-center gap-1"><div class="w-3 h-3 rounded-full bg-amber-400"></div><span class="text-[10px] text-on-surface-variant">Đố vui</span></div>
                        </div>
                    </div>

                    <!-- Point types explanation -->
                    <div class="glass-card rounded-2xl shadow-md p-5 border border-outline-variant/10">
                        <h3 class="font-title-md text-on-surface mb-3">Hệ thống điểm</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-emerald-500 text-[20px] mt-0.5">how_to_reg</span>
                                <div>
                                    <p class="font-bold text-on-surface">Điểm chuyên cần</p>
                                    <p class="text-xs text-on-surface-variant">Cộng khi có mặt trong phiên Giáo lý và Huynh trưởng đánh dấu hoàn thành.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-purple-500 text-[20px] mt-0.5">groups</span>
                                <div>
                                    <p class="font-bold text-on-surface">Điểm sinh hoạt</p>
                                    <p class="text-xs text-on-surface-variant">Cộng khi tham gia các buổi sinh hoạt, liên đoàn, trại...</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="material-symbols-outlined text-amber-500 text-[20px] mt-0.5">quiz</span>
                                <div>
                                    <p class="font-bold text-on-surface">Điểm đố vui</p>
                                    <p class="text-xs text-on-surface-variant">Cộng khi trả lời đúng câu hỏi Kinh Thánh hàng tuần.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</DashboardLayout>
