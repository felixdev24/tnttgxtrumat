<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { router, useForm, page } from '@inertiajs/svelte';
    
    interface Webhook {
        id: number;
        name: string;
        url: string;
        events: string[];
        is_active: boolean;
        secret: string | null;
        created_at: string;
    }

    let { webhooks }: { webhooks: Webhook[] } = $props();

    // Modal state
    let showModal = $state(false);
    let isEditing = $state(false);

    const availableEvents = [
        { id: 'student_created', name: 'Thêm đoàn sinh mới' },
        { id: 'student_updated', name: 'Cập nhật đoàn sinh' },
        { id: 'absence_alert', name: 'Cảnh báo vắng mặt (> 2 buổi)' },
        { id: 'attendance_completed', name: 'Hoàn thành điểm danh một buổi' },
    ];

    const form = useForm({
        id: null as number | null,
        name: '',
        url: '',
        events: [] as string[],
        secret: '',
        is_active: true,
    });

    function openAddModal() {
        isEditing = false;
        form.reset();
        form.id = null;
        form.events = [];
        form.is_active = true;
        showModal = true;
    }

    function openEditModal(webhook: Webhook) {
        isEditing = true;
        form.reset();
        form.id = webhook.id;
        form.name = webhook.name;
        form.url = webhook.url;
        form.events = [...webhook.events];
        form.secret = webhook.secret || '';
        form.is_active = webhook.is_active;
        showModal = true;
    }

    function toggleEvent(eventId: string) {
        const index = form.events.indexOf(eventId);
        if (index === -1) {
            form.events.push(eventId);
        } else {
            form.events.splice(index, 1);
        }
    }

    function submitForm() {
        if (form.events.length === 0) {
            alert('Vui lòng chọn ít nhất một sự kiện.');
            return;
        }

        if (isEditing && form.id) {
            form.put(`/dashboard/webhooks/${form.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        } else {
            form.post('/dashboard/webhooks', {
                preserveScroll: true,
                onSuccess: () => {
                    showModal = false;
                    form.reset();
                },
            });
        }
    }

    function deleteWebhook(webhook: Webhook) {
        if (confirm(`Bạn có chắc chắn muốn xóa Webhook "${webhook.name}"?`)) {
            router.delete(`/dashboard/webhooks/${webhook.id}`, {
                preserveScroll: true,
            });
        }
    }

    function toggleStatus(webhook: Webhook) {
        router.put(`/dashboard/webhooks/${webhook.id}`, {
            name: webhook.name,
            url: webhook.url,
            events: webhook.events,
            secret: webhook.secret,
            is_active: !webhook.is_active,
        }, {
            preserveScroll: true,
        });
    }

    function formatDate(dateString: string) {
        return new Date(dateString).toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
</script>

<svelte:head>
    <title>Quản lý Webhooks - Thiếu Nhi Thánh Thể</title>
</svelte:head>

<DashboardLayout>
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-surface rounded-3xl p-stack-lg border border-outline-variant/20 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Quản Lý Webhooks</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Cấu hình đẩy dữ liệu tự động sang ứng dụng khác (Zalo, Slack, CRM...) khi có sự kiện.</p>
                </div>
                <button
                    onclick={openAddModal}
                    class="bg-primary text-on-primary px-6 py-3 rounded-xl font-title-md text-[16px] flex items-center gap-2 hover:brightness-110 active:scale-95 transition-all shadow-md"
                >
                    <span class="material-symbols-outlined">add_link</span>
                    Thêm Webhook
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-surface rounded-3xl border border-outline-variant/20 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant text-sm font-label-bold">
                            <th class="p-4 rounded-tl-xl whitespace-nowrap">TÊN & URL</th>
                            <th class="p-4 whitespace-nowrap">SỰ KIỆN</th>
                            <th class="p-4 whitespace-nowrap">NGÀY TẠO</th>
                            <th class="p-4 whitespace-nowrap text-center">TRẠNG THÁI</th>
                            <th class="p-4 rounded-tr-xl text-right whitespace-nowrap">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10 text-sm">
                        {#each webhooks as webhook}
                            <tr class="hover:bg-surface-container-lowest transition-colors group">
                                <td class="p-4 align-top">
                                    <div class="font-title-md text-on-surface text-[15px]">{webhook.name}</div>
                                    <div class="text-xs font-mono text-on-surface-variant mt-1 truncate max-w-xs md:max-w-sm" title={webhook.url}>{webhook.url}</div>
                                </td>
                                <td class="p-4 align-top">
                                    <div class="flex flex-wrap gap-1">
                                        {#each webhook.events as event}
                                            <span class="px-2 py-0.5 bg-primary/10 text-primary rounded-md text-[11px] font-medium border border-primary/20">
                                                {event}
                                            </span>
                                        {/each}
                                    </div>
                                </td>
                                <td class="p-4 align-top text-on-surface-variant">
                                    {formatDate(webhook.created_at)}
                                </td>
                                <td class="p-4 align-top text-center">
                                    <button 
                                        onclick={() => toggleStatus(webhook)}
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border transition-colors {webhook.is_active ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'}"
                                    >
                                        <span class="w-2 h-2 rounded-full {webhook.is_active ? 'bg-green-500' : 'bg-gray-400'}"></span>
                                        {webhook.is_active ? 'Hoạt động' : 'Tạm dừng'}
                                    </button>
                                </td>
                                <td class="p-4 align-top text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            onclick={() => openEditModal(webhook)}
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-primary hover:bg-primary-container/50 transition-colors"
                                            title="Sửa"
                                        >
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <button
                                            onclick={() => deleteWebhook(webhook)}
                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-error hover:bg-error-container/50 transition-colors"
                                            title="Xóa"
                                        >
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="p-12 text-center text-on-surface-variant">
                                    <div class="w-16 h-16 bg-surface-variant rounded-full flex items-center justify-center mx-auto mb-3">
                                        <span class="material-symbols-outlined text-[32px] text-on-surface-variant">webhook</span>
                                    </div>
                                    <p class="font-title-md text-[16px]">Chưa có webhook nào</p>
                                    <p class="text-sm mt-1">Bấm "Thêm Webhook" để tạo kết nối mới.</p>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</DashboardLayout>

<!-- Modal Add/Edit -->
{#if showModal}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick={() => showModal = false}></div>
        <div class="bg-surface w-full max-w-2xl rounded-3xl shadow-xl relative z-10 flex flex-col max-h-[90vh]">
            <div class="p-6 border-b border-outline-variant/10 flex justify-between items-center bg-surface-container-lowest shrink-0 rounded-t-3xl">
                <div>
                    <h2 class="font-headline-sm text-[22px] text-on-surface">{isEditing ? 'Cập nhật Webhook' : 'Thêm Webhook mới'}</h2>
                </div>
                <button onclick={() => showModal = false} class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-surface-variant text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto">
                <form onsubmit={(e) => { e.preventDefault(); submitForm(); }} class="space-y-5">
                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Tên gợi nhớ <span class="text-error">*</span></label>
                        <input type="text" bind:value={form.name} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="VD: Gửi thông báo Zalo" />
                        {#if form.errors.name}<p class="text-xs text-error mt-1">{form.errors.name}</p>{/if}
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Endpoint URL <span class="text-error">*</span></label>
                        <input type="url" bind:value={form.url} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="https://..." />
                        {#if form.errors.url}<p class="text-xs text-error mt-1">{form.errors.url}</p>{/if}
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-2">Các sự kiện (Events) kích hoạt <span class="text-error">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-surface-container-lowest border border-outline-variant/20 p-4 rounded-xl">
                            {#each availableEvents as event}
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <div class="relative flex items-center justify-center w-5 h-5 mt-0.5">
                                        <input 
                                            type="checkbox" 
                                            class="peer appearance-none w-5 h-5 border-2 border-outline rounded focus:outline-none focus:ring-2 focus:ring-primary/20 checked:border-primary checked:bg-primary transition-all cursor-pointer"
                                            checked={form.events.includes(event.id)}
                                            onchange={() => toggleEvent(event.id)}
                                        />
                                        <span class="material-symbols-outlined text-[16px] text-on-primary absolute pointer-events-none opacity-0 peer-checked:opacity-100 transition-opacity">check</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-on-surface group-hover:text-primary transition-colors">{event.name}</span>
                                        <span class="text-xs text-on-surface-variant font-mono">{event.id}</span>
                                    </div>
                                </label>
                            {/each}
                        </div>
                        {#if form.errors.events}<p class="text-xs text-error mt-1">{form.errors.events}</p>{/if}
                    </div>

                    <div>
                        <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Secret Token (Tùy chọn)</label>
                        <input type="text" bind:value={form.secret} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" placeholder="Chuỗi mã hóa HMAC để xác thực" />
                        <p class="text-[11px] text-on-surface-variant mt-1">Dùng để tạo HMAC SHA256 gửi kèm header `X-TNTT-Signature`.</p>
                        {#if form.errors.secret}<p class="text-xs text-error mt-1">{form.errors.secret}</p>{/if}
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative w-12 h-6 rounded-full bg-surface-variant transition-colors peer-checked:bg-primary">
                            <input 
                                type="checkbox" 
                                bind:checked={form.is_active}
                                class="peer sr-only"
                            />
                            <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white transition-transform peer-checked:translate-x-6"></div>
                        </div>
                        <span class="text-sm font-medium text-on-surface">Kích hoạt Webhook này</span>
                    </label>
                </form>
            </div>
            
            <div class="p-6 border-t border-outline-variant/10 bg-surface-container-lowest flex justify-end gap-3 shrink-0 rounded-b-3xl">
                <button onclick={() => showModal = false} class="px-6 py-2.5 rounded-xl font-label-bold text-on-surface hover:bg-surface-variant transition-colors">
                    Hủy
                </button>
                <button 
                    onclick={submitForm}
                    disabled={form.processing}
                    class="px-6 py-2.5 rounded-xl font-label-bold bg-primary text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-sm disabled:opacity-50 flex items-center gap-2"
                >
                    {#if form.processing}
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    {/if}
                    {isEditing ? 'Lưu thay đổi' : 'Tạo mới'}
                </button>
            </div>
        </div>
    </div>
{/if}
