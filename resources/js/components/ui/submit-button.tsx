import { Button, buttonVariants } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { VariantProps } from 'class-variance-authority';

interface SubmitButtonProps
    extends Omit<React.ComponentProps<'button'>, 'type'>,
        VariantProps<typeof buttonVariants> {
    processing?: boolean;
    processingLabel?: React.ReactNode;
    asChild?: boolean;
}

function SubmitButton({
    processing = false,
    processingLabel,
    disabled,
    children,
    ...props
}: SubmitButtonProps) {
    return (
        <Button type="submit" disabled={disabled || processing} {...props}>
            {processing && <Spinner />}
            {processing && processingLabel ? processingLabel : children}
        </Button>
    );
}

export { SubmitButton };
