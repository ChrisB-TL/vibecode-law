import AppearanceToggleDropdown from '@/components/appearance/appearance-dropdown';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function PublicFooter() {
    const { legalPages } = usePage<SharedData>().props;
    const currentYear = new Date().getFullYear();

    return (
        <footer className="border-t border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
            <div className="mx-auto flex max-w-5xl flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row">
                <p className="text-sm text-neutral-500 dark:text-neutral-400">
                    &copy; {currentYear} vibecode.law. All rights reserved.
                </p>
                <nav className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 sm:gap-6">
                    {legalPages.map((page) => (
                        <Link
                            key={page.route}
                            href={page.route}
                            className="text-sm text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            {page.title}
                        </Link>
                    ))}
                    <AppearanceToggleDropdown />
                </nav>
            </div>
        </footer>
    );
}
