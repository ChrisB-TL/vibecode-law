import { toUrl } from '@/lib/utils';
import type { InertiaLinkProps } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';

export function useActiveUrl() {
    const page = usePage();
    // Can use local host, as only need pathname and avoids SSR issues
    const currentUrlPath = new URL(page.url, 'http://localhost').pathname;

    function urlIsActive(
        urlToCheck: NonNullable<InertiaLinkProps['href']>,
        currentUrl?: string,
    ) {
        const urlToCompare = currentUrl ?? currentUrlPath;
        return toUrl(urlToCheck) === urlToCompare;
    }

    return {
        currentUrl: currentUrlPath,
        urlIsActive,
    };
}
