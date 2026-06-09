<script lang="ts">
    import { Link, useForm } from '@inertiajs/svelte';

    const form = useForm({
        login: '',
        password: '',
        remember: false,
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        form.post('/login');
    }
</script>

<div
    class="login-root relative flex min-h-[calc(100vh-5rem)] flex-col items-center justify-center overflow-hidden px-5 py-12 font-['Quicksand',sans-serif]"
>
    <!-- Background glow effects -->
    <div
        class="pointer-events-none absolute top-[-10%] left-[-10%] -z-10 h-[40%] w-[40%] rounded-full bg-[#c00008]/8 blur-[120px] dark:bg-[#ffb3ad]/10"
    ></div>
    <div
        class="pointer-events-none absolute right-[-5%] bottom-[-5%] -z-10 h-[30%] w-[30%] rounded-full bg-[#42617d]/8 blur-[100px] dark:bg-[#a4c9ff]/10"
    ></div>

    <!-- Login card -->
    <div
        class="login-glass relative z-10 w-full max-w-md rounded-2xl p-8 md:p-12"
    >
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1
                class="mb-2 text-[32px] font-bold leading-tight text-[#191c1d] dark:text-[#e5e2e1]"
            >
                Đăng Nhập
            </h1>
            <p class="text-base text-[#42474d] dark:text-[#e4beba]">
                Chào mừng bạn trở lại với Đoàn Thiếu Nhi
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-6" onsubmit={handleSubmit}>
            <!-- Email -->
            <div class="space-y-2">
                <label
                    for="login-email"
                    class="ml-1 block text-sm font-semibold text-[#191c1d] dark:text-[#e5e2e1]"
                >
                    Email / Username
                </label>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute top-1/2 left-5 -translate-y-1/2 text-[#42474d] dark:text-[#e4beba]"
                        aria-hidden="true">person</span
                    >
                    <input
                        id="login-email"
                        type="text"
                        placeholder="tntt.member@example.com"
                        bind:value={form.login}
                        class="login-input w-full rounded-xl border py-4 pr-5 pl-14 text-base outline-none transition-all"
                    />
                </div>
            </div>

            {#if form.errors.login}
                <div class="text-sm text-red-500 mt-1 ml-1 font-semibold">
                    {form.errors.login}
                </div>
            {/if}

            <!-- Password -->
            <div class="space-y-2">
                <label
                    for="login-password"
                    class="ml-1 block text-sm font-semibold text-[#191c1d] dark:text-[#e5e2e1]"
                >
                    Mật Khẩu
                </label>
                <div class="relative">
                    <span
                        class="material-symbols-outlined absolute top-1/2 left-5 -translate-y-1/2 text-[#42474d] dark:text-[#e4beba]"
                        aria-hidden="true">lock</span
                    >
                    <input
                        id="login-password"
                        type="password"
                        placeholder="••••••••"
                        bind:value={form.password}
                        class="login-input w-full rounded-xl border py-4 pr-5 pl-14 text-base outline-none transition-all"
                    />
                </div>
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        type="checkbox"
                        bind:checked={form.remember}
                        class="login-checkbox h-5 w-5 rounded"
                    />
                    <span
                        class="text-sm font-semibold text-[#42474d] dark:text-[#e4beba]"
                        >Ghi nhớ tôi</span
                    >
                </label>
                <a
                    href="/forgot-password"
                    class="text-sm font-semibold text-[#c00008] hover:underline dark:text-[#ffb3ad]"
                >
                    Quên mật khẩu?
                </a>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                disabled={form.processing}
                class="login-btn-submit mt-4 w-full rounded-xl py-4 text-center text-xl font-semibold transition-all duration-150 active:scale-[0.97] disabled:opacity-70"
            >
                {#if form.processing}
                    <span class="inline-flex items-center gap-2">
                        <span class="login-spinner"></span>
                        Đang xử lý…
                    </span>
                {:else}
                    Đăng Nhập
                {/if}
            </button>
        </form>
    </div>

    <!-- Decorative icons -->
    <div class="mt-12 flex items-center gap-10 opacity-30">
        <div class="flex flex-col items-center gap-1">
            <span
                class="material-symbols-outlined text-5xl text-[#42474d] dark:text-[#e5e2e1]"
                aria-hidden="true">cloud</span
            >
            <span
                class="text-xs font-bold uppercase tracking-widest text-[#42474d] dark:text-[#e5e2e1]"
                >Bình An</span
            >
        </div>
        <div class="flex flex-col items-center gap-1">
            <span
                class="material-symbols-outlined text-5xl text-[#42474d] dark:text-[#e5e2e1]"
                aria-hidden="true">favorite</span
            >
            <span
                class="text-xs font-bold uppercase tracking-widest text-[#42474d] dark:text-[#e5e2e1]"
                >Yêu Thương</span
            >
        </div>
        <div class="flex flex-col items-center gap-1">
            <span
                class="material-symbols-outlined text-5xl text-[#42474d] dark:text-[#e5e2e1]"
                aria-hidden="true">auto_awesome</span
            >
            <span
                class="text-xs font-bold uppercase tracking-widest text-[#42474d] dark:text-[#e5e2e1]"
                >Hy Vọng</span
            >
        </div>
    </div>
</div>

<style>
    /* Glass panel – light */
    .login-glass {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
    }
    :global(.dark) .login-glass {
        background: rgba(30, 30, 30, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-top-color: rgba(255, 255, 255, 0.1);
        border-left-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    /* Input – light */
    .login-input {
        background: #f0f1f2;
        border-color: #dde0e2;
        color: #191c1d;
        font-family: 'Quicksand', sans-serif;
    }
    .login-input::placeholder {
        color: rgba(66, 71, 77, 0.4);
    }
    .login-input:focus {
        border-color: #c00008;
        box-shadow: 0 0 0 3px rgba(192, 0, 8, 0.12);
    }
    :global(.dark) .login-input {
        background: #1c1b1b;
        border-color: #5b403e;
        color: #e5e2e1;
    }
    :global(.dark) .login-input::placeholder {
        color: rgba(228, 190, 186, 0.4);
    }
    :global(.dark) .login-input:focus {
        border-color: #ffb3ad;
        box-shadow: 0 0 0 3px rgba(255, 179, 173, 0.15);
    }

    /* Checkbox */
    .login-checkbox {
        accent-color: #c00008;
        border: 1.5px solid #dde0e2;
        background: #f0f1f2;
    }
    :global(.dark) .login-checkbox {
        accent-color: #ffb3ad;
        border-color: #5b403e;
        background: #1c1b1b;
    }

    /* Submit button */
    .login-btn-submit {
        background: #c00008;
        color: #ffffff;
        box-shadow: 0 0 20px rgba(192, 0, 8, 0.25);
    }
    :global(.dark) .login-btn-submit {
        background: #ff5451;
        color: #5c0008;
        box-shadow: 0 0 20px rgba(255, 84, 81, 0.3);
    }

    /* Spinner */
    .login-spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2.5px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    :global(.dark) .login-spinner {
        border-color: rgba(92, 0, 8, 0.3);
        border-top-color: #5c0008;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
