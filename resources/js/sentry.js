import * as Sentry from '@sentry/react';

if (import.meta.env.VITE_ENABLE_SENTRY === 'true') {
    Sentry.init({
        dsn: 'https://fcc96842fb61b3244d58e53abf23a0f3@o4510765350322176.ingest.de.sentry.io/4510765353336912',
        sendDefaultPii: import.meta.env.VITE_SENTRY_PII ?? false,
        tracesSampleRate: import.meta.env.VITE_SENTRY_TRACE_SAMPLE_RATE ?? 0.0,
        replaysSessionSampleRate:
            import.meta.env.VITE_SENTRY_REPLAY_SAMPLE_RATE ?? 0.0,
        replaysOnErrorSampleRate:
            import.meta.env.VITE_SENTRY_REPLAY_ERROR_SAMPLE_RATE ?? 0.0,
    });
}
