import { cn } from '@/lib/utils';

interface StatusMessageProps {
    message?: string | null;
    className?: string;
}

export function StatusMessage({ message, className }: StatusMessageProps) {
    if (!message) {
        return null;
    }

    return (
        <div
            className={cn(
                'text-center text-sm font-medium text-green-600',
                className,
            )}
        >
            {message}
        </div>
    );
}
