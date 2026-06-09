<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    type FooterLink = {
        label: string;
        href: string;
    };

    let {
        showSiteFooter = true,
        brandLine = 'Thiếu Nhi Thánh Thể',
        copyright,
        footerLinks = [
            { label: 'Giáo xứ', href: '#' },
            { label: 'Facebook', href: '#' },
            { label: 'Liên hệ', href: '#' },
            { label: 'Tài liệu', href: '#' },
        ] satisfies FooterLink[],
        children,
    }: {
        showSiteFooter?: boolean;
        brandLine?: string;
        copyright?: string;
        footerLinks?: FooterLink[];
        children?: Snippet;
    } = $props();

    const year = new Date().getFullYear();

    const displayCopyright = $derived(
        copyright ?? `© ${year} Thiếu Nhi Thánh Thể Giáo xứ Trù Mật.`,
    );
</script>

{#if showSiteFooter}
    <footer
        class="mt-16 w-full rounded-t-3xl bg-[#edeeef] dark:bg-zinc-800/90"
        aria-label="Chân trang"
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
                aria-label="Liên kết chân trang"
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
    </footer>
{/if}
