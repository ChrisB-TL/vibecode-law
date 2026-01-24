import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { CodeXml } from 'lucide-react';

export default function AppLogo() {
    return (
        <Link
            href={home()}
            className="flex cursor-pointer items-center gap-2 transition-opacity hover:opacity-80"
        >
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <CodeXml className="h-5 w-5" aria-hidden="true" />
            </div>
            <span className="font-heading text-xl font-bold tracking-tight">
                vibecode<span className="text-muted-foreground">.law</span>
            </span>
        </Link>
    );
}
