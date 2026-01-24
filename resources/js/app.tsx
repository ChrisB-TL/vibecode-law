import '../css/app.css';
import './sentry';

import { createInertiaApp } from '@inertiajs/react';
import * as Sentry from '@sentry/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';

import { ToastWrapper } from './components/providers/toast-provider';
import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const root = createRoot(el, {
            onUncaughtError: Sentry.reactErrorHandler((error, errorInfo) => {
                console.warn('Uncaught error', error, errorInfo.componentStack);
            }),
            onCaughtError: Sentry.reactErrorHandler(),
            onRecoverableError: Sentry.reactErrorHandler(),
        });

        root.render(
            <StrictMode>
                <ToastWrapper>
                    <App {...props} />
                </ToastWrapper>
            </StrictMode>,
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
