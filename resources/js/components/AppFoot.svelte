<script lang="ts">
    import { page, Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { onMount, onDestroy } from 'svelte';

    type FooterLink = {
        label: string;
        href: string;
    };

    let {
        showSiteFooter = true,
        brandLine = 'Thi\u1ebfu Nhi Th\u00e1nh Th\u1ec3',
        copyright,
        children,
    }: {
        showSiteFooter?: boolean;
        brandLine?: string;
        copyright?: string;
        children?: Snippet;
    } = $props();

    let globalSettings = $derived((page.props as any).global_settings || {});

    let footerLinks = $derived([
        { label: 'Gi\u00e1o x\u1ee9', href: globalSettings.footer_link_giao_xu || 'https://www.facebook.com/giaoxuTruMat' },
        { label: 'Facebook', href: globalSettings.footer_link_facebook || 'https://www.facebook.com/tnttgxtrumat' },
        { label: 'Li\u00ean h\u1ec7', href: globalSettings.footer_link_lien_he || 'tel:0868107004' },
        { label: 'T\u00e0i li\u1ec7u', href: globalSettings.footer_link_tai_lieu || '#' },
    ]);

    const year = new Date().getFullYear();

    const displayCopyright = $derived(
        copyright ?? `\u00a9 ${year} Thi\u1ebfu Nhi Th\u00e1nh Th\u1ec3 Gi\u00e1o x\u1ee9 Tr\u00f9 M\u1eadt.`,
    );

    // Digital clock - Vietnam time (UTC+7)
    let clockTime = $state('');
    let clockDate = $state('');
    let clockInterval: ReturnType<typeof setInterval>;

    function updateClock() {
        const now = new Date();
        const vnTime = new Intl.DateTimeFormat('vi-VN', {
            timeZone: 'Asia/Ho_Chi_Minh',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        }).format(now);

        const vnDate = new Intl.DateTimeFormat('vi-VN', {
            timeZone: 'Asia/Ho_Chi_Minh',
            weekday: 'long',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }).format(now);

        clockTime = vnTime;
        clockDate = vnDate;
    }

    onMount(() => {
        updateClock();
        clockInterval = setInterval(updateClock, 1000);
    });

    onDestroy(() => {
        if (clockInterval) clearInterval(clockInterval);
    });
</script>

{#if showSiteFooter}
    <footer
        class="mt-16 w-full rounded-t-3xl bg-[#edeeef] dark:bg-zinc-800/90"
        aria-label="Ch\u00e2n trang"
    >
        <div
            class="mx-auto flex w-full max-w-[1200px] flex-col items-center gap-6 px-4 py-8 md:flex-row md:items-start md:justify-between md:px-10 md:py-8"
        >
            <div
                class="flex max-w-md flex-col items-center gap-2 text-center md:items-start md:text-left"
            >
                <span
                    class="font-['Quicksand',sans-serif] text-xl font-bold text-[#c00008] dark:text-[#ffb4a9]"
                >
                    {brandLine}
                </span>
                <p
                    class="font-['Nunito',sans-serif] text-base leading-relaxed text-[#42474d] dark:text-zinc-300"
                >
                    {displayCopyright}
                </p>
                {@render children?.()}
            </div>

            <nav
                class="flex flex-wrap items-center justify-center gap-6 md:justify-end"
                aria-label="Li\u00ean k\u1ebft ch\u00e2n trang"
            >
                {#each footerLinks as item (item.label)}
                    {#if item.href !== '#' && item.href.startsWith('/')}
                        <Link
                            href={item.href}
                            class="font-['Nunito',sans-serif] text-base text-[#42474d] underline decoration-[#c00008] decoration-2 underline-offset-4 transition-colors hover:text-[#c00008] dark:text-zinc-300 dark:decoration-[#ffb4a9] dark:hover:text-[#ffb4a9]"
                        >
                            {item.label}
                        </Link>
                    {:else}
                        <a
                            href={item.href}
                            class="font-['Nunito',sans-serif] text-base text-[#42474d] underline decoration-[#c00008] decoration-2 underline-offset-4 transition-colors hover:text-[#c00008] dark:text-zinc-300 dark:decoration-[#ffb4a9] dark:hover:text-[#ffb4a9]"
                        >
                            {item.label}
                        </a>
                    {/if}
                {/each}
            </nav>
        </div>

        <!-- Digital Clock Bar -->
        <div class="border-t border-black/5 dark:border-white/5 bg-[#e4e5e6] dark:bg-zinc-900/60">
            <div class="mx-auto flex w-full max-w-[1200px] items-center justify-between gap-4 px-4 py-3 md:px-10">
                <!-- Date -->
                <p class="font-['Nunito',sans-serif] text-xs text-[#42474d]/70 dark:text-zinc-500 capitalize hidden sm:block">
                    {clockDate}
                </p>

                <!-- Clock -->
                <div class="mx-auto sm:mx-0 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-[#c00008] dark:text-[#ffb4a9]" aria-hidden="true">schedule</span>
                    <div class="font-mono font-bold tracking-widest text-[#42474d] dark:text-zinc-200 text-sm tabular-nums clock-digits">
                        {clockTime}
                    </div>
                    <span class="text-[10px] font-semibold text-[#42617d]/60 dark:text-zinc-500 uppercase tracking-wider">ICT +7</span>
                </div>
            </div>
        </div>
    </footer>
{/if}

<style>
    .clock-digits {
        font-size: 0.85rem;
        letter-spacing: 0.12em;
        font-feature-settings: "tnum";
    }
</style>

