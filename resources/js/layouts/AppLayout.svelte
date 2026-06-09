<script lang="ts">
    import AppFoot from '@/components/AppFoot.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import SplashLoader from '@/components/SplashLoader.svelte';
    import type { Snippet } from 'svelte';

    let {
        children,
        title = '',
    }: {
        children?: Snippet;
        title?: string;
    } = $props();
</script>

<div
    class="flex min-h-screen flex-col bg-[#f8f9fa] text-[#191c1d] dark:bg-[#131313] dark:text-[#e5e2e1]"
>
    <AppHead {title} />
    <main class="min-w-0 flex-1 w-full">
        {@render children?.()}
    </main>
    <AppFoot />
</div>

<SplashLoader />
