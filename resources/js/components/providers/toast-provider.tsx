import { type PropsWithChildren } from 'react';

import { Toaster } from '@/components/ui/toaster';
import { useFlashToasts } from '@/hooks/use-flash-toasts';

export function ToastWrapper({ children }: PropsWithChildren) {
    useFlashToasts();

    return (
        <>
            {children}
            <Toaster />
        </>
    );
}
