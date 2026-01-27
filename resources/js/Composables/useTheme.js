import { ref, watch, provide, inject } from 'vue';

// Export themeKey so it can be used consistently
export const themeKey = Symbol('theme');

export function useTheme() {
    const darkMode = ref(localStorage.getItem('darkMode') === 'true');

    const toggleTransition = () => {
        const root = document.documentElement;
        root.classList.add('no-transition');
        setTimeout(() => {
            root.classList.remove('no-transition');
        }, 0);
    };

    watch(darkMode, (newValue) => {
        toggleTransition();

        if (newValue) {
            document.body.classList.add('dark');
        } else {
            document.body.classList.remove('dark');
        }

        localStorage.setItem('darkMode', newValue);
    }, { immediate: true });

    const themeSwitcher = () => {
        darkMode.value = !darkMode.value;
    };

    return {
        darkMode,
        themeSwitcher,
    };
}

export function provideTheme(app = null) {
    const theme = useTheme();
    if (app) {
        // Provide to Vue app instance using the same key
        app.provide(themeKey, theme);
    } else {
        // Provide in composition API context (only works in setup context)
        provide(themeKey, theme);
    }
    return theme;
}

export function injectTheme() {
    const theme = inject(themeKey);
    if (!theme) {
        return useTheme();
    }
    return theme;
}

