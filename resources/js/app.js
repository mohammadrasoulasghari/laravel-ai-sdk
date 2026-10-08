import Alpine from 'alpinejs';
import { themeStore } from './theme';

window.Alpine = Alpine;

Alpine.store('theme', themeStore());

/**
 * Reusable dialog behaviour for <x-ui.modal>. Closes on Escape and returns focus.
 */
Alpine.data('modal', (initiallyOpen = false) => ({
    open: initiallyOpen,
    trigger: null,

    show() {
        this.trigger = document.activeElement;
        this.open = true;
        this.$nextTick(() => this.$refs.panel?.querySelector('[autofocus], input, button, a')?.focus());
    },

    hide() {
        this.open = false;
        this.trigger?.focus();
    },
}));

Alpine.start();
