<script lang="ts">
    import { applyTheme, THEME_STORAGE_KEY } from '@/lib/color-scheme';
    import { onMount } from 'svelte';

    let isDark = $state(false);

    onMount(() => {
        isDark = document.documentElement.classList.contains('dark');
    });

    function toggle(): void {
        isDark = !isDark;
        const next = isDark ? 'dark' : 'light';

        localStorage.setItem(THEME_STORAGE_KEY, next);
        applyTheme(next);
    }
</script>

<button
    type="button"
    class="rounded-xl p-2 transition-all duration-300 hover:bg-[#e7e8e9]/50 active:scale-95 dark:hover:bg-white/10"
    onclick={toggle}
    aria-label={isDark ? 'Chuyển giao diện sáng' : 'Chuyển giao diện tối'}
    aria-pressed={isDark}
>
    <span class="material-symbols-outlined text-[#42474d] dark:text-zinc-300" aria-hidden="true">
        {isDark ? 'light_mode' : 'dark_mode'}
    </span>
</button>
