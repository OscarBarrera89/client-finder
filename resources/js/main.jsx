import '../css/app.css';

import { aplicarTemaDeUsuario } from '@/lib/tema';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.jsx`, import.meta.glob('./pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        aplicarTemaDeUsuario(props.initialPage.props.auth?.user);

        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});

// Reaplicar el tema al navegar y tras cada envío correcto (al cambiarlo, al entrar o al salir)
const reaplicarTema = (event) => aplicarTemaDeUsuario(event.detail.page.props.auth?.user);
router.on('navigate', reaplicarTema);
router.on('success', reaplicarTema);
