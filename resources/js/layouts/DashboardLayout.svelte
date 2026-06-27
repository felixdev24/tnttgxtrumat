<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { Link } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    let { children } = $props();

    let user = $derived(page.props.auth?.user);

    // Some simple mobile nav toggle state if needed
    let mobileNavOpen = $state(false);

    function toggleMobileNav() {
        mobileNavOpen = !mobileNavOpen;
    }
</script>

<div class="font-sans">
    <!-- Top AppBar removed to use AppHead.svelte instead -->
    <div class="flex min-h-screen relative">
        <!-- Mobile Overlay -->
        {#if mobileNavOpen}
            <!-- svelte-ignore a11y_click_events_have_key_events -->
            <!-- svelte-ignore a11y_no_static_element_interactions -->
            <div class="fixed inset-0 z-40 bg-zinc-900/50 backdrop-blur-sm md:hidden" onclick={toggleMobileNav}></div>
        {/if}

        <!-- SideNavBar -->
        <aside
            class="{mobileNavOpen ? 'translate-x-0' : '-translate-x-full'} md:translate-x-0 transition-transform duration-300 ease-in-out z-50 flex flex-col w-64 fixed left-0 top-[80px] bottom-0 bg-white dark:bg-zinc-900 p-6 border-r border-zinc-200 dark:border-zinc-800"
        >
            <div class="flex flex-col gap-stack-sm mb-stack-lg">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center"
                    >
                        <span class="material-symbols-outlined text-primary"
                            >admin_panel_settings</span
                        >
                    </div>
                    <div>
                        <h3 class="font-title-md text-label-bold text-primary">
                            Quản Trị
                        </h3>
                        <p class="text-xs text-on-surface-variant">Liên Đoàn</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 flex flex-col gap-1" onclick={() => window.innerWidth < 768 && (mobileNavOpen = false)}>
                <Link
                    href="/dashboard"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url === '/dashboard'
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-title-md text-[16px]">Thống Kê</span>
                </Link>
                <Link
                    href="/dashboard/doan-sinh"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/doan-sinh')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span
                        class="material-symbols-outlined"
                        style="font-variation-settings: 'FILL' {page.url.startsWith('/dashboard/doan-sinh') ? '1' : '0'};"
                        >group</span
                    >
                    <span class="font-title-md text-[16px]">Đoàn Sinh</span>
                </Link>
                <Link
                    href="/dashboard/huynh-truong"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/huynh-truong')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span
                        class="material-symbols-outlined"
                        style="font-variation-settings: 'FILL' {page.url.startsWith('/dashboard/huynh-truong') ? '1' : '0'};"
                        >supervisor_account</span
                    >
                    <span class="font-title-md text-[16px]">Huynh Trưởng</span>
                </Link>
                <Link
                    href="/dashboard/posts"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/posts')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span
                        class="material-symbols-outlined"
                        style="font-variation-settings: 'FILL' {page.url.startsWith('/dashboard/posts') ? '1' : '0'};"
                        >description</span
                    >
                    <span class="font-title-md text-[16px]"
                        >Quản lý bài viết</span
                    >
                </Link>
                <Link
                    href="/dashboard/attendance"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/attendance')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span
                        class="material-symbols-outlined"
                        style="font-variation-settings: 'FILL' {page.url.startsWith('/dashboard/attendance') ? '1' : '0'};"
                        >fact_check</span
                    >
                    <span class="font-title-md text-[16px]">Điểm Danh</span>
                </Link>
                <Link
                    href="/dashboard/quizzes"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/quizzes')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span class="material-symbols-outlined">menu_book</span>
                    <span class="font-title-md text-[16px]"
                        >Đố Vui Kinh Thánh</span
                    >
                </Link>
                <Link
                    href="/dashboard/calendar"
                    class="flex items-center gap-stack-md p-stack-md rounded-xl transition-all {page.url.startsWith('/dashboard/calendar')
                        ? 'bg-primary-container/30 text-primary font-bold shadow-sm'
                        : 'text-on-surface-variant hover:bg-surface-container-high'}"
                >
                    <span class="material-symbols-outlined">calendar_month</span>
                    <span class="font-title-md text-[16px]"
                        >Lịch Phụng Vụ</span
                    >
                </Link>
            </nav>

            <div
                class="mt-auto pb-8 flex flex-col gap-1 border-t border-outline-variant/10 pt-4"
            >
                <Link
                    href="#"
                    class="flex items-center gap-stack-md p-stack-md text-on-surface-variant hover:bg-surface-container-high rounded-xl transition-all"
                >
                    <span class="material-symbols-outlined">settings</span>
                    <span class="font-title-md text-[16px]">Cài Đặt</span>
                </Link>
                <button
                    onclick={() => {
                        import('@inertiajs/svelte').then(({ router }) =>
                            router.post('/logout'),
                        );
                    }}
                    class="w-full flex items-center gap-stack-md p-stack-md text-error hover:bg-error-container rounded-xl transition-all"
                >
                    <span class="material-symbols-outlined">logout</span>
                    <span class="font-title-md text-[16px]">Đăng Xuất</span>
                </button>
            </div>
        </aside>

        <main class="flex-1 md:ml-64 pt-4 md:pt-8 flex flex-col min-w-0">
            <!-- Mobile Dashboard Menu Toggle -->
            <div class="md:hidden flex items-center gap-3 px-4 pb-4 border-b border-outline-variant/10 mb-4 sticky top-[80px] bg-white/80 dark:bg-zinc-900/80 backdrop-blur-md z-30">
                <button onclick={toggleMobileNav} class="p-2 -ml-2 rounded-xl text-on-surface hover:bg-surface-variant transition-colors" aria-label="Mở menu Dashboard">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <h2 class="font-title-md text-lg text-primary">Menu Chức Năng</h2>
            </div>

            <div class="px-0 sm:px-0">
                {@render children()}
            </div>
        </main>
    </div>
</div>

<style>
    /* CSS specific to Dashboard Layout */
    :global(.glass-card) {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    @media (prefers-color-scheme: dark) {
        :global(.dark .glass-card) {
            background: rgba(24, 24, 27, 0.75); /* zinc-900 */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    }
    :global(.duolingo-shadow) {
        box-shadow: 0 4px 0 0 rgba(0, 0, 0, 0.1);
    }
    :global(.duolingo-shadow-primary) {
        box-shadow: 0 4px 0 0 #a60006;
    }
    :global(.duolingo-shadow-primary:active) {
        box-shadow: none;
        transform: translateY(4px);
    }
    :global(.material-symbols-outlined) {
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
        vertical-align: middle;
    }
</style>
