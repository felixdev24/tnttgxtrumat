<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, page } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import { Html5QrcodeScanner } from 'html5-qrcode';

    let {
        session,
        csrf_token,
    }: {
        session: { id: number; title: string; tntt_class_id: number; tntt_class_name: string };
        csrf_token: string;
    } = $props();

    let scanner: Html5QrcodeScanner | null = null;
    let scanResult = $state<{ success: boolean; message?: string; user?: any } | null>(null);
    let scannedList = $state<any[]>([]);
    let isProcessing = $state(false);
    let totalScanned = $state(0);

    onMount(() => {
        scanner = new Html5QrcodeScanner('qr-reader', { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        scanner.render(onScanSuccess, onScanFailure);
    });

    onDestroy(() => {
        if (scanner) {
            scanner.clear().catch((error) => {
                console.error('Failed to clear html5QrcodeScanner. ', error);
            });
        }
    });

    async function onScanSuccess(decodedText: string) {
        if (isProcessing) return;
        isProcessing = true;

        try {
            const response = await fetch('/dashboard/attendance/check-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf_token,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    session_id: session.id,
                    qr_token: decodedText,
                }),
            });

            const data = await response.json();

            if (data.success) {
                if (data.already_checked_in) {
                    scanResult = { success: true, message: 'Đã điểm danh trước đó', user: data.user };
                } else {
                    scanResult = { success: true, message: 'Điểm danh thành công!', user: data.user };
                    scannedList = [{ ...data.user, time: new Date() }, ...scannedList].slice(0, 15);
                    totalScanned += 1;
                }
                playAudio(true);
            } else {
                scanResult = { success: false, message: data.message || 'Lỗi không xác định' };
                playAudio(false);
            }
        } catch (error) {
            console.error(error);
            scanResult = { success: false, message: 'Lỗi kết nối máy chủ' };
            playAudio(false);
        }

        // Auto-clear result after 3 seconds
        setTimeout(() => {
            scanResult = null;
            isProcessing = false;
        }, 3000);
    }

    function onScanFailure(_error: any) {
        // Silently ignore scan failures
    }

    function playAudio(success: boolean) {
        try {
            const ctx = new (window.AudioContext || (window as any).webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.connect(gain);
            gain.connect(ctx.destination);

            if (success) {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, ctx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.5, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.3);
            } else {
                osc.type = 'square';
                osc.frequency.setValueAtTime(200, ctx.currentTime);
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.4);
            }
        } catch {
            // Audio API not available
        }
    }
</script>

<svelte:head>
    <title>Quét QR Điểm Danh - {session.title}</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg max-w-5xl mx-auto">
        <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-4">
            <Link href="/dashboard/attendance" class="hover:underline">Điểm Danh</Link>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <Link href={`/dashboard/attendance/${session.id}`} class="hover:underline">{session.title}</Link>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <span class="text-primary font-medium">Quét QR</span>
        </nav>

        <div class="mb-stack-lg flex items-start justify-between">
            <div>
                <h1 class="font-headline-lg text-headline-sm text-primary">Quét QR Điểm Danh</h1>
                <p class="text-on-surface-variant mt-1 text-sm">
                    Sử dụng camera để quét mã QR của đoàn sinh lớp <strong>{session.tntt_class_name}</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl">
                <span class="material-symbols-outlined text-emerald-600">how_to_reg</span>
                <div class="text-right">
                    <p class="text-xs text-emerald-700 font-bold uppercase">Đã điểm danh</p>
                    <p class="text-2xl font-bold text-emerald-700">{totalScanned}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Camera Section -->
            <div class="glass-card rounded-3xl p-6 shadow-md border-t-8 border-t-primary flex flex-col">
                <div id="qr-reader" class="w-full overflow-hidden rounded-xl bg-black min-h-[300px]"></div>

                <div
                    class="mt-6 p-4 rounded-xl text-center min-h-[110px] flex items-center justify-center transition-all duration-300 {scanResult
                        ? scanResult.success
                            ? 'bg-emerald-100 border-2 border-emerald-300 scale-[1.01]'
                            : 'bg-error-container/30 border-2 border-error-container scale-[1.01]'
                        : 'bg-surface-container border-2 border-transparent'}"
                >
                    {#if scanResult}
                        {#if scanResult.success}
                            <div class="animate-bounce-once">
                                <span class="material-symbols-outlined text-[44px] text-emerald-600 mb-1">check_circle</span>
                                <h3 class="font-title-lg text-emerald-800">{scanResult.user.name}</h3>
                                <p class="text-emerald-700 text-sm font-label-bold">{scanResult.message}</p>
                                {#if scanResult.user.tntt_class_name}
                                    <p class="text-emerald-600 text-xs mt-1">Lớp: {scanResult.user.tntt_class_name}</p>
                                {/if}
                            </div>
                        {:else}
                            <div>
                                <span class="material-symbols-outlined text-[44px] text-error mb-1">error</span>
                                <h3 class="font-title-lg text-error">Lỗi Quét Mã</h3>
                                <p class="text-error text-sm font-label-bold">{scanResult.message}</p>
                            </div>
                        {/if}
                    {:else}
                        <div class="text-on-surface-variant">
                            <span class="material-symbols-outlined text-[44px] opacity-40 mb-1">qr_code_scanner</span>
                            <p class="font-label-bold text-sm">Đang chờ quét mã...</p>
                            <p class="text-xs opacity-60 mt-1">Đưa mã QR vào khung camera</p>
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Recent Scans Section -->
            <div class="glass-card rounded-3xl p-6 shadow-md border border-outline-variant/10 flex flex-col">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-outline-variant/10">
                    <h2 class="font-title-md text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Vừa điểm danh
                    </h2>
                    <span class="px-2.5 py-1 bg-surface-container rounded-lg text-xs font-bold text-on-surface-variant">
                        {scannedList.length} lượt
                    </span>
                </div>

                <div class="flex-1 flex flex-col gap-2 overflow-y-auto max-h-[380px] pr-1">
                    {#each scannedList as user, i (i)}
                        <div
                            class="p-3 rounded-xl bg-surface-container-lowest border border-outline-variant/10 flex items-center gap-3 shadow-sm"
                        >
                            <div
                                class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm flex-shrink-0"
                            >
                                {user.name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-label-bold text-on-surface truncate">{user.name}</h4>
                                {#if user.tntt_class_name}
                                    <p class="text-xs text-outline-variant">Lớp: {user.tntt_class_name}</p>
                                {/if}
                            </div>
                            <div class="text-xs font-bold text-emerald-600 flex items-center gap-1 flex-shrink-0">
                                <span class="material-symbols-outlined text-[13px]">done_all</span>
                                {user.time.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}
                            </div>
                        </div>
                    {:else}
                        <div class="flex-1 flex flex-col items-center justify-center py-10 text-on-surface-variant">
                            <span class="material-symbols-outlined text-4xl opacity-30 mb-2">history_toggle_off</span>
                            <p class="text-sm">Chưa có lượt quét nào trong phiên này.</p>
                        </div>
                    {/each}
                </div>

                <div class="mt-4 pt-4 border-t border-outline-variant/10 text-center">
                    <Link
                        href={`/dashboard/attendance/${session.id}`}
                        class="text-primary font-label-bold hover:underline text-sm flex items-center justify-center gap-1"
                    >
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        Quay lại trang chi tiết
                    </Link>
                </div>
            </div>
        </div>
    </div>
</DashboardLayout>

<style>
    :global(#qr-reader) {
        border: none !important;
    }
    :global(#qr-reader__scan_region) {
        background-color: black;
    }
    :global(#qr-reader__dashboard) {
        padding: 16px !important;
        background-color: var(--color-surface-container);
    }
    :global(#qr-reader button) {
        background-color: var(--color-primary);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
    }
    :global(#qr-reader button:hover) {
        filter: brightness(1.1);
    }
    :global(#qr-reader select) {
        padding: 8px;
        border-radius: 8px;
        border: 1px solid var(--color-outline-variant);
        margin-bottom: 8px;
    }
    :global(#qr-reader__status_span) {
        display: none !important;
    }
    @keyframes bounce-once {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-6px);
        }
    }
    :global(.animate-bounce-once) {
        animation: bounce-once 0.4s ease-in-out;
    }
</style>
