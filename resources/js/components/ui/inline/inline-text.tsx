import { cn } from '@/lib/utils';
import { forwardRef, useState } from 'react';

interface InlineTextProps extends Omit<React.InputHTMLAttributes<HTMLInputElement>, 'size'> {
    weight?: 'normal' | 'medium' | 'semibold' | 'bold';
    error?: string;
    textClasses?: string;
}

const weightClasses = {
    normal: 'font-normal',
    medium: 'font-medium',
    semibold: 'font-semibold',
    bold: 'font-bold',
};

export const InlineText = forwardRef<HTMLInputElement, InlineTextProps>(
    ({ className, weight = 'normal', textClasses = 'text-base', error, ...props }, ref) => {
        const [isFocused, setIsFocused] = useState(false);

        return (
            <div className="relative">
                <input
                    ref={ref}
                    type="text"
                    className={cn(
                        'w-full border-0 bg-transparent px-0 py-1 outline-none transition-all',
                        'placeholder:text-neutral-400 dark:placeholder:text-neutral-500',
                        'focus:ring-0',
                        textClasses,
                        weightClasses[weight],
                        isFocused === false && 'cursor-pointer',
                        isFocused === true &&
                            'border-b-2 border-amber-500 dark:border-amber-400',
                        error !== undefined &&
                            'border-b-2 border-red-500 dark:border-red-400',
                        'text-neutral-900 dark:text-white',
                        className,
                    )}
                    onFocus={(e) => {
                        setIsFocused(true);
                        props.onFocus?.(e);
                    }}
                    onBlur={(e) => {
                        setIsFocused(false);
                        props.onBlur?.(e);
                    }}
                    {...props}
                />
                {error !== undefined && (
                    <p className="mt-1 text-sm text-red-500 dark:text-red-400">{error}</p>
                )}
            </div>
        );
    },
);

InlineText.displayName = 'InlineText';
