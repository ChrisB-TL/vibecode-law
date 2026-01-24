import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

interface ThumbnailImageProps {
    url?: string | null;
    fallbackText: string;
    alt: string;
    rectString?: string | null;
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

const sizeClasses = {
    sm: 'size-10',
    md: 'size-14',
    lg: 'size-20',
};

const fontSizeClasses = {
    sm: 'text-xl',
    md: 'text-2xl',
    lg: 'text-3xl',
};

export function ThumbnailImage({
    url,
    fallbackText,
    alt,
    rectString,
    size = 'md',
    className,
}: ThumbnailImageProps) {
    const { transformImages } = usePage<SharedData>().props;

    if (url) {
        const src =
            transformImages === true
                ? `${url}?w=100${rectString ? `&${rectString}` : ''}`
                : url;

        return (
            <img
                src={src}
                alt={alt}
                className={cn(
                    sizeClasses[size],
                    'shrink-0 rounded-lg object-cover',
                    className,
                )}
            />
        );
    }

    return (
        <div
            className={cn(
                sizeClasses[size],
                'flex shrink-0 items-center justify-center rounded-lg bg-neutral-100 dark:bg-neutral-800',
                className,
            )}
        >
            <span
                className={cn(
                    fontSizeClasses[size],
                    'font-bold text-neutral-400 dark:text-neutral-500',
                )}
            >
                {fallbackText.charAt(0)}
            </span>
        </div>
    );
}
