<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { Link } from '@inertiajs/svelte';
    import { fly, fade } from 'svelte/transition';

    let { leaderboard, userTotalScore, userRank } = $props();
    let user = $derived(page.props.auth?.user);

    // Split leaderboard into top 3 and others
    let top3 = $derived(leaderboard.slice(0, 3));
    let others = $derived(leaderboard.slice(3));

    // Map rank to specific styling
    const rankColors = {
        1: 'bg-gradient-to-br from-yellow-300 to-yellow-500 text-yellow-900 border-yellow-200',
        2: 'bg-gradient-to-br from-gray-200 to-gray-400 text-gray-800 border-gray-100',
        3: 'bg-gradient-to-br from-orange-300 to-orange-500 text-orange-900 border-orange-200',
    };

    const rankIcons = {
        1: 'trophy',
        2: 'military_tech',
        3: 'workspace_premium',
    };
</script>

<svelte:head>
    <title>Bảng Xếp Hạng Đố Vui - Thiếu Nhi Thánh Thể</title>
</svelte:head>

<main class="pt-[100px] pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-md mx-auto min-h-screen relative font-body-md">
    
    <div class="flex items-center gap-4 mb-stack-lg">
        <Link href="/quizzes" class="w-10 h-10 rounded-full glass-card flex items-center justify-center hover:bg-white/50 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-on-surface">arrow_back</span>
        </Link>
        <div>
            <h1 class="font-headline-lg text-headline-lg-mobile text-primary">Bảng Xếp Hạng Đố Vui</h1>
            <p class="text-on-surface-variant text-sm mt-1">Những nhà thông thái Kinh Thánh xuất sắc nhất</p>
        </div>
    </div>

    {#if user}
        <div class="glass-card p-4 rounded-2xl mb-stack-lg flex flex-col sm:flex-row items-center justify-between gap-4 shadow-md border-primary/20" in:fly={{ y: 20, duration: 400 }}>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center font-bold text-xl shadow-inner border border-primary/20">
                    {userRank ? `#${userRank}` : '-'}
                </div>
                <div>
                    <p class="font-label-bold text-primary">Thành tích của bạn</p>
                    <p class="text-on-surface-variant text-sm">{user.name}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-surface-container-low px-4 py-2 rounded-xl border border-outline-variant/30">
                <span class="material-symbols-outlined text-tertiary" style="font-variation-settings: 'FILL' 1;">monetization_on</span>
                <span class="font-headline-md text-primary">{userTotalScore.toLocaleString()} điểm</span>
            </div>
        </div>
    {/if}

    {#if leaderboard.length === 0}
        <div class="glass-card p-16 text-center rounded-3xl shadow-sm">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4 block">sentiment_dissatisfied</span>
            <h2 class="font-headline-md text-primary mb-2">Chưa có ai tham gia</h2>
            <p class="text-on-surface-variant">Hãy là người đầu tiên ghi điểm trên bảng xếp hạng nhé!</p>
        </div>
    {:else}
        <!-- Top 3 Podium -->
        <div class="flex flex-wrap justify-center items-end gap-2 sm:gap-4 md:gap-6 mb-16 pt-8">
            {#each top3 as rankUser}
                <div 
                    class="flex flex-col items-center {rankUser.rank === 1 ? 'order-2 w-[110px] sm:w-[140px]' : rankUser.rank === 2 ? 'order-1 w-[90px] sm:w-[120px]' : 'order-3 w-[90px] sm:w-[120px]'}"
                    in:fly={{ y: 50, duration: 600, delay: rankUser.rank * 100 }}
                >
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 mb-3">
                        <div class="w-full h-full rounded-full bg-white shadow-md p-1 border-4 {rankUser.rank === 1 ? 'border-yellow-400' : rankUser.rank === 2 ? 'border-gray-300' : 'border-orange-400'} z-10 relative flex items-center justify-center overflow-hidden">
                            {#if rankUser.avatar}
                                <img src={rankUser.avatar} alt={rankUser.name} class="w-full h-full object-cover rounded-full" />
                            {:else}
                                <span class="material-symbols-outlined text-3xl text-surface-variant">person</span>
                            {/if}
                        </div>
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-8 h-8 rounded-full flex items-center justify-center shadow-sm z-20 {rankColors[rankUser.rank as keyof typeof rankColors]}">
                            <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 1;">{rankIcons[rankUser.rank as keyof typeof rankIcons]}</span>
                        </div>
                    </div>
                    
                    <div class="w-full {rankColors[rankUser.rank as keyof typeof rankColors]} {rankUser.rank === 1 ? 'h-32 sm:h-40' : rankUser.rank === 2 ? 'h-24 sm:h-32' : 'h-20 sm:h-24'} rounded-t-2xl shadow-lg border-t border-l border-r flex flex-col items-center justify-start pt-3 px-1 text-center relative overflow-hidden">
                        <div class="absolute inset-0 bg-white/20"></div>
                        <p class="font-bold text-xs sm:text-sm line-clamp-2 w-full relative z-10 px-1 leading-tight">{rankUser.name}</p>
                        <p class="font-headline-sm text-sm sm:text-base mt-auto mb-3 relative z-10 flex items-center gap-1">
                            {rankUser.score}
                        </p>
                    </div>
                </div>
            {/each}
        </div>

        <!-- The Rest of the Leaderboard -->
        {#if others.length > 0}
            <div class="glass-card rounded-3xl overflow-hidden shadow-lg border border-white/50">
                <div class="px-6 py-4 bg-surface-container-low border-b border-outline-variant/20 flex justify-between font-label-bold text-on-surface-variant text-sm">
                    <span class="w-12 text-center">Hạng</span>
                    <span class="flex-1 px-4">Tên</span>
                    <span class="w-24 text-right">Điểm</span>
                </div>
                <div class="divide-y divide-outline-variant/10">
                    {#each others as rankUser, i}
                        <div 
                            class="flex items-center px-6 py-3 transition-colors hover:bg-surface-container-lowest {rankUser.is_me ? 'bg-primary/5' : ''}"
                            in:fade={{ duration: 300, delay: 300 + (i * 50) }}
                        >
                            <span class="w-12 text-center font-title-md text-on-surface-variant {rankUser.is_me ? 'text-primary font-bold' : ''}">
                                {rankUser.rank}
                            </span>
                            <div class="flex-1 px-4 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-surface-container border border-outline-variant/20 overflow-hidden flex items-center justify-center">
                                    {#if rankUser.avatar}
                                        <img src={rankUser.avatar} alt={rankUser.name} class="w-full h-full object-cover" />
                                    {:else}
                                        <span class="material-symbols-outlined text-surface-variant text-xl">person</span>
                                    {/if}
                                </div>
                                <div>
                                    <p class="font-body-md {rankUser.is_me ? 'font-bold text-primary' : 'text-on-surface'}">{rankUser.name}</p>
                                    {#if rankUser.branch}
                                        <p class="text-xs text-on-surface-variant capitalize">{rankUser.branch.replace('_', ' ')}</p>
                                    {/if}
                                </div>
                            </div>
                            <span class="w-24 text-right font-label-bold text-primary bg-primary/5 px-3 py-1.5 rounded-lg flex items-center justify-end gap-1">
                                {rankUser.score}
                            </span>
                        </div>
                    {/each}
                </div>
            </div>
        {/if}
    {/if}
</main>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
</style>
