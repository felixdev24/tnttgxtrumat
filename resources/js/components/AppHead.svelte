<script lang="ts">
    import ThemeToggle from '@/components/ThemeToggle.svelte';
    import { Link, usePage, useForm } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    type NavItem = {
        label: string;
        href: string;
    };

    let {
        title = '',
        children,
        showSiteHeader = true,
        headerSpacer = true,
        brandLine = 'Thiếu Nhi Thánh Thể',
        parishName = 'Giáo Xứ Trù Mật',
        logoSrc = '/apple-touch-icon.png',
        navItems = [
            { label: 'Trang chủ', href: '/' },
            { label: 'Hoạt động', href: '/hoat-dong' },
            { label: 'Đố vui', href: '/quizzes' },
            { label: 'Dashboard', href: '/dashboard' }
        ] satisfies NavItem[],
    }: {
        title?: string;
        children?: Snippet;
        showSiteHeader?: boolean;
        headerSpacer?: boolean;
        brandLine?: string;
        parishName?: string;
        logoSrc?: string;
        navItems?: NavItem[];
    } = $props();

    const page = usePage();

    const filteredNavItems = $derived(
        navItems.filter((item) => {
            if (item.href === '/dashboard') {
                return page.props.auth.user && ['huynh_truong', 'super_admin'].includes(page.props.auth.user.role);
            }
            return true;
        }),
    );

    let mobileNavOpen = $state(false);
    let profileOpen = $state(false);
    let activeProfileTab = $state('info');
    let showCurrentPassword = $state(false);
    let showNewPassword = $state(false);
    let showConfirmPassword = $state(false);

    let passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function updatePassword() {
        passwordForm.put('/account/password', {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
                profileOpen = false;
            }
        });
    }

    function toggleProfile() {
        profileOpen = !profileOpen;
        if (profileOpen) {
            activeProfileTab = 'info';
            passwordForm.reset();
            passwordForm.clearErrors();
        }
    }

    function handleLogout() {
        profileOpen = false;
        import('@inertiajs/svelte').then(({ router }) => {
            router.post('/logout');
        });
    }

    const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);

    function pathnameFromPageUrl(url: string): string {
        if (!url) {
            return '/';
        }
        try {
            return new URL(
                url,
                typeof window !== 'undefined'
                    ? window.location.origin
                    : 'http://localhost',
            ).pathname;
        } catch {
            const path = url.split('?')[0] ?? '/';

            return path.startsWith('/') ? path : `/${path}`;
        }
    }

    const currentPath = $derived(pathnameFromPageUrl(page.url));

    function navItemIsActive(href: string): boolean {
        if (href === '#') {
            return false;
        }
        if (href === '/') {
            return currentPath === '/' || currentPath === '';
        }

        return currentPath === href || currentPath.startsWith(`${href}/`);
    }

    function closeMobileNav(): void {
        mobileNavOpen = false;
    }

    function toggleMobileNav(): void {
        mobileNavOpen = !mobileNavOpen;
    }
</script>

<svelte:head>
    <title>{fullTitle}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,600;0,700;1,400&family=Quicksand:wght@500;600;700&display=swap"
        rel="stylesheet"
    />
    {@render children?.()}
</svelte:head>

{#if showSiteHeader}
    <header
        class="site-header fixed top-0 right-0 left-0 z-50 border-b border-white/20 bg-white/80 shadow-sm backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/80 dark:shadow-none"
    >
        <div
            class="mx-auto flex h-20 w-full max-w-[1200px] items-center justify-between gap-3 px-4 py-2 md:px-10"
        >
            <div class="flex min-w-0 items-center gap-2 md:gap-3">
                <img
                    src={logoSrc}
                    alt={brandLine}
                    class="h-10 w-10 md:h-12 md:w-12 shrink-0 rounded-full object-contain shadow-sm"
                    width="48"
                    height="48"
                />
                <div class="min-w-0 flex flex-col justify-center leading-tight">
                    <span
                        class="block truncate font-['Quicksand',sans-serif] text-base font-bold tracking-tight text-[#c00008] sm:text-lg md:text-xl dark:text-[#ffb4a9]"
                    >
                        {brandLine}
                    </span>
                    <span
                        class="hidden sm:block truncate font-['Nunito',sans-serif] text-[10px] md:text-[12px] font-medium uppercase tracking-wider text-[#42617d]/80 dark:text-[#aacaea]/90"
                    >
                        {parishName}
                    </span>
                </div>
            </div>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Chính">
                {#each filteredNavItems as item (item.href + item.label)}
                    {@const active = navItemIsActive(item.href)}
                    {#if item.href === '/'}
                        <Link
                            href={item.href}
                            class="font-['Nunito',sans-serif] text-base transition-colors {active
                                ? 'border-b-4 border-[#42617d] pb-1 font-bold text-[#42617d] dark:border-[#aacaea] dark:text-[#aacaea]'
                                : 'text-[#42474d] hover:text-[#42617d] dark:text-zinc-300 dark:hover:text-[#aacaea]'}"
                        >
                            {item.label}
                        </Link>
                    {:else}
                        <a
                            href={item.href}
                            class="font-['Nunito',sans-serif] text-base transition-colors {active
                                ? 'border-b-4 border-[#42617d] pb-1 font-bold text-[#42617d] dark:border-[#aacaea] dark:text-[#aacaea]'
                                : 'text-[#42474d] hover:text-[#42617d] dark:text-zinc-300 dark:hover:text-[#aacaea]'}"
                        >
                            {item.label}
                        </a>
                    {/if}
                {/each}
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                <button
                    type="button"
                    class="rounded-xl p-2 transition-all hover:bg-[#e7e8e9]/50 md:hidden dark:hover:bg-white/10"
                    aria-expanded={mobileNavOpen}
                    aria-controls="site-header-mobile-nav"
                    onclick={toggleMobileNav}
                >
                    <span
                        class="material-symbols-outlined text-[#42474d] dark:text-zinc-300"
                        aria-hidden="true"
                        >{mobileNavOpen ? 'close' : 'menu'}</span
                    >
                    <span class="sr-only">Mở hoặc đóng menu</span>
                </button>

                <ThemeToggle />

                {#if page.props.auth.user}
                    <button
                        type="button"
                        onclick={toggleProfile}
                        class="flex items-center gap-2 rounded-full border border-gray-200 bg-white p-1 pr-3 transition-all hover:bg-gray-50 dark:border-gray-700 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                    >
                        <img
                            src={page.props.auth.user.avatar ||
                                'https://ui-avatars.com/api/?name=' +
                                    encodeURIComponent(
                                        page.props.auth.user.name,
                                    ) +
                                    '&color=7F9CF5&background=EBF4FF'}
                            alt="Avatar"
                            class="h-8 w-8 rounded-full object-cover"
                        />
                        <span
                            class="hidden text-sm font-semibold text-gray-700 sm:block dark:text-gray-200"
                        >
                            {page.props.auth.user.name}
                        </span>
                    </button>
                {:else}
                    <Link
                        href="/login"
                        class="header-cta-duo hidden items-center justify-center rounded-xl bg-[#c00008] px-6 py-2 font-['Quicksand',sans-serif] text-sm font-bold text-white transition-transform active:scale-95 sm:flex dark:bg-[#c00008]"
                    >
                        Tham Gia
                    </Link>
                {/if}
            </div>
        </div>

        <div
            id="site-header-mobile-nav"
            class="border-t border-white/20 bg-white/95 px-4 py-3 backdrop-blur-xl md:hidden dark:border-white/10 dark:bg-zinc-900/95 {!mobileNavOpen
                ? 'hidden'
                : ''}"
        >
            <nav class="flex flex-col gap-1" aria-label="Chính (di động)">
                {#each filteredNavItems as item (item.label + '-m')}
                    {@const active = navItemIsActive(item.href)}
                    {#if item.href === '/'}
                        <Link
                            href={item.href}
                            class="rounded-xl px-3 py-2 font-['Nunito',sans-serif] text-base transition-colors {active
                                ? 'bg-[#a7c7e7]/40 font-bold text-[#34536f] dark:bg-white/10 dark:text-[#aacaea]'
                                : 'text-[#42474d] hover:bg-[#e7e8e9]/60 dark:text-zinc-300 dark:hover:bg-white/10'}"
                            onclick={closeMobileNav}
                        >
                            {item.label}
                        </Link>
                    {:else}
                        <a
                            href={item.href}
                            class="rounded-xl px-3 py-2 font-['Nunito',sans-serif] text-base transition-colors {active
                                ? 'bg-[#a7c7e7]/40 font-bold text-[#34536f] dark:bg-white/10 dark:text-[#aacaea]'
                                : 'text-[#42474d] hover:bg-[#e7e8e9]/60 dark:text-zinc-300 dark:hover:bg-white/10'}"
                            onclick={closeMobileNav}
                        >
                            {item.label}
                        </a>
                    {/if}
                {/each}

                {#if !page.props.auth.user}
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-zinc-700">
                        <Link
                            href="/login"
                            class="header-cta-duo flex items-center justify-center rounded-xl bg-[#c00008] px-6 py-2.5 font-['Quicksand',sans-serif] text-sm font-bold text-white transition-transform active:scale-95 dark:bg-[#c00008]"
                            onclick={closeMobileNav}
                        >
                            Tham Gia
                        </Link>
                    </div>
                {/if}
            </nav>
        </div>
    </header>

    <!-- Profile Modal -->
    {#if profileOpen && page.props.auth.user}
        <!-- Backdrop -->
        <!-- svelte-ignore a11y_click_events_have_key_events -->
        <!-- svelte-ignore a11y_no_static_element_interactions -->
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity"
            onclick={toggleProfile}
            aria-hidden="true"
        >
            <!-- Modal Content -->
            <!-- svelte-ignore a11y_click_events_have_key_events -->
            <!-- svelte-ignore a11y_no_static_element_interactions -->
            <div
                class="relative w-full max-w-md scale-100 transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all dark:bg-zinc-900"
                onclick={(e) => e.stopPropagation()}
            >
                <button
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    onclick={toggleProfile}
                >
                    <span class="material-symbols-outlined">close</span>
                </button>

                <div class="flex flex-col items-center pb-4 text-center">
                    <img
                        src={page.props.auth.user.avatar ||
                            'https://ui-avatars.com/api/?name=' +
                                encodeURIComponent(page.props.auth.user.name) +
                                '&color=7F9CF5&background=EBF4FF'}
                        alt="Avatar"
                        class="mb-3 h-24 w-24 rounded-full object-cover shadow-sm"
                    />
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        {page.props.auth.user.name}
                    </h3>
                    <p
                        class="text-sm font-medium text-[#c00008] dark:text-[#ffb3ad]"
                    >
                        @{page.props.auth.user.username}
                    </p>
                </div>

                <div class="flex gap-4 mb-4 border-b border-outline-variant/20">
                    <button
                        onclick={() => (activeProfileTab = 'info')}
                        class="pb-2 px-2 font-label-bold transition-all {activeProfileTab === 'info' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-on-surface'}"
                    >
                        Thông tin
                    </button>
                    <button
                        onclick={() => (activeProfileTab = 'password')}
                        class="pb-2 px-2 font-label-bold transition-all {activeProfileTab === 'password' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-on-surface'}"
                    >
                        Đổi mật khẩu
                    </button>
                </div>

                {#if activeProfileTab === 'info'}
                    <div class="mt-4 space-y-3 rounded-xl bg-gray-50 p-4 text-sm dark:bg-zinc-800/50">
                    {#if ['huynh_truong', 'super_admin'].includes(page.props.auth.user.role)}
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Chức vụ</span
                            >
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >Huynh trưởng</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Cấp bậc</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.rank || 'N/A'}</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Số năm HĐ</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.years_of_activity || 0} năm</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Email</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.email}</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >SĐT</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.phone || 'N/A'}</span
                            >
                        </div>
                    {:else}
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Vai trò</span
                            >
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >Giáo lý sinh</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Cấp</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.grade_level ||
                                    'N/A'}</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >Ngành</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.branch || 'N/A'}</span
                            >
                        </div>
                        <div
                            class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                        >
                            <span class="text-gray-500 dark:text-gray-400"
                                >SĐT Phụ huynh</span
                            >
                            <span
                                class="font-medium text-gray-900 dark:text-white"
                                >{page.props.auth.user.parent_phone ||
                                    'N/A'}</span
                            >
                        </div>
                    {/if}

                    <div
                        class="flex justify-between border-b border-gray-200 pb-2 dark:border-zinc-700"
                    >
                        <span class="text-gray-500 dark:text-gray-400"
                            >Ngày sinh</span
                        >
                        <span class="font-medium text-gray-900 dark:text-white"
                            >{page.props.auth.user.dob
                                ? new Date(
                                      page.props.auth.user.dob as string,
                                  ).toLocaleDateString('vi-VN')
                                : 'N/A'}</span
                        >
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400"
                            >Địa chỉ</span
                        >
                        <span class="font-medium text-gray-900 dark:text-white"
                            >{page.props.auth.user.address || 'N/A'}</span
                        >
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        onclick={handleLogout}
                        class="w-full rounded-xl bg-red-500 px-4 py-2.5 text-center text-sm font-semibold text-white transition-colors hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                    >
                        Đăng Xuất
                    </button>
                </div>
                {:else if activeProfileTab === 'password'}
                    <form onsubmit={(e) => { e.preventDefault(); updatePassword(); }} class="space-y-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Mật khẩu hiện tại</label>
                            <div class="relative">
                                <input type={showCurrentPassword ? "text" : "password"} bind:value={passwordForm.current_password} required class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800" />
                                <button
                                    type="button"
                                    onclick={() => (showCurrentPassword = !showCurrentPassword)}
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 flex items-center justify-center transition-colors"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        {showCurrentPassword ? 'visibility_off' : 'visibility'}
                                    </span>
                                </button>
                            </div>
                            {#if passwordForm.errors.current_password}<p class="text-xs text-error mt-1">{passwordForm.errors.current_password}</p>{/if}
                        </div>

                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Mật khẩu mới</label>
                            <div class="relative">
                                <input type={showNewPassword ? "text" : "password"} bind:value={passwordForm.password} required minlength="8" class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800" />
                                <button
                                    type="button"
                                    onclick={() => (showNewPassword = !showNewPassword)}
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 flex items-center justify-center transition-colors"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        {showNewPassword ? 'visibility_off' : 'visibility'}
                                    </span>
                                </button>
                            </div>
                            {#if passwordForm.errors.password}<p class="text-xs text-error mt-1">{passwordForm.errors.password}</p>{/if}
                        </div>

                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Xác nhận mật khẩu mới</label>
                            <div class="relative">
                                <input type={showConfirmPassword ? "text" : "password"} bind:value={passwordForm.password_confirmation} required minlength="8" class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800" />
                                <button
                                    type="button"
                                    onclick={() => (showConfirmPassword = !showConfirmPassword)}
                                    class="absolute top-1/2 right-3 -translate-y-1/2 text-on-surface-variant hover:text-on-surface p-1 flex items-center justify-center transition-colors"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[18px]">
                                        {showConfirmPassword ? 'visibility_off' : 'visibility'}
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="p-3 bg-error-container/20 border border-error-container rounded-xl text-xs text-error flex items-center gap-2 mt-2">
                            <span class="material-symbols-outlined text-[18px]">warning</span>
                            Sẽ tự động đăng xuất.
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="submit"
                                disabled={passwordForm.processing}
                                class="w-full rounded-xl font-label-bold bg-primary px-4 py-2.5 text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-sm disabled:opacity-50"
                            >
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    {/if}
{/if}

{#if showSiteHeader && headerSpacer}
    <div class="h-20 shrink-0" aria-hidden="true"></div>
{/if}

<style>
    :global(.material-symbols-outlined) {
        display: inline-block;
        line-height: 1;
        font-family: 'Material Symbols Outlined', sans-serif;
        font-size: 24px;
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
        letter-spacing: normal;
        text-transform: none;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
    }

    :global(.header-cta-duo) {
        box-shadow: 0 4px 0 0 rgba(192, 0, 8, 0.35);
    }

    :global(.header-cta-duo:active) {
        box-shadow: 0 0 0 0 rgba(192, 0, 8, 0.35);
        transform: translateY(4px);
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
</style>
