<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { useForm, page } from '@inertiajs/svelte';

    let { user } = $props();

    let activeTab = $state('info');

    const infoForm = useForm({
        name: user.name,
        email: user.email || '',
        phone: user.phone || '',
        dob: user.dob ? user.dob.split('T')[0] : '',
        address: user.address || '',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    let showCurrentPassword = $state(false);
    let showNewPassword = $state(false);
    let showConfirmPassword = $state(false);

    function updateInfo() {
        infoForm.put('/dashboard/profile', {
            preserveScroll: true,
        });
    }

    function updatePassword() {
        passwordForm.put('/dashboard/profile/password', {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    }
</script>

<svelte:head>
    <title>Thông Tin Cá Nhân - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-stack-lg">
                <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-3xl shadow-sm">
                    {user.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <h1 class="font-headline-lg text-headline-sm md:text-headline-lg text-primary">{user.name}</h1>
                    <div class="flex gap-2 items-center mt-1">
                        <span class="text-on-surface-variant text-sm">@{user.username}</span>
                        {#if (page.props as any).auth?.is_super_admin}
                            <span class="px-2 py-0.5 bg-error-container text-error rounded-full text-xs font-bold">Super Admin</span>
                        {:else}
                            <span class="px-2 py-0.5 bg-primary-container text-on-primary-container rounded-full text-xs font-bold">Huynh Trưởng</span>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Flash Message -->
            {#if (page.props as any).flash?.success}
                <div class="mb-gutter flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 shadow-sm">
                    <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                    <p class="text-sm font-label-bold">{(page.props as any).flash.success}</p>
                </div>
            {/if}

            <!-- Tabs -->
            <div class="flex gap-4 mb-6 border-b border-outline-variant/20">
                <button
                    onclick={() => (activeTab = 'info')}
                    class="pb-2 px-2 font-label-bold transition-all {activeTab === 'info' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-on-surface'}"
                >
                    Thông tin cá nhân
                </button>
                <button
                    onclick={() => (activeTab = 'password')}
                    class="pb-2 px-2 font-label-bold transition-all {activeTab === 'password' ? 'text-primary border-b-2 border-primary' : 'text-on-surface-variant hover:text-on-surface'}"
                >
                    Đổi mật khẩu
                </button>
            </div>

            <!-- Tab Content -->
            <div class="glass-card rounded-2xl shadow-md p-6">
                {#if activeTab === 'info'}
                    <form onsubmit={(e) => { e.preventDefault(); updateInfo(); }} class="space-y-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Họ & Tên</label>
                            <input type="text" bind:value={infoForm.name} required class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            {#if infoForm.errors.name}<p class="text-xs text-error mt-1">{infoForm.errors.name}</p>{/if}
                        </div>

                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Email</label>
                            <input type="email" bind:value={infoForm.email} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            {#if infoForm.errors.email}<p class="text-xs text-error mt-1">{infoForm.errors.email}</p>{/if}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-label-bold text-on-surface-variant mb-1">SĐT</label>
                                <input type="text" bind:value={infoForm.phone} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            </div>
                            <div>
                                <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Ngày sinh</label>
                                <input type="date" bind:value={infoForm.dob} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Địa chỉ</label>
                            <input type="text" bind:value={infoForm.address} class="w-full px-4 py-2 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button
                                type="submit"
                                disabled={infoForm.processing}
                                class="px-6 py-2.5 rounded-xl font-label-bold bg-primary text-on-primary hover:brightness-110 active:scale-95 transition-all shadow-sm disabled:opacity-50"
                            >
                                Lưu thông tin
                            </button>
                        </div>
                    </form>
                {:else if activeTab === 'password'}
                    <form onsubmit={(e) => { e.preventDefault(); updatePassword(); }} class="space-y-4">
                        <div>
                            <label class="block text-sm font-label-bold text-on-surface-variant mb-1">Mật khẩu hiện tại</label>
                            <div class="relative">
                                <input type={showCurrentPassword ? "text" : "password"} bind:value={passwordForm.current_password} required class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
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
                                <input type={showNewPassword ? "text" : "password"} bind:value={passwordForm.password} required minlength="8" class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
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
                                <input type={showConfirmPassword ? "text" : "password"} bind:value={passwordForm.password_confirmation} required minlength="8" class="w-full px-4 py-2 pr-10 bg-surface-container rounded-xl border-none outline-none focus:ring-2 focus:ring-primary/20" />
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

                        <div class="p-3 bg-error-container/20 border border-error-container rounded-xl text-xs text-error flex items-center gap-2 mt-4">
                            <span class="material-symbols-outlined text-[18px]">warning</span>
                            Sau khi đổi mật khẩu thành công, bạn sẽ tự động được đăng xuất để đăng nhập lại.
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button
                                type="submit"
                                disabled={passwordForm.processing}
                                class="px-6 py-2.5 rounded-xl font-label-bold bg-secondary text-on-secondary hover:brightness-110 active:scale-95 transition-all shadow-sm disabled:opacity-50"
                            >
                                Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                {/if}
            </div>
        </div>
    </div>
</DashboardLayout>
