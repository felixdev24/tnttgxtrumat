<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';

    interface CalendarEvent {
        id: number;
        event_date: string; // "YYYY-MM-DD"
        title: string;
        type: 'solemnity' | 'memorial' | 'feast' | 'note';
        description: string | null;
        created_at?: string;
        updated_at?: string;
    }

    let { posts = [], calendarEvents = [] }: { posts?: any[], calendarEvents?: CalendarEvent[] } = $props();

    // Calendar state
    let today = new Date();
    let currentYear = $state(today.getFullYear());
    let currentMonth = $state(today.getMonth() + 1);
    let selectedDateStr = $state<string | null>(
        `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
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
        const days: Array<{ day: number; dateStr: string; isCurrentMonth: boolean }> = [];

        const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
        const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
        const daysInPrevMonth = getDaysInMonth(prevYear, prevMonth);
        for (let i = startDay - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const dateStr = `${prevYear}-${String(prevMonth).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            days.push({ day: dayNum, dateStr, isCurrentMonth: false });
        }

        for (let i = 1; i <= totalDays; i++) {
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            days.push({ day: i, dateStr, isCurrentMonth: true });
        }

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

    let eventsByDate = $derived.by(() => {
        const map: Record<string, CalendarEvent[]> = {};
        calendarEvents.forEach(e => {
            const d = e.event_date.split(' ')[0];
            if (!map[d]) map[d] = [];
            map[d].push(e);
        });
        return map;
    });

    let selectedDateEvents = $derived(selectedDateStr ? (eventsByDate[selectedDateStr] || []) : []);

    function prevMonth(): void {
        if (currentMonth === 1) {
            currentMonth = 12;
            currentYear--;
        } else {
            currentMonth--;
        }
    }

    function nextMonth(): void {
        if (currentMonth === 12) {
            currentMonth = 1;
            currentYear++;
        } else {
            currentMonth++;
        }
    }

    function selectDay(dateStr: string): void {
        selectedDateStr = dateStr;
    }

    function formatTime(dateStr: string): string {
        const dateObj = new Date(dateStr);
        return `${String(dateObj.getHours()).padStart(2, '0')}:${String(dateObj.getMinutes()).padStart(2, '0')}`;
    }

    function getBorderColor(type: string): string {
        switch (type) {
            case 'solemnity': return 'border-[#c00008] dark:border-[#ffb3ad]';
            case 'memorial': return 'border-[#42617d] dark:border-[#a4c9ff]';
            case 'feast': return 'border-[#636037] dark:border-[#e2c62d]';
            case 'note': default: return 'border-gray-500 dark:border-gray-400';
        }
    }

    function getTextColor(type: string): string {
        switch (type) {
            case 'solemnity': return 'text-[#c00008] dark:text-[#ffb3ad]';
            case 'memorial': return 'text-[#42617d] dark:text-[#a4c9ff]';
            case 'feast': return 'text-[#636037] dark:text-[#e2c62d]';
            case 'note': default: return 'text-gray-500 dark:text-gray-400';
        }
    }

    function getEventBg(type: string): string {
        switch (type) {
            case 'solemnity': return 'bg-[#c00008]/10 dark:bg-[#ffb3ad]/20';
            case 'memorial': return 'bg-[#42617d]/10 dark:bg-[#a4c9ff]/20';
            case 'feast': return 'bg-[#636037]/10 dark:bg-[#e2c62d]/20';
            case 'note': default: return 'bg-gray-500/10 dark:bg-gray-400/20';
        }
    }

    function getEventDot(type: string): string {
        switch (type) {
            case 'solemnity': return 'bg-[#c00008] dark:bg-[#ffb3ad]';
            case 'memorial': return 'bg-[#42617d] dark:bg-[#a4c9ff]';
            case 'feast': return 'bg-[#636037] dark:bg-[#e2c62d]';
            case 'note': default: return 'bg-gray-500 dark:bg-gray-400';
        }
    }

    let currentSlide = $state(0);
    let slideInterval: any;

    onMount(() => {
        if (posts && posts.length > 0) {
            slideInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % posts.length;
            }, 5000);
        }
    });

    onDestroy(() => {
        if (slideInterval) clearInterval(slideInterval);
    });

    function setSlide(index: number) {
        currentSlide = index;
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = setInterval(() => {
                currentSlide = (currentSlide + 1) % posts.length;
            }, 5000);
        }
    }

    const defaultImage = "https://lh3.googleusercontent.com/aida-public/AB6AXuA-YGOtfD8BeMFCV2dDO0XTkt9DVYJ2kOl-GP_1udKzqZE0MzJCbmehru16Q6rx9sZBop1WON7fu8PumE_fXdd3rlE20pkR6WrCyZMiNC2CllmlQiZgJV8eACfDptt5bJyob7LP1Gc8TikqMKGt-Sj79VKk0lrOb4e6V6TizcA2qRScSXRBABwb889C1f8xtwGShvGQv8r480eQbOO4kM6SXP-nZPXxWJi64BhJPgDbOfT-mDcLyPpBxnLH90RgX_3rJo9dODNImqs";
</script>

<div
    class="welcome-root w-full bg-[#f8f9fa] font-['Quicksand',sans-serif] text-[#191c1d] antialiased dark:bg-[#131313] dark:text-[#e5e2e1]"
>
    <!-- Hero (không gồm header/footer theo mẫu Stitch) -->
    <section
        class="relative flex min-h-[85vh] items-center justify-center overflow-hidden px-5 md:min-h-[720px] md:px-16"
    >
        <div class="absolute inset-0 z-0">
            <img
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCNjh8c1W778vUQBjbBW8JfbgTNmiZTnQBt7_dpqLFkcRV3P_qSG_qMToNOeylBGYsodsMjIXZCDreNrdDa3GPxu8T0LX_9LBPBivO-KwyBaYdz3x0uGjHpIMpGhs9jXvj-LqhuUntKYur-3r6aomBrLOvxcX-QEVfXDxWm3dh-d0KWGvVNtjkjUUIP2COkX8oKvTzn1r1qJezPcVxHgoLz8G4hXY_D133l2d1kykrOCuFSK2C2A0hT6SqnP02j4CvmXTu4oDbpiSo"
                alt=""
                class="h-full w-full object-cover opacity-30 dark:opacity-40"
            />
            <div
                class="absolute inset-0 bg-gradient-to-t from-[#f8f9fa] via-transparent to-[#f8f9fa]/60 dark:from-[#131313] dark:to-[#131313]/50"
            ></div>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-2 text-center">
            <span
                class="welcome-glass mb-6 inline-block rounded-full border border-[#42617d]/20 px-4 py-2 text-sm font-semibold text-[#42617d] dark:border-[#ffb3ad]/30 dark:text-[#ffb3ad]"
            >
                Phong Trào Thiếu Nhi Thánh Thể
            </span>
            <h1
                class="welcome-text-glow mb-8 text-3xl leading-tight font-bold tracking-tight text-[#42617d] md:text-5xl lg:text-[64px] dark:text-[#ffb3ad]"
            >
                Cầu Nguyện - Rước Lễ
                <br />
                <span class="text-[#c00008] dark:text-[#e2c62d]">Hy Sinh - Làm Việc Tông Đồ</span>
            </h1>
            <p
                class="mx-auto mb-10 max-w-2xl text-lg font-medium leading-relaxed text-[#42474d] dark:text-[#e4beba] dark:font-normal"
            >
                Cùng nhau bước đi trong ánh sáng của Chúa Kitô, trở thành tông đồ của Chúa.
            </p>
            <div class="flex flex-col justify-center gap-4 md:flex-row">
                <Link
                    href="/login"
                    class="welcome-cta-primary inline-block rounded-xl px-10 py-4 text-lg font-bold transition-transform hover:scale-105"
                >
                    Tham Gia Ngay
                </Link>
                <button
                    type="button"
                    class="welcome-glass rounded-xl border border-[#42617d]/25 px-10 py-4 text-lg font-semibold text-[#42617d] transition-colors hover:bg-[#42617d]/10 dark:border-[#a4c9ff]/30 dark:text-[#a4c9ff] dark:hover:bg-[#a4c9ff]/10"
                >
                    Tìm Hiểu Thêm
                </button>
            </div>
        </div>
    </section>

    <!-- Bento -->
    <section class="px-5 py-12 md:px-16 md:py-12">
        <div class="mx-auto grid max-w-[1200px] grid-cols-1 gap-4 md:grid-cols-12">
            {#if posts && posts.length > 0}
                <div
                    class="welcome-glass relative flex min-h-[400px] flex-col justify-end overflow-hidden rounded-[2rem] md:col-span-8 group"
                >
                    {#each posts as post, i}
                        <div class="absolute inset-0 transition-opacity duration-1000 {i === currentSlide ? 'opacity-100 z-0' : 'opacity-0 -z-10'}">
                            <img
                                src={post.cover_image ? `/storage/${post.cover_image}` : defaultImage}
                                alt={post.title}
                                class="h-full w-full object-cover opacity-60 dark:opacity-50 transition-transform duration-1000 {i === currentSlide ? 'scale-105' : 'scale-100'}"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                        </div>
                    {/each}
                    <div class="relative z-10 p-8 w-full">
                        <div class="flex items-center justify-between mb-4">
                            <span
                                class="material-symbols-outlined text-4xl text-[#ffb3ad]"
                                aria-hidden="true">groups</span
                            >
                            <div class="flex gap-2">
                                {#each posts as _, i}
                                    <button
                                        onclick={() => setSlide(i)}
                                        class="h-2 rounded-full transition-all {i === currentSlide ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80 w-2'}"
                                        aria-label={`Go to slide ${i + 1}`}
                                    ></button>
                                {/each}
                            </div>
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white line-clamp-1">{posts[currentSlide]?.title || ''}</h3>
                        <p class="max-w-lg text-base text-white/80 line-clamp-2 mb-4">
                            {#if posts[currentSlide]}
                                {@html posts[currentSlide].content.replace(/<[^>]*>?/gm, '').substring(0, 150)}...
                            {/if}
                        </p>
                        {#if posts[currentSlide]}
                            <Link href={`/hoat-dong/${posts[currentSlide].slug}`} class="inline-flex items-center gap-1 text-sm font-bold text-white hover:underline">
                                Đọc tiếp <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                            </Link>
                        {/if}
                    </div>
                </div>
            {:else}
                <div
                    class="welcome-glass relative flex min-h-[400px] flex-col justify-end overflow-hidden rounded-[2rem] p-8 md:col-span-8 group"
                >
                    <img
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuA-YGOtfD8BeMFCV2dDO0XTkt9DVYJ2kOl-GP_1udKzqZE0MzJCbmehru16Q6rx9sZBop1WON7fu8PumE_fXdd3rlE20pkR6WrCyZMiNC2CllmlQiZgJV8eACfDptt5bJyob7LP1Gc8TikqMKGt-Sj79VKk0lrOb4e6V6TizcA2qRScSXRBABwb889C1f8xtwGShvGQv8r480eQbOO4kM6SXP-nZPXxWJi64BhJPgDbOfT-mDcLyPpBxnLH90RgX_3rJo9dODNImqs"
                        alt=""
                        class="absolute inset-0 h-full w-full object-cover opacity-20 transition-transform duration-700 group-hover:scale-110 dark:opacity-30"
                    />
                    <div class="relative z-10">
                        <span
                            class="material-symbols-outlined mb-4 text-4xl text-[#42617d] dark:text-[#ffb3ad]"
                            aria-hidden="true">groups</span
                        >
                        <h3 class="mb-2 text-2xl font-bold text-[#191c1d] dark:text-[#e5e2e1]">Sinh Hoạt Đoàn Đội</h3>
                        <p class="max-w-lg text-base text-[#42474d] dark:text-[#e4beba]">
                            Nơi các em cùng học hỏi, vui chơi và thăng tiến trong tình huynh đệ đoàn kết.
                        </p>
                    </div>
                </div>
            {/if}

            <div
                class="welcome-glass flex flex-col gap-4 rounded-[2rem] border border-[#e2c62d]/25 p-8 md:col-span-4 dark:border-[#e2c62d]/20"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#e2c62d]/15 dark:bg-[#e2c62d]/10"
                >
                    <span class="material-symbols-outlined text-3xl text-[#636037] dark:text-[#e2c62d]" aria-hidden="true"
                        >auto_stories</span
                    >
                </div>
                <h3 class="text-xl font-semibold text-[#191c1d] dark:text-[#e5e2e1]">Học Giáo Lý</h3>
                <p class="text-base text-[#42474d] dark:text-[#e4beba]">
                    Nền tảng kiến thức đức tin vững chắc cho hành trình Kitô hữu trẻ.
                </p>
                <div class="mt-auto border-t border-black/5 pt-6 dark:border-white/5">
                    <button
                        type="button"
                        class="flex cursor-pointer items-center gap-1 border-none bg-transparent p-0 text-left text-sm font-semibold text-[#636037] dark:text-[#e2c62d]"
                    >
                        Xem tài liệu
                        <span class="material-symbols-outlined text-sm" aria-hidden="true">arrow_forward</span>
                    </button>
                </div>
            </div>

            <div
                class="welcome-glass flex flex-col gap-4 rounded-[2rem] border border-[#42617d]/20 p-8 md:col-span-4 dark:border-[#a4c9ff]/20"
            >
                <div
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#42617d]/10 dark:bg-[#a4c9ff]/10"
                >
                    <span class="material-symbols-outlined text-3xl text-[#42617d] dark:text-[#a4c9ff]" aria-hidden="true"
                        >emoji_events</span
                    >
                </div>
                <h3 class="text-xl font-semibold text-[#191c1d] dark:text-[#e5e2e1]">Thi Đua &amp; Quiz</h3>
                <p class="text-base text-[#42474d] dark:text-[#e4beba]">
                    Tham gia các thử thách hằng tuần để tích lũy điểm thưởng và thăng cấp.
                </p>
            </div>

            <div
                class="welcome-glass flex flex-col gap-8 rounded-[2rem] p-8 md:col-span-8 md:flex-row md:items-stretch"
            >
                <div class="min-w-0 flex-1">
                    <div class="mb-8 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-[#191c1d] dark:text-[#e5e2e1]">
                            Lịch Sinh Hoạt - Tháng {currentMonth}/{currentYear}
                        </h3>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick={prevMonth}
                                class="rounded-full p-2 hover:bg-black/5 dark:hover:bg-white/5"
                                aria-label="Tháng trước"
                            >
                                <span class="material-symbols-outlined text-[#42474d] dark:text-[#e5e2e1]" aria-hidden="true"
                                    >chevron_left</span
                                >
                            </button>
                            <button
                                type="button"
                                onclick={nextMonth}
                                class="rounded-full p-2 hover:bg-black/5 dark:hover:bg-white/5"
                                aria-label="Tháng sau"
                            >
                                <span class="material-symbols-outlined text-[#42474d] dark:text-[#e5e2e1]" aria-hidden="true"
                                    >chevron_right</span
                                >
                            </button>
                        </div>
                    </div>
                    <div
                        class="mb-2 grid grid-cols-7 gap-2 text-center text-xs font-bold uppercase tracking-wide text-[#42474d] opacity-60 dark:text-[#e4beba]"
                    >
                        <div>CN</div>
                        <div>T2</div>
                        <div>T3</div>
                        <div>T4</div>
                        <div>T5</div>
                        <div>T6</div>
                        <div>T7</div>
                    </div>
                    <div class="grid grid-cols-7 gap-2 text-center text-base text-[#191c1d] dark:text-[#e5e2e1]">
                        {#each calendarDays as day}
                            {@const hasEvents = eventsByDate[day.dateStr] && eventsByDate[day.dateStr].length > 0}
                            {@const isSelected = selectedDateStr === day.dateStr}
                            {@const eventType = hasEvents ? eventsByDate[day.dateStr][0].type : 'note'}
                            <button
                                type="button"
                                onclick={() => selectDay(day.dateStr)}
                                class="rounded-lg p-2 transition-colors relative cursor-pointer
                                    {!day.isCurrentMonth ? 'opacity-30 dark:opacity-20 hover:bg-black/5 dark:hover:bg-white/5' : ''}
                                    {day.isCurrentMonth && !isSelected && !hasEvents ? 'hover:bg-black/5 dark:hover:bg-white/5' : ''}
                                    {isSelected ? 'border border-black/10 bg-black/[0.03] dark:border-white/10 dark:bg-white/5 font-bold' : ''}
                                    {hasEvents && !isSelected ? `border border-transparent hover:border-black/10 dark:hover:border-white/10 ${getTextColor(eventType)}` : ''}
                                    {hasEvents && isSelected ? `border ${getBorderColor(eventType)} ${getEventBg(eventType)} font-bold` : ''}
                                "
                                aria-label={day.dateStr}
                            >
                                {day.day}
                                {#if hasEvents}
                                    <div
                                        class="absolute bottom-1 left-1/2 h-1 w-1 -translate-x-1/2 rounded-full {getEventDot(eventType)}"
                                    ></div>
                                {/if}
                            </button>
                        {/each}
                    </div>
                </div>
                <div class="flex w-full flex-col gap-2 md:w-64">
                    {#if selectedDateStr}
                        <h4 class="mb-2 text-sm font-bold uppercase text-[#42474d] dark:text-[#e4beba]">
                            Sự kiện ngày {selectedDateStr.split('-')[2]}/{selectedDateStr.split('-')[1]}
                        </h4>
                    {/if}
                    
                    {#if selectedDateEvents.length > 0}
                        {#each selectedDateEvents as event}
                            <div
                                class="rounded-2xl border-l-4 {getBorderColor(event.type)} bg-[#edeeef] p-4 dark:bg-[#2a2a2a]"
                            >
                                <span class="text-xs font-bold uppercase tracking-wide {getTextColor(event.type)}"
                                    >{event.event_date.includes(' ') && !event.event_date.endsWith('00:00:00') ? formatTime(event.event_date) : 'Cả ngày'} - {new Date(event.event_date).getDay() === 0 ? 'Chủ Nhật' : 'Thứ ' + (new Date(event.event_date).getDay() + 1)}</span
                                >
                                <span class="mt-1 block text-sm font-semibold text-[#191c1d] dark:text-[#e5e2e1]"
                                    >{event.title}</span
                                >
                                {#if event.description}
                                    <p class="mt-1 text-xs text-[#42474d] dark:text-[#e4beba]">{event.description}</p>
                                {/if}
                            </div>
                        {/each}
                    {:else}
                        <div class="flex h-full items-center justify-center rounded-2xl bg-[#edeeef] p-4 text-center text-sm text-[#42474d] dark:bg-[#2a2a2a] dark:text-[#e4beba]">
                            Không có sự kiện nào.
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="px-5 pb-16 md:px-16">
        <div
            class="mx-auto flex max-w-[1200px] flex-wrap justify-between gap-8 rounded-[3rem] border border-black/5 bg-[#edeeef] p-10 dark:border-white/5 dark:bg-[#1c1b1b]"
        >
            <div class="min-w-[140px] flex-1 text-center">
                <div class="mb-1 text-[40px] font-bold text-[#42617d] dark:text-[#ffb3ad]">450+</div>
                <div class="text-sm font-semibold text-[#42474d] dark:text-[#e4beba]">Đoàn Sinh</div>
            </div>
            <div class="min-w-[140px] flex-1 text-center">
                <div class="mb-1 text-[40px] font-bold text-[#636037] dark:text-[#e2c62d]">32</div>
                <div class="text-sm font-semibold text-[#42474d] dark:text-[#e4beba]">Huynh Trưởng</div>
            </div>
            <div class="min-w-[140px] flex-1 text-center">
                <div class="mb-1 text-[40px] font-bold text-[#42617d] dark:text-[#a4c9ff]">12</div>
                <div class="text-sm font-semibold text-[#42474d] dark:text-[#e4beba]">Lớp Giáo Lý</div>
            </div>
            <div class="min-w-[140px] flex-1 text-center">
                <div class="mb-1 text-[40px] font-bold text-[#191c1d] dark:text-[#e5e2e1]">20+</div>
                <div class="text-sm font-semibold text-[#42474d] dark:text-[#e4beba]">Hoạt Động/Năm</div>
            </div>
    </div>
    </section>
</div>

<style>
    :global(.welcome-root .material-symbols-outlined) {
        font-family: 'Material Symbols Outlined', sans-serif;
        font-weight: 400;
        font-style: normal;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
    }

    .welcome-glass {
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .welcome-glass {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .welcome-cta-primary {
        background: #c00008;
        color: #ffffff;
        box-shadow:
            inset 0 0 20px rgba(239, 68, 68, 0.12),
            0 10px 30px rgba(192, 0, 8, 0.2);
    }

    :global(.dark) .welcome-cta-primary {
        background: #ff5451;
        color: #5c0008;
        box-shadow:
            inset 0 0 20px rgba(239, 68, 68, 0.1),
            0 10px 30px rgba(239, 68, 68, 0.15);
    }

    .welcome-text-glow {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .welcome-text-glow {
        text-shadow: 0 0 15px rgba(255, 179, 173, 0.45);
    }
</style>
