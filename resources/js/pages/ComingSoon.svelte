<script lang="ts">
    import { Link } from '@inertiajs/svelte';
</script>

<div
    class="welcome-root flex min-h-screen w-full items-center justify-center bg-[#f8f9fa] font-['Quicksand',sans-serif] text-[#191c1d] antialiased overflow-hidden dark:bg-[#131313] dark:text-[#e5e2e1]"
>
    <div class="absolute inset-0 z-0">
        <img
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCNjh8c1W778vUQBjbBW8JfbgTNmiZTnQBt7_dpqLFkcRV3P_qSG_qMToNOeylBGYsodsMjIXZCDreNrdDa3GPxu8T0LX_9LBPBivO-KwyBaYdz3x0uGjHpIMpGhs9jXvj-LqhuUntKYur-3r6aomBrLOvxcX-QEVfXDxWm3dh-d0KWGvVNtjkjUUIP2COkX8oKvTzn1r1qJezPcVxHgoLz8G4hXY_D133l2d1kykrOCuFSK2C2A0hT6SqnP02j4CvmXTu4oDbpiSo"
            alt=""
            class="h-full w-full object-cover opacity-20 dark:opacity-30"
        />
        <div
            class="absolute inset-0 bg-gradient-to-t from-[#f8f9fa] via-[#f8f9fa]/80 to-[#f8f9fa]/50 dark:from-[#131313] dark:via-[#131313]/80 dark:to-[#131313]/50"
        ></div>
    </div>

    <div class="welcome-glass relative z-10 mx-5 max-w-2xl rounded-[3rem] p-10 text-center shadow-2xl md:p-16">
        <div class="mb-6 flex justify-center">
            <span class="material-symbols-outlined text-[80px] text-[#e2c62d]" aria-hidden="true">
                construction
            </span>
        </div>
        
        <h1
            class="welcome-text-glow mb-4 text-3xl font-bold tracking-tight text-[#42617d] md:text-5xl dark:text-[#ffb3ad]"
        >
            Tính Năng Đang Phát Triển
        </h1>
        
        <p class="mx-auto mb-10 max-w-lg text-lg text-[#42474d] dark:text-[#e4beba]">
            Trang bạn đang truy cập hiện chưa hoàn thiện hoặc đang trong quá trình xây dựng. Vui lòng quay lại sau!
        </p>
        
        <Link
            href="/"
            class="welcome-cta-primary inline-flex items-center gap-2 rounded-xl px-8 py-4 text-lg font-bold transition-transform hover:scale-105"
        >
            <span class="material-symbols-outlined" aria-hidden="true">home</span>
            Về Trang Chủ
        </Link>
    </div>
</div>

<style>
    :global(.welcome-root .material-symbols-outlined) {
        font-family: 'Material Symbols Outlined', sans-serif;
        font-weight: 400;
        font-style: normal;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
    }

    .welcome-glass {
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .welcome-glass {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
    }

    .welcome-cta-primary {
        background: #c00008;
        color: #ffffff;
        box-shadow:
            inset 0 0 20px rgba(239, 68, 68, 0.12),
            0 10px 30px rgba(192, 0, 8, 0.2);
    }

    :global(.dark) .welcome-cta-primary {
        background: #ff5451;
        color: #5c0008;
        box-shadow:
            inset 0 0 20px rgba(239, 68, 68, 0.1),
            0 10px 30px rgba(239, 68, 68, 0.15);
    }

    .welcome-text-glow {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    :global(.dark) .welcome-text-glow {
        text-shadow: 0 0 15px rgba(255, 179, 173, 0.45);
    }
</style>
