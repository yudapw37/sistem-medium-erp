import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import { provideTheme } from './Composables/useTheme';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // Setup plugin first
        app.use(plugin);
        
        // Provide theme to the app after plugin is setup
        provideTheme(app);
        
        // Make route available globally in Vue templates
        if (typeof window !== 'undefined' && window.route) {
            app.config.globalProperties.route = window.route;
        }
        
        // Setup toast
        app.use(Toast, {
            position: 'top-right',
            timeout: 3000,
        });
        
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

