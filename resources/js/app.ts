import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { initializeTheme } from '@/composables/useAppearance';
import AuthLayout from '@/layouts/AuthLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import StorefrontLayout from '@/layouts/StorefrontLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'PurpleBug';
const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('storefront/'):
                return StorefrontLayout;
            default:
                return StorefrontLayout;
        }
    },
    progress: {
        color: '#7C3AED',
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia);
        if (el) {
            app.mount(el);
        }
        return app;
    },
});

if (typeof window !== 'undefined') {
    // This will set light / dark mode on page load...
    initializeTheme();

    // This will listen for flash toast data from the server...
    initializeFlashToast();
}