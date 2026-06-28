<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, router, useForm, page } from '@inertiajs/svelte';

    let { session, records, attendanceRate }: { session: any; records: any[]; attendanceRate: number } = $props();

    const statusMap = {
        present: { label: 'Có mặt', icon: 'check_circle', color: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
        absent: { label: 'Vắng', icon: 'cancel', color: 'text-error bg-error-container/30 border-error-container' },
        late: { label: 'Trễ', icon: 'schedule', color: 'text-amber-600 bg-amber-50 border-amber-200' },
        excused: { label: 'Có phép', icon: 'edit_document', color: 'text-blue-600 bg-blue-50 border-blue-200' },
    };

    function updateStatus(recordId: number, newStatus: string) {
        router.post(`/dashboard/attendance/${session.id}/manual`, {
            record_id: recordId,
            status: newStatus
        }, {
            preserveScroll: true,
        });
    }

    function endSession() {
        if(confirm('Bạn có chắc muốn kết thúc phiên điểm danh này?')) {
            router.put(`/dashboard/attendance/${session.id}`, {
                title: session.title,
                session_date: session.session_date,
                status: 'completed',
                notes: session.notes
            }, { preserveScroll: true });
        }
    }
</script>

<svelte:head>
    <title>{session.title} - Điểm Danh</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            
            <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-4">
                <Link href="/dashboard/attendance" class="hover:underline">Điểm Danh</Link>
                <span class="material-symbols-outlined text-sm">chevron_right</span>
                <span class="text-primary font-medium">{session.title}</span>
            </nav>

            <div class="glass-card rounded-3xl p-6 mb-gutter border-t-8 border-t-primary shadow-md flex flex-col md:flex-row justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="font-headline-lg text-headline-sm text-on-surface">{session.title}</h1>
                        <span class="px-2 py-1 text-xs font-bold rounded-md bg-secondary-container text-on-secondary-container">Lớp {session.tntt_class?.name || 'Không rõ'}</span>
                        {#if session.status === 'completed'}
                            <span class="px-2 py-1 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800">Hoàn tất</span>
                        {:else if session.status === 'in_progress'}
                            <span class="px-2 py-1 text-xs font-bold rounded-md bg-blue-100 text-blue-800 animate-pulse">Đang diễn ra</span>
                        {/if}
                    </div>
                    <p class="text-on-surface-variant flex items-center gap-1.5 text-sm mb-1">
                        <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        Ngày: {new Date(session.session_date).toLocaleDateString('vi-VN')}
                    </p>
                    {#if session.notes}
                        <p class="text-on-surface-variant text-sm flex items-start gap-1.5 mt-2 bg-surface-container-low p-2 rounded-lg">
                            <span class="material-symbols-outlined text-[16px] mt-0.5">info</span>
                            {session.notes}
                        </p>
                    {/if}
                </div>
                
                <div class="flex flex-col items-center justify-center min-w-[150px] bg-surface-container-lowest p-4 rounded-2xl shadow-inner border border-outline-variant/10">
                    <p class="text-xs font-label-bold text-on-surface-variant uppercase mb-1">Tỷ lệ có mặt</p>
                    <p class="text-4xl font-display-lg text-primary">{attendanceRate}%</p>
                    <div class="w-full bg-surface-container-high rounded-full h-1.5 mt-2 overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-1000" style="width: {attendanceRate}%"></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                <h2 class="font-title-lg text-on-surface">Danh sách Điểm danh ({records.length})</h2>
                
                <div class="flex gap-2">
                    <a 
                        href={`/dashboard/attendance/${session.id}/export`}
                        class="px-5 py-2.5 rounded-xl font-label-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors flex items-center gap-2"
                        target="_blank"
                    >
                        <span class="material-symbols-outlined">download</span>
                        Xuất Excel
                    </a>
                    {#if session.status !== 'completed'}
                        <Link 
                            href={`/dashboard/attendance/${session.id}/scan`}
                            class="duolingo-shadow-primary bg-primary text-on-primary px-5 py-2.5 rounded-xl font-label-bold flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-sm"
                        >
                            <span class="material-symbols-outlined">qr_code_scanner</span>
                            Quét QR
                        </Link>
                        <button
                            onclick={endSession}
                            class="px-5 py-2.5 rounded-xl font-label-bold bg-surface-container-high hover:bg-error-container hover:text-error transition-colors flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined">stop_circle</span>
                            Chốt Sổ
                        </button>
                    {/if}
                </div>
            </div>

            <div class="glass-card rounded-2xl shadow-md overflow-x-auto border border-outline-variant/10">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-surface-container-high/50 text-on-surface-variant text-sm font-label-bold border-b border-outline-variant/10">
                            <th class="p-4 w-12 text-center">STT</th>
                            <th class="p-4">Đoàn Sinh</th>
                            <th class="p-4 w-48 text-center">Trạng Thái</th>
                            <th class="p-4">Thao tác nhanh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each records as record, i}
                            <tr class="hover:bg-surface-container/30 transition-colors {record.status === 'absent' ? 'bg-error-container/5' : ''}">
                                <td class="p-4 text-center text-outline-variant font-medium">{i + 1}</td>
                                <td class="p-4">
                                    <div class="font-bold text-on-surface text-[15px]">{record.user.name}</div>
                                    <div class="text-xs text-outline">@{record.user.username}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {statusMap[record.status as keyof typeof statusMap].color}">
                                        <span class="material-symbols-outlined text-[14px]">{statusMap[record.status as keyof typeof statusMap].icon}</span>
                                        {statusMap[record.status as keyof typeof statusMap].label}
                                    </span>
                                </td>
                                <td class="p-4">
                                    {#if session.status !== 'completed'}
                                        <div class="flex gap-1">
                                            {#if record.status !== 'present'}
                                                <button onclick={() => updateStatus(record.id, 'present')} class="p-1.5 rounded bg-surface-container hover:bg-emerald-100 hover:text-emerald-700 transition-colors" title="Đánh dấu có mặt">
                                                    <span class="material-symbols-outlined block text-[18px]">check</span>
                                                </button>
                                            {/if}
                                            {#if record.status !== 'absent'}
                                                <button onclick={() => updateStatus(record.id, 'absent')} class="p-1.5 rounded bg-surface-container hover:bg-error-container hover:text-error transition-colors" title="Đánh dấu vắng">
                                                    <span class="material-symbols-outlined block text-[18px]">close</span>
                                                </button>
                                            {/if}
                                            <div class="h-6 w-px bg-outline-variant/30 mx-1 self-center"></div>
                                            {#if record.status === 'absent'}
                                                <button 
                                                    onclick={() => {
                                                        const message = `Kính gửi phụ huynh em ${record.user.name}. Em ${record.user.name} đã vắng buổi sinh hoạt ngày ${new Date(session.session_date).toLocaleDateString('vi-VN')}. Xin vui lòng liên hệ Huynh Trưởng.`;
                                                        navigator.clipboard.writeText(message);
                                                        alert('Đã copy tin nhắn mẫu Zalo!');
                                                    }} 
                                                    class="p-1.5 rounded bg-surface-container hover:bg-blue-100 hover:text-blue-700 transition-colors" 
                                                    title="Copy tin nhắn Zalo cho phụ huynh"
                                                >
                                                    <span class="material-symbols-outlined block text-[18px]">chat</span>
                                                </button>
                                                <div class="h-6 w-px bg-outline-variant/30 mx-1 self-center"></div>
                                            {/if}
                                            <select 
                                                value={record.status}
                                                onchange={(e) => updateStatus(record.id, (e.target as HTMLSelectElement).value)}
                                                class="text-xs bg-surface-container border-none outline-none rounded-md px-2 py-1 focus:ring-1 focus:ring-primary/30"
                                            >
                                                <option value="present">Có mặt</option>
                                                <option value="absent">Vắng</option>
                                                <option value="late">Trễ</option>
                                                <option value="excused">Có phép</option>
                                            </select>
                                        </div>
                                    {:else}
                                        <span class="text-xs text-outline italic">Đã khóa</span>
                                    {/if}
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</DashboardLayout>
