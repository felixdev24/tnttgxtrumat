<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';

    interface CalendarEvent {
        id: number;
        event_date: string; // "YYYY-MM-DD"
        title: string;
        type: 'solemnity' | 'memorial' | 'feast' | 'note';
        description: string | null;
        created_at?: string;
        updated_at?: string;
    }

    let { events = [] }: { events: CalendarEvent[] } = $props();

    // Svelte 5 states
    let today = new Date();
    let currentYear = $state(today.getFullYear());
    let currentMonth = $state(today.getMonth() + 1); // 1-12
    let selectedDateStr = $state<string | null>(null);

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);

    // Form setup using Inertia useForm
    const form = useForm({
        id: null as number | null,
        event_date: '',
        title: '',
        type: 'note' as 'solemnity' | 'memorial' | 'feast' | 'note',
        description: '',
    });

    // Helper data
    const daysOfWeek = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
    const months = Array.from({ length: 12 }, (_, i) => i + 1);
    const years = Array.from(
        { length: 10 },
        (_, i) => today.getFullYear() - 2 + i,
    );

    // Get days in a month
    function getDaysInMonth(year: number, month: number): number {
        return new Date(year, month, 0).getDate();
    }

    // Get weekday of the first day of the month (0 = Sunday, 1 = Monday...)
    function getFirstDayOfMonth(year: number, month: number): number {
        return new Date(year, month - 1, 1).getDay();
    }

    // Days grid calculation
    let calendarDays = $derived.by(() => {
        const totalDays = getDaysInMonth(currentYear, currentMonth);
        const startDay = getFirstDayOfMonth(currentYear, currentMonth);
        const days: Array<{
            day: number;
            dateStr: string;
            isCurrentMonth: boolean;
        }> = [];

        // Previous month padding
        const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        const daysInPrevMonth = getDaysInMonth(prevYear, prevMonth);
        for (let i = startDay - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const dateStr = `${prevYear}-${String(prevMonth).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            days.push({ day: dayNum, dateStr, isCurrentMonth: false });
        }

        // Current month days
        for (let i = 1; i <= totalDays; i++) {
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            days.push({ day: i, dateStr, isCurrentMonth: true });
        }

        // Next month padding to complete the grid (multiples of 7)
        const totalCells = Math.ceil(days.length / 7) * 7;
        const nextMonthPadding = totalCells - days.length;
        const nextMonth = currentMonth === 12 ? 1 : currentMonth + 1;
        const nextYear = currentMonth === 12 ? currentYear + 1 : currentYear;
        for (let i = 1; i <= nextMonthPadding; i++) {
            const dateStr = `${nextYear}-${String(nextMonth).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            days.push({ day: i, dateStr, isCurrentMonth: false });
        }

        return days;
    });

    // Map events by date for quick lookup
    let eventsByDate = $derived.by(() => {
        const map: Record<string, CalendarEvent[]> = {};
        events.forEach((e) => {
            const d = e.event_date.split(' ')[0]; // Ensure just YYYY-MM-DD
            if (!map[d]) {
                map[d] = [];
            }
            map[d].push(e);
        });
        return map;
    });

    // Events in selected month
    let eventsInSelectedMonth = $derived.by(() => {
        return events
            .filter((e) => {
                const d = new Date(e.event_date);
                return (
                    d.getFullYear() === currentYear &&
                    d.getMonth() + 1 === currentMonth
                );
            })
            .sort(
                (a, b) =>
                    new Date(a.event_date).getTime() -
                    new Date(b.event_date).getTime(),
            );
    });

    // Selected day's events
    let selectedDateEvents = $derived(
        selectedDateStr ? eventsByDate[selectedDateStr] || [] : [],
    );

    // Displayed events list (selected date or entire month)
    let displayedEvents = $derived(
        selectedDateStr ? selectedDateEvents : eventsInSelectedMonth,
    );

    // Format display date
    function formatDateVietnamese(dateStr: string): string {
        const dateObj = new Date(dateStr);
        const day = dateObj.getDate();
        const month = dateObj.getMonth() + 1;
        const year = dateObj.getFullYear();
        const weekdays = [
            'Chủ Nhật',
            'Thứ Hai',
            'Thứ Ba',
            'Thứ Tư',
            'Thứ Năm',
            'Thứ Sáu',
            'Thứ Bảy',
        ];
        const weekday = weekdays[dateObj.getDay()];
        return `${weekday}, Ngày ${day} Tháng ${month}, ${year}`;
    }

    // Navigate months
    function prevMonth(): void {
        if (currentMonth === 1) {
            currentMonth = 12;
            currentYear--;
        } else {
            currentMonth--;
        }
        selectedDateStr = null;
    }

    function nextMonth(): void {
        if (currentMonth === 12) {
            currentMonth = 1;
            currentYear++;
        } else {
            currentMonth++;
        }
        selectedDateStr = null;
    }

    // Select a day
    function selectDay(dateStr: string): void {
        if (selectedDateStr === dateStr) {
            selectedDateStr = null;
        } else {
            selectedDateStr = dateStr;
        }
    }

    // Event operations
    function openAddModal(dateStr?: string): void {
        isEditing = false;
        form.reset();
        form.id = null;
        form.event_date = dateStr || new Date().toISOString().split('T')[0];
        form.type = 'note';
        showModal = true;
    }

    function openEditModal(event: CalendarEvent): void {
        isEditing = true;
        form.reset();
        form.id = event.id;
        form.event_date = event.event_date.split(' ')[0];
        form.title = event.title;
        form.type = event.type;
        form.description = event.description || '';
        showModal = true;
    }

    function submitForm(): void {
        if (isEditing && form.id) {
            form.put(`/dashboard/calendar/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/calendar', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteEvent(event: CalendarEvent): void {
        if (confirm(`Bạn có chắc muốn xóa lịch: "${event.title}"?`)) {
            router.delete(`/dashboard/calendar/${event.id}`, {
                preserveScroll: true,
            });
        }
    }

    // CSS styling labels
    const typeLabels = {
        solemnity: 'Lễ Trọng',
        memorial: 'Lễ Nhớ',
        feast: 'Lễ Kính',
        note: 'Ghi Chú / Sự Kiện',
    };

    const typeColors = {
        solemnity: {
            bg: 'bg-red-50 dark:bg-red-950/20',
            border: 'border-red-100 dark:border-red-900/40',
            text: 'text-red-700 dark:text-red-400',
            dot: 'bg-red-500',
            badge: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/50',
        },
        feast: {
            bg: 'bg-amber-50 dark:bg-amber-950/20',
            border: 'border-amber-100 dark:border-amber-900/40',
            text: 'text-amber-700 dark:text-amber-400',
            dot: 'bg-amber-500',
            badge: 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50',
        },
        memorial: {
            bg: 'bg-purple-50 dark:bg-purple-950/20',
            border: 'border-purple-100 dark:border-purple-900/40',
            text: 'text-purple-700 dark:text-purple-400',
            dot: 'bg-purple-500',
            badge: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-900/50',
        },
        note: {
            bg: 'bg-emerald-50 dark:bg-emerald-950/20',
            border: 'border-emerald-100 dark:border-emerald-900/40',
            text: 'text-emerald-700 dark:text-emerald-400',
            dot: 'bg-emerald-500',
            badge: 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50',
        },
    };

    let selectedMonthName = $derived(
        `Tháng ${currentMonth} Năm ${currentYear}`,
    );

    // Monthly event counters
    let countSolemnities = $derived(
        eventsInSelectedMonth.filter((e) => e.type === 'solemnity').length,
    );
    let countMemorials = $derived(
        eventsInSelectedMonth.filter((e) => e.type === 'memorial').length,
    );
    let countFeasts = $derived(
        eventsInSelectedMonth.filter((e) => e.type === 'feast').length,
    );
    let countNotes = $derived(
        eventsInSelectedMonth.filter((e) => e.type === 'note').length,
    );

    // Get event status classes
    function getStatusDots(dateStr: string): CalendarEvent[] {
        return eventsByDate[dateStr] || [];
    }

    // Check if a date string is today
    function isToday(dateStr: string): boolean {
        const t = new Date();
        const d = new Date(dateStr);
        return (
            t.getDate() === d.getDate() &&
            t.getMonth() === d.getMonth() &&
            t.getFullYear() === d.getFullYear()
        );
    }
</script>

<svelte:head>
    <title>Lịch Phụng Vụ & Hoạt Động - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div
        class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg"
    >
        <div class="max-w-container-max mx-auto">
            <!-- Header section -->
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4"
            >
                <div>
                    <nav
                        class="flex items-center gap-2 text-on-surface-variant text-sm mb-1"
                    >
                        <Link href="/dashboard">Quản trị</Link>
                        <span class="material-symbols-outlined text-sm"
                            >chevron_right</span
                        >
                        <span class="text-primary font-medium"
                            >Lịch Phụng Vụ & Hoạt Động</span
                        >
                    </nav>
                    <h1 class="font-headline-lg text-headline-lg text-primary">
                        Quản Lý Lịch Phụng Vụ & Hoạt Động
                    </h1>
                    <p class="text-on-surface-variant mt-1 text-sm">
                        Điều hành và cập nhật các ngày lễ trọng, lễ kính, lễ nhớ
                        và ghi chú sinh hoạt.
                    </p>
                </div>
                <button
                    onclick={() => openAddModal()}
                    class="duolingo-shadow-primary bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined">add_circle</span>
                    Thêm sự kiện lịch
                </button>
            </div>

            <!-- Toast alert for Inertia success messages -->
            {#if page.props.flash?.success}
                <div
                    class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900/40 animate-fade-in shadow-sm"
                >
                    <span class="material-symbols-outlined text-emerald-500"
                        >check_circle</span
                    >
                    <p class="text-sm font-label-bold">
                        {page.props.flash.success}
                    </p>
                </div>
            {/if}

            <!-- Statistics cards for month (Rich styling) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-gutter mb-gutter">
                <!-- Lễ Trọng -->
                <div
                    class="glass-card p-5 rounded-2xl border-l-4 border-l-red-500 shadow-sm relative overflow-hidden flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-bold text-red-500 uppercase tracking-wide"
                                >Lễ Trọng</span
                            >
                            <span
                                class="material-symbols-outlined text-red-500 opacity-80"
                                >grade</span
                            >
                        </div>
                        <p
                            class="text-[32px] font-display-lg text-on-surface leading-tight"
                        >
                            {countSolemnities}
                        </p>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2">
                        Trong {selectedMonthName}
                    </p>
                </div>

                <!-- Lễ Kính -->
                <div
                    class="glass-card p-5 rounded-2xl border-l-4 border-l-amber-500 shadow-sm relative overflow-hidden flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-bold text-amber-500 uppercase tracking-wide"
                                >Lễ Kính</span
                            >
                            <span
                                class="material-symbols-outlined text-amber-500 opacity-80"
                                >workspace_premium</span
                            >
                        </div>
                        <p
                            class="text-[32px] font-display-lg text-on-surface leading-tight"
                        >
                            {countFeasts}
                        </p>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2">
                        Trong {selectedMonthName}
                    </p>
                </div>

                <!-- Lễ Nhớ -->
                <div
                    class="glass-card p-5 rounded-2xl border-l-4 border-l-purple-500 shadow-sm relative overflow-hidden flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-bold text-purple-500 uppercase tracking-wide"
                                >Lễ Nhớ</span
                            >
                            <span
                                class="material-symbols-outlined text-purple-500 opacity-80"
                                >bookmark</span
                            >
                        </div>
                        <p
                            class="text-[32px] font-display-lg text-on-surface leading-tight"
                        >
                            {countMemorials}
                        </p>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2">
                        Trong {selectedMonthName}
                    </p>
                </div>

                <!-- Ghi Chú / Sự Kiện -->
                <div
                    class="glass-card p-5 rounded-2xl border-l-4 border-l-emerald-500 shadow-sm relative overflow-hidden flex flex-col justify-between"
                >
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span
                                class="text-xs font-bold text-emerald-500 uppercase tracking-wide"
                                >Ghi Chú / Sự Kiện</span
                            >
                            <span
                                class="material-symbols-outlined text-emerald-500 opacity-80"
                                >event_note</span
                            >
                        </div>
                        <p
                            class="text-[32px] font-display-lg text-on-surface leading-tight"
                        >
                            {countNotes}
                        </p>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2">
                        Trong {selectedMonthName}
                    </p>
                </div>
            </div>

            <!-- Calendar and listing panel -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-gutter">
                <!-- Left: Calendar view -->
                <div class="xl:col-span-8">
                    <section
                        class="glass-card rounded-2xl shadow-md overflow-hidden border border-outline-variant/10 bg-white dark:bg-zinc-900"
                    >
                        <!-- Navigation bar -->
                        <div
                            class="p-5 flex flex-col sm:flex-row justify-between items-center border-b border-outline-variant/10 gap-4"
                        >
                            <h2
                                class="font-title-lg text-headline-sm text-primary flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined"
                                    >calendar_month</span
                                >
                                {selectedMonthName}
                            </h2>

                            <div class="flex items-center gap-3">
                                <!-- Navigation Buttons -->
                                <div
                                    class="flex items-center bg-surface-container rounded-xl p-1"
                                >
                                    <button
                                        onclick={prevMonth}
                                        class="p-2 hover:bg-white dark:hover:bg-zinc-800 rounded-lg text-on-surface transition-all"
                                        title="Tháng trước"
                                    >
                                        <span
                                            class="material-symbols-outlined block"
                                            >chevron_left</span
                                        >
                                    </button>
                                    <button
                                        onclick={() => {
                                            currentMonth = today.getMonth() + 1;
                                            currentYear = today.getFullYear();
                                            selectedDateStr = null;
                                        }}
                                        class="px-3 py-1 bg-white dark:bg-zinc-800 hover:bg-surface-variant rounded-lg text-xs font-bold shadow-sm transition-all"
                                    >
                                        Hôm nay
                                    </button>
                                    <button
                                        onclick={nextMonth}
                                        class="p-2 hover:bg-white dark:hover:bg-zinc-800 rounded-lg text-on-surface transition-all"
                                        title="Tháng sau"
                                    >
                                        <span
                                            class="material-symbols-outlined block"
                                            >chevron_right</span
                                        >
                                    </button>
                                </div>

                                <!-- Jump Selectors -->
                                <div class="flex gap-2">
                                    <select
                                        bind:value={currentMonth}
                                        class="bg-surface-container border-none rounded-xl px-3 py-2 text-sm font-label-bold outline-none focus:ring-2 focus:ring-primary/20"
                                        onchange={() =>
                                            (selectedDateStr = null)}
                                    >
                                        {#each months as m}
                                            <option value={m}>Tháng {m}</option>
                                        {/each}
                                    </select>
                                    <select
                                        bind:value={currentYear}
                                        class="bg-surface-container border-none rounded-xl px-3 py-2 text-sm font-label-bold outline-none focus:ring-2 focus:ring-primary/20"
                                        onchange={() =>
                                            (selectedDateStr = null)}
                                    >
                                        {#each years as y}
                                            <option value={y}>Năm {y}</option>
                                        {/each}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="p-6">
                            <!-- Weekdays Header -->
                            <div
                                class="grid grid-cols-7 gap-2 mb-2 text-center text-xs font-bold text-on-surface-variant uppercase tracking-wider"
                            >
                                {#each daysOfWeek as day}
                                    <div
                                        class="py-2 {day === 'CN'
                                            ? 'text-red-500'
                                            : ''}"
                                    >
                                        {day}
                                    </div>
                                {/each}
                            </div>

                            <!-- Calendar Cells -->
                            <div class="grid grid-cols-7 gap-2">
                                {#each calendarDays as { day, dateStr, isCurrentMonth }}
                                    {@const cellEvents = getStatusDots(dateStr)}
                                    <!-- svelte-ignore a11y_no_noninteractive_element_to_interactive_role -->
                                    <div
                                        role="button"
                                        tabindex="0"
                                        onclick={() => selectDay(dateStr)}
                                        onkeydown={(e) => {
                                            if (
                                                e.key === 'Enter' ||
                                                e.key === ' '
                                            ) {
                                                selectDay(dateStr);
                                            }
                                        }}
                                        ondblclick={() => openAddModal(dateStr)}
                                        class="cursor-pointer min-h-[90px] p-2 flex flex-col justify-between items-start border rounded-2xl text-left transition-all hover:bg-surface-container/60 relative group {isCurrentMonth
                                            ? 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800'
                                            : 'bg-zinc-50/50 dark:bg-zinc-950/20 border-zinc-100 dark:border-zinc-900 text-outline-variant'} {selectedDateStr ===
                                        dateStr
                                            ? 'ring-2 ring-primary/80 border-primary bg-primary/5 dark:bg-primary/5'
                                            : ''} {isToday(dateStr)
                                            ? 'border-primary border-2 shadow-sm bg-primary/5 dark:bg-primary/5'
                                            : ''}"
                                    >
                                        <!-- Day number -->
                                        <div
                                            class="flex items-center justify-between w-full"
                                        >
                                            <span
                                                class="text-sm font-bold {isToday(
                                                    dateStr,
                                                )
                                                    ? 'text-primary font-black scale-110'
                                                    : ''} {!isCurrentMonth
                                                    ? 'opacity-40'
                                                    : ''}"
                                            >
                                                {day}
                                            </span>
                                            {#if isToday(dateStr)}
                                                <span
                                                    class="text-[9px] px-1.5 py-0.5 rounded-full bg-primary text-on-primary font-black uppercase tracking-tighter"
                                                    >Nay</span
                                                >
                                            {/if}
                                        </div>

                                        <!-- Preview list of events (small, max 2) -->
                                        <div
                                            class="w-full mt-1 flex flex-col gap-1 overflow-hidden pointer-events-none"
                                        >
                                            {#each cellEvents.slice(0, 2) as ev}
                                                <div
                                                    class="text-[10px] px-1.5 py-0.5 rounded-md truncate font-medium {typeColors[
                                                        ev.type
                                                    ].bg} {typeColors[ev.type]
                                                        .text}"
                                                >
                                                    {ev.title}
                                                </div>
                                            {/each}
                                            {#if cellEvents.length > 2}
                                                <div
                                                    class="text-[9px] font-bold text-outline-variant pl-1 text-center"
                                                >
                                                    + {cellEvents.length - 2} lễ/ghi
                                                    chú
                                                </div>
                                            {/if}
                                        </div>

                                        <!-- Add quick indicator dots -->
                                        {#if cellEvents.length > 0}
                                            <div
                                                class="flex gap-1 justify-center w-full mt-1.5"
                                            >
                                                {#each cellEvents as ev}
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full {typeColors[
                                                            ev.type
                                                        ].dot}"
                                                    ></span>
                                                {/each}
                                            </div>
                                        {/if}

                                        <!-- Quick add button hover state -->
                                        <button
                                            onclick={(e) => {
                                                e.stopPropagation();
                                                openAddModal(dateStr);
                                            }}
                                            class="absolute bottom-1 right-1 p-1 bg-primary text-on-primary rounded-full opacity-0 group-hover:opacity-100 transition-opacity pointer-events-auto hover:scale-110 shadow-sm animate-fade-in"
                                            title="Thêm nhanh sự kiện vào ngày này"
                                        >
                                            <span
                                                class="material-symbols-outlined text-[12px] block"
                                                >add</span
                                            >
                                        </button>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right: Events Listing panel -->
                <div class="xl:col-span-4">
                    <section
                        class="glass-card rounded-2xl shadow-md overflow-hidden border border-outline-variant/10 bg-white dark:bg-zinc-900 h-full flex flex-col"
                    >
                        <!-- List header -->
                        <div
                            class="p-5 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-high/50"
                        >
                            <div>
                                <h3
                                    class="font-title-md text-[16px] text-on-surface flex items-center gap-2"
                                >
                                    <span
                                        class="material-symbols-outlined text-primary"
                                        >event_upcoming</span
                                    >
                                    {selectedDateStr
                                        ? 'Chi Tiết Ngày Chọn'
                                        : `Sự Kiện ${selectedMonthName}`}
                                </h3>
                                <p
                                    class="text-xs text-on-surface-variant mt-0.5"
                                >
                                    {selectedDateStr
                                        ? formatDateVietnamese(selectedDateStr)
                                        : `${eventsInSelectedMonth.length} sự kiện lịch biểu`}
                                </p>
                            </div>
                            {#if selectedDateStr}
                                <button
                                    onclick={() => (selectedDateStr = null)}
                                    class="p-1.5 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors"
                                    title="Xem tất cả trong tháng"
                                >
                                    <span
                                        class="material-symbols-outlined text-[18px] block"
                                        >close</span
                                    >
                                </button>
                            {/if}
                        </div>

                        <div
                            class="flex-1 overflow-y-auto max-h-[600px] divide-y divide-outline-variant/10"
                        >
                            {#if displayedEvents.length === 0}
                                <div
                                    class="flex flex-col items-center justify-center p-10 text-center h-64"
                                >
                                    <span
                                        class="material-symbols-outlined text-5xl text-outline-variant mb-3"
                                        >calendar_today</span
                                    >
                                    <p
                                        class="text-on-surface-variant font-label-bold text-sm"
                                    >
                                        Không có sự kiện nào
                                    </p>
                                    {#if selectedDateStr}
                                        <p
                                            class="text-xs text-outline italic mt-1 mb-4"
                                        >
                                            Nhấp đúp chuột hoặc bấm nút dưới đây
                                            để tạo sự kiện mới cho ngày này.
                                        </p>
                                        <button
                                            onclick={() =>
                                                openAddModal(
                                                    selectedDateStr || '',
                                                )}
                                            class="duolingo-shadow bg-secondary text-on-secondary px-4 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 shadow-sm"
                                        >
                                            Tạo sự kiện ngày này
                                        </button>
                                    {:else}
                                        <p
                                            class="text-xs text-outline italic mt-1"
                                        >
                                            Chọn một ngày hoặc bấm nút trên đầu
                                            để thêm sự kiện mới.
                                        </p>
                                    {/if}
                                </div>
                            {:else}
                                {#each displayedEvents as event (event.id)}
                                    <div
                                        class="p-4 hover:bg-surface-container-low/40 transition-colors group flex flex-col gap-2 relative"
                                    >
                                        <!-- Badges & Action Buttons -->
                                        <div
                                            class="flex justify-between items-start gap-2"
                                        >
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {typeColors[
                                                    event.type
                                                ].badge}"
                                            >
                                                {typeLabels[event.type]}
                                            </span>

                                            <!-- Action controls (visible on hover or focus) -->
                                            <div
                                                class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                <button
                                                    onclick={() =>
                                                        openEditModal(event)}
                                                    class="p-1 hover:bg-surface-variant rounded-lg text-secondary transition-colors"
                                                    title="Chỉnh sửa"
                                                >
                                                    <span
                                                        class="material-symbols-outlined text-[18px] block"
                                                        >edit</span
                                                    >
                                                </button>
                                                <button
                                                    onclick={() =>
                                                        deleteEvent(event)}
                                                    class="p-1 hover:bg-error-container rounded-lg text-error transition-colors"
                                                    title="Xóa"
                                                >
                                                    <span
                                                        class="material-symbols-outlined text-[18px] block"
                                                        >delete</span
                                                    >
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <h4
                                            class="font-bold text-sm text-on-surface group-hover:text-primary transition-colors leading-snug"
                                        >
                                            {event.title}
                                        </h4>

                                        <!-- Date & Notes -->
                                        <div class="flex flex-col gap-1">
                                            <p
                                                class="text-xs text-outline flex items-center gap-1"
                                            >
                                                <span
                                                    class="material-symbols-outlined text-[14px]"
                                                    >calendar_today</span
                                                >
                                                {formatDateVietnamese(
                                                    event.event_date,
                                                )}
                                            </p>
                                            {#if event.description}
                                                <p
                                                    class="text-xs text-on-surface-variant bg-surface-container-low/50 p-2 rounded-lg italic border-l-2 border-outline-variant/30 mt-1 whitespace-pre-line"
                                                >
                                                    {event.description}
                                                </p>
                                            {/if}
                                        </div>
                                    </div>
                                {/each}
                            {/if}
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Event Modal -->
    {#if showModal}
        <!-- Backdrop -->
        <button
            class="fixed inset-0 bg-black/45 backdrop-blur-sm z-40"
            onclick={() => (showModal = false)}
            aria-label="Đóng"
        ></button>

        <!-- Modal Box -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                class="glass-card rounded-2xl p-6 w-full max-w-md shadow-2xl border border-outline-variant/20 bg-white dark:bg-zinc-900 overflow-hidden animate-scale-up"
            >
                <!-- Modal header -->
                <div class="flex items-center justify-between mb-5">
                    <h3
                        class="font-title-lg text-headline-sm text-primary flex items-center gap-2"
                    >
                        <span class="material-symbols-outlined">
                            {isEditing ? 'edit_calendar' : 'calendar_add_on'}
                        </span>
                        {isEditing ? 'Cập Nhật Lịch' : 'Thêm Lịch Mới'}
                    </h3>
                    <button
                        onclick={() => (showModal = false)}
                        class="p-1.5 hover:bg-surface-variant rounded-full text-on-surface-variant transition-colors"
                    >
                        <span class="material-symbols-outlined block"
                            >close</span
                        >
                    </button>
                </div>

                <!-- Modal form -->
                <form
                    onsubmit={(e) => {
                        e.preventDefault();
                        submitForm();
                    }}
                    class="space-y-4"
                >
                    <!-- Title field -->
                    <div>
                        <label
                            class="block font-label-bold text-on-surface mb-1.5 text-sm"
                            >Tiêu đề / Ngày lễ</label
                        >
                        <input
                            type="text"
                            bind:value={form.title}
                            placeholder="VD: Lễ Chúa Giêsu Kitô Vua, Nghỉ hè..."
                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-3 outline-none text-sm text-on-surface"
                            required
                        />
                        {#if form.errors.title}
                            <span class="text-error text-xs mt-1 block"
                                >{form.errors.title}</span
                            >
                        {/if}
                    </div>

                    <!-- Date & Type fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Date -->
                        <div>
                            <label
                                class="block font-label-bold text-on-surface mb-1.5 text-sm"
                                >Ngày diễn ra</label
                            >
                            <input
                                type="date"
                                bind:value={form.event_date}
                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2.5 outline-none text-sm text-on-surface"
                                required
                            />
                            {#if form.errors.event_date}
                                <span class="text-error text-xs mt-1 block"
                                    >{form.errors.event_date}</span
                                >
                            {/if}
                        </div>

                        <!-- Type -->
                        <div>
                            <label
                                class="block font-label-bold text-on-surface mb-1.5 text-sm"
                                >Loại lịch / Ngày lễ</label
                            >
                            <select
                                bind:value={form.type}
                                class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 px-4 py-2.5 outline-none text-sm text-on-surface"
                                required
                            >
                                <option value="solemnity">Lễ Trọng</option>
                                <option value="feast">Lễ Kính</option>
                                <option value="memorial">Lễ Nhớ</option>
                                <option value="note">Ghi Chú / Sự Kiện</option>
                            </select>
                            {#if form.errors.type}
                                <span class="text-error text-xs mt-1 block"
                                    >{form.errors.type}</span
                                >
                            {/if}
                        </div>
                    </div>

                    <!-- Description field -->
                    <div>
                        <label
                            class="block font-label-bold text-on-surface mb-1.5 text-sm"
                            >Mô tả / Ghi chú chi tiết</label
                        >
                        <textarea
                            bind:value={form.description}
                            placeholder="Nhập thông tin chi tiết về ngày lễ hoặc ghi chú công việc cần chuẩn bị..."
                            rows="4"
                            class="w-full bg-surface-container rounded-xl border-none focus:ring-2 focus:ring-primary/30 p-4 outline-none text-sm text-on-surface resize-none"
                        ></textarea>
                        {#if form.errors.description}
                            <span class="text-error text-xs mt-1 block"
                                >{form.errors.description}</span
                            >
                        {/if}
                    </div>

                    <!-- Footer actions -->
                    <div
                        class="flex justify-end gap-3 pt-3 border-t border-outline-variant/10"
                    >
                        <button
                            type="button"
                            onclick={() => (showModal = false)}
                            class="px-5 py-2.5 rounded-xl border border-outline-variant text-on-surface-variant font-title-md text-sm hover:bg-surface-variant transition-colors"
                        >
                            Hủy
                        </button>
                        <button
                            type="submit"
                            disabled={form.processing}
                            class="duolingo-shadow-primary px-7 py-2.5 rounded-xl bg-primary text-on-primary font-title-md text-sm hover:brightness-110 active:scale-95 transition-all shadow-md disabled:opacity-50"
                        >
                            {form.processing ? 'Đang lưu...' : 'Lưu sự kiện'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    {/if}
</DashboardLayout>

<style>
    /* Styling elements for smooth transitions */
    :global(.animate-fade-in) {
        animation: fadeIn 0.25s ease-out forwards;
    }
    :global(.animate-scale-up) {
        animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes scaleUp {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
</style>
