export const THEME_STORAGE_KEY = 'tntt-color-scheme';

export type ThemePreference = 'light' | 'dark';

export function applyTheme(theme: ThemePreference): void {
    document.documentElement.classList.toggle('dark', theme === 'dark');
}
