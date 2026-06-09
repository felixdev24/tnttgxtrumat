<script lang="ts">

    import { page } from '@inertiajs/svelte';
    import { Link } from '@inertiajs/svelte';

    // Props from the controller
    let { allWeeks } = $props();

    // Authentication state
    let user = $derived(page.props.auth?.user);
</script>

<svelte:head>
    <title>Đố Vui Kinh Thánh - Thiếu Nhi Thánh Thể</title>
</svelte:head>

<main class="pt-[100px] pb-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto min-h-screen">
        <!-- Hero Section -->
        <section class="text-center mb-stack-lg">
            <h1 class="font-display-lg text-display-lg text-primary mb-stack-sm">Đố vui Kinh Thánh</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto">Thử thách kiến thức Lời Chúa và nhận những phần quà hấp dẫn từ Ban Giáo Lý Giáo Xứ Trù Mật.</p>
        </section>

        <!-- Bento Layout Selection -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
            <!-- Left Panel: Illustration & Info -->
            <div class="md:col-span-4 flex flex-col gap-gutter">
                <div class="glass-card p-stack-lg rounded-xl flex flex-col items-center justify-center text-center shadow-sm">
                    <img class="w-48 h-48 object-contain mb-stack-md" alt="Mascot" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBiB4O1HAtgvBUAclUy66epsbFGehstuBiROhdNOr0k-6yviQ6lD7b5U2v-YlEJPtgYMsaGmkHahOaXA2hJ6AaOqjSE9QkgMxqX55DzptE43Dv6JXCO2VXiRFkWRHthBBFXeo2otbjPCXmol8Xync_p9D4soG6rjhN9aHi-YFlKJj0YPKUcgSnUde5-5WJUj5ueS2jt-v9rgbPWu4VZengPS9_J62ZvSz6W77pMvwd_iZqgdcK4ereGxBDNMf1Ht1_1z4nKIGKziZY"/>
                    <div class="bg-primary-container text-on-primary-container px-4 py-2 rounded-full font-label-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">schedule</span>
                        Thời gian: 15:00
                    </div>
                </div>

                <div class="bg-secondary-container p-stack-lg rounded-xl shadow-sm">
                    <h3 class="font-title-md text-title-md text-on-secondary-container mb-stack-sm">Lưu ý nhỏ</h3>
                    <ul class="space-y-unit font-body-md text-on-secondary-fixed-variant">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                            Mỗi tuần chỉ được thi một lần duy nhất.
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                            Phần thưởng sẽ được trao vào Thánh lễ Chúa Nhật.
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Panel: Week Selection & Action -->
            <div class="md:col-span-8 flex flex-col gap-gutter">
                <div class="glass-card p-stack-lg rounded-xl shadow-sm h-full flex flex-col">
                    <h2 class="font-title-md text-title-md text-on-surface mb-gutter">Chọn tuần thi đấu</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-stack-md mb-gutter">
                        {#each allWeeks as week, index}
                            <Link href={`/quizzes/${week.id}`} class="relative group p-stack-md rounded-lg border-2 {index === 0 ? 'border-primary bg-primary/5 ring-4 ring-primary-container/30' : 'border-outline-variant hover:border-primary-container bg-surface-container-low'} text-left transition-all hover:shadow-md cursor-pointer block">
                                <div class="flex justify-between items-start mb-stack-sm">
                                    <span class="{index === 0 ? 'bg-primary' : 'bg-outline'} text-on-primary px-3 py-1 rounded-full text-xs font-bold">
                                        TUẦN {week.label.match(/Tuần (\d+)/)?.[1] || index + 1}
                                    </span>
                                    {#if index === 0}
                                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                    {:else}
                                        <span class="material-symbols-outlined text-outline-variant">circle</span>
                                    {/if}
                                </div>
                                <h4 class="font-title-md text-on-surface mb-unit">{week.theme || week.label}</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-xs {index === 0 ? 'bg-surface-container' : 'bg-surface-container-high'} px-2 py-1 rounded flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">quiz</span> {week.questions_count} Câu hỏi
                                    </span>
                                    {#if index === 0}
                                        <span class="text-xs bg-surface-container px-2 py-1 rounded flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">redeem</span> Quà đặc biệt
                                        </span>
                                    {/if}
                                </div>
                            </Link>
                        {/each}

                        {#if allWeeks.length < 4}
                            <!-- Locked Week placeholder -->
                            <div class="relative group p-stack-md rounded-lg border-2 border-outline-variant bg-surface-dim/30 text-left cursor-not-allowed opacity-60">
                                <div class="flex justify-between items-start mb-stack-sm">
                                    <span class="bg-outline-variant text-on-surface-variant px-3 py-1 rounded-full text-xs font-bold">TUẦN {allWeeks.length + 1}</span>
                                    <span class="material-symbols-outlined text-outline">lock</span>
                                </div>
                                <h4 class="font-title-md text-on-surface-variant mb-unit">Đang cập nhật...</h4>
                                <p class="text-xs text-on-surface-variant italic">Mở sau vài ngày nữa</p>
                            </div>
                        {/if}
                    </div>

                    <!-- CTA Button -->
                    <div class="mt-auto flex flex-col items-center gap-stack-sm">
                        {#if allWeeks.length > 0}
                            <Link href={`/quizzes/${allWeeks[0].id}`} class="w-full md:w-2/3 bg-tertiary text-on-tertiary py-5 rounded-full font-headline-lg text-headline-lg-mobile md:text-headline-lg flex items-center justify-center duo-button-shadow transition-all hover:brightness-110 active:scale-95 text-center">
                                Bắt đầu ngay
                            </Link>
                            <p class="text-on-surface-variant font-body-md text-sm">Bạn đã sẵn sàng cho thử thách {allWeeks[0].label} chưa?</p>
                        {:else}
                            <p class="text-on-surface-variant font-body-md text-sm">Hiện chưa có tuần thi nào.</p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
</main>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .duo-button-shadow {
        box-shadow: 0 4px 0 0 #930005;
    }
    .duo-button-shadow:active {
        box-shadow: 0 0px 0 0 #930005;
        transform: translateY(4px);
    }
</style>
