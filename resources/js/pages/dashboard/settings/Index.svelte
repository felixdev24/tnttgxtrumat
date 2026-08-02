<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { useForm, page } from '@inertiajs/svelte';

    interface Setting {
        id: number;
        key: string;
        value: string;
        label: string;
    }

    let { settings = [] }: { settings: Setting[] } = $props();

    // Map initial settings to form fields
    const form = useForm({
        _method: 'put',
        settings: settings.map(s => ({
            key: s.key,
            value: s.key === 'welcome_hero_image' ? null : (s.value || ''),
            label: s.label || s.key,
            current_value: s.value
        }))
    });

    function saveSettings() {
        form.post('/dashboard/settings', {
            preserveScroll: true,
        });
    }

    const groupHeaders: Record<string, string> = {
        'welcome_hero_image': 'Cấu Hình Trang Chủ',
        'footer_link_giao_xu': 'Liên Kết Footer',
        'academic_year': 'Cấu Hình Thẻ Thiếu Nhi'
    };
</script>

<svelte:head>
    <title>Cài Đặt Hệ Thống - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-stack-lg gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Cài Đặt Thẻ & Hệ Thống</h1>
                    <p class="text-on-surface-variant mt-1 text-sm">Quản lý cấu hình động cho Trang Chủ, Footer và Thẻ Thiếu Nhi.</p>
                </div>
            </div>

            <!-- Flash Message -->
            {#if (page.props as any).flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900/40 animate-fade-in shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{(page.props as any).flash.success}</p>
                </div>
            {/if}

            <div class="glass-card rounded-2xl shadow-md p-6">
                <form onsubmit={(e) => { e.preventDefault(); saveSettings(); }} class="space-y-6">
                    <div class="space-y-4">
                        {#each form.settings as setting, index}
                            {#if groupHeaders[setting.key]}
                                <h3 class="text-lg font-headline-sm text-primary border-b border-outline-variant/30 pb-2 mt-6 mb-2">
                                    {groupHeaders[setting.key]}
                                </h3>
                            {/if}
                            <div class="mt-2">
                                <label class="block text-sm font-label-bold text-on-surface-variant mb-1">
                                    {setting.label}
                                </label>
                                {#if setting.key === 'welcome_hero_image'}
                                    {#if setting.current_value}
                                        <div class="mb-2">
                                            <img src={`/storage/${setting.current_value}`} alt="Current Hero" class="h-24 w-auto rounded-lg object-cover border border-outline-variant/30" />
                                        </div>
                                    {/if}
                                    <input 
                                        type="file" 
                                        accept="image/*"
                                        onchange={(e) => {
                                            const file = e.currentTarget.files?.[0];
                                            if (file) {
                                                form.settings[index].value = file as any;
                                            }
                                        }}
                                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800"
                                    />
                                    <p class="text-xs text-outline mt-1">Chọn ảnh mới để thay thế ảnh hiện tại (để trống nếu không đổi).</p>
                                {:else if setting.key === 'academic_year'}
                                    <input 
                                        type="text" 
                                        bind:value={form.settings[index].value} 
                                        placeholder="Ví dụ: 2024-2025" 
                                        required 
                                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800"
                                    />
                                    <p class="text-xs text-outline mt-1">Cấu hình niên khóa hiển thị ở góc dưới bên trái của thẻ Thiếu Nhi.</p>
                                {:else}
                                    <input 
                                        type="text" 
                                        bind:value={form.settings[index].value} 
                                        class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20 dark:bg-zinc-800"
                                    />
                                {/if}
                                {#if form.errors[`settings.${index}.value`]}
                                    <p class="text-xs text-error mt-1">{form.errors[`settings.${index}.value`]}</p>
                                {/if}
                            </div>
                        {/each}
                    </div>

                    <div class="flex justify-end pt-4 border-t border-outline-variant/10">
                        <button 
                            type="submit" 
                            disabled={form.processing}
                            class="px-8 py-3 rounded-xl font-label-bold bg-primary text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-md disabled:opacity-50 flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Lưu cấu hình
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</DashboardLayout>
