/**
 * Theme store: "light" | "dark" | "system", persisted in localStorage.
 * The initial class is applied by an inline script in <head> to avoid a flash.
 */
export const THEME_KEY = 'theme';

export function themeStore() {
    return {
        mode: localStorage.getItem(THEME_KEY) ?? 'system',

        get isDark() {
            return (
                this.mode === 'dark' || (this.mode === 'system' && matchMedia('(prefers-color-scheme: dark)').matches)
            );
        },

        toggle() {
            this.set(this.isDark ? 'light' : 'dark');
        },

        set(mode) {
            this.mode = mode;
            localStorage.setItem(THEME_KEY, mode);
            document.documentElement.classList.toggle('dark', this.isDark);
        },
    };
}
