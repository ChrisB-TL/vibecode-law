import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FormField } from '@/components/ui/form-field';
import { SubmitButton } from '@/components/ui/submit-button';
import { Textarea } from '@/components/ui/textarea';
import { useModalForm } from '@/hooks/use-modal-form';
import { router } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useState } from 'react';

interface RejectShowcaseModalProps {
    showcase: { title: string };
    rejectUrl: string;
}

export function RejectShowcaseModal({
    showcase,
    rejectUrl,
}: RejectShowcaseModalProps) {
    const {
        isOpen,
        handleOpenChange: baseHandleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
    } = useModalForm<{ reason?: string }>();

    const [reason, setReason] = useState('');

    const handleReject = (e: React.FormEvent) => {
        e.preventDefault();

        if (reason.trim() === '') {
            setErrors({ reason: 'Please provide a reason for rejection.' });
            return;
        }

        setIsSubmitting(true);
        setErrors({});

        router.post(
            rejectUrl,
            { reason },
            {
                onSuccess: () => {
                    baseHandleOpenChange(false);
                    setReason('');
                },
                onError: (newErrors) => {
                    if (newErrors.reason !== undefined) {
                        setErrors({ reason: newErrors.reason });
                    }
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    const handleOpenChange = (open: boolean) => {
        if (open === false) {
            setReason('');
        }
        baseHandleOpenChange(open);
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button
                    variant="outline"
                    size="sm"
                    className="border-red-200 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-400 dark:hover:bg-red-900"
                    onClick={(e) => e.stopPropagation()}
                >
                    <X className="size-4" />
                    Reject
                </Button>
            </DialogTrigger>
            <DialogContent onClick={(e) => e.stopPropagation()}>
                <form onSubmit={handleReject}>
                    <DialogHeader>
                        <DialogTitle>Reject Showcase</DialogTitle>
                        <DialogDescription>
                            Please provide a reason for rejecting "
                            {showcase.title}". This will be shared with the
                            author.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="mt-4">
                        <FormField
                            label="Reason for rejection"
                            htmlFor="reason"
                            error={errors.reason}
                        >
                            <Textarea
                                id="reason"
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                                placeholder="Explain why this showcase is being rejected..."
                                rows={4}
                                className="resize-none"
                                disabled={isSubmitting}
                                aria-invalid={
                                    errors.reason !== undefined
                                        ? true
                                        : undefined
                                }
                            />
                        </FormField>
                    </div>

                    <DialogFooter className="mt-6">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => handleOpenChange(false)}
                            disabled={isSubmitting}
                        >
                            Cancel
                        </Button>
                        <SubmitButton
                            variant="destructive"
                            processing={isSubmitting}
                            processingLabel="Rejecting..."
                        >
                            Reject Showcase
                        </SubmitButton>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
