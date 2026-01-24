import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { Check } from 'lucide-react';
import { useState } from 'react';

interface ApproveShowcaseButtonProps {
    showcase: { title: string };
    approveUrl: string;
}

export function ApproveShowcaseButton({
    showcase,
    approveUrl,
}: ApproveShowcaseButtonProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleApprove = (e: React.MouseEvent) => {
        e.preventDefault();
        setIsSubmitting(true);

        router.post(
            approveUrl,
            {},
            {
                onSuccess: () => {
                    setIsOpen(false);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            },
        );
    };

    return (
        <AlertDialog open={isOpen} onOpenChange={setIsOpen}>
            <AlertDialogTrigger asChild>
                <Button
                    variant="outline"
                    size="sm"
                    className="border-green-200 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-400 dark:hover:bg-green-900"
                    onClick={(e) => e.stopPropagation()}
                >
                    <Check className="size-4" />
                    Approve
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent onClick={(e) => e.stopPropagation()}>
                <AlertDialogHeader>
                    <AlertDialogTitle>Approve Showcase</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to approve "{showcase.title}"?
                        This will make it publicly visible and notify the
                        author.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={isSubmitting}>
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleApprove}
                        disabled={isSubmitting}
                        className="bg-green-600 hover:bg-green-700"
                    >
                        {isSubmitting ? 'Approving...' : 'Approve'}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
