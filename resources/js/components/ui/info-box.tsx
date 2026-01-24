import { cva, type VariantProps } from 'class-variance-authority';

import { cn } from '@/lib/utils';

const infoBoxVariants = cva(
    'flex items-start gap-3 rounded-lg p-4 text-sm',
    {
        variants: {
            variant: {
                info: 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                success: 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                warning: 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                error: 'border border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300',
            },
        },
        defaultVariants: {
            variant: 'info',
        },
    },
);

interface InfoBoxProps
    extends React.HTMLAttributes<HTMLDivElement>,
        VariantProps<typeof infoBoxVariants> {
    icon?: React.ReactNode;
}

function InfoBox({ variant, icon, className, children, ...props }: InfoBoxProps) {
    return (
        <div className={cn(infoBoxVariants({ variant }), className)} {...props}>
            {icon !== undefined && <span className="mt-0.5 shrink-0">{icon}</span>}
            <div className="flex-1">{children}</div>
        </div>
    );
}

function InfoBoxTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
    return (
        <h3
            className={cn('font-medium', className)}
            {...props}
        />
    );
}

function InfoBoxDescription({ className, ...props }: React.HTMLAttributes<HTMLParagraphElement>) {
    return (
        <p
            className={cn('mt-1 text-sm opacity-90', className)}
            {...props}
        />
    );
}

export { InfoBox, InfoBoxTitle, InfoBoxDescription, infoBoxVariants };
