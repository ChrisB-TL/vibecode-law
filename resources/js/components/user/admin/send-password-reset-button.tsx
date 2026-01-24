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
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { sendPasswordReset } from '@/routes/staff/users';
import { router } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { useState } from 'react';

interface SendPasswordResetButtonProps {
    user: App.Http.Resources.User.AdminUserResource;
}

export function SendPasswordResetButton({
    user,
}: SendPasswordResetButtonProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [isSending, setIsSending] = useState(false);

    const handleSend = () => {
        setIsSending(true);

        router.post(
            sendPasswordReset.url({ user: user.handle }),
            {},
            {
                onFinish: () => {
                    setIsSending(false);
                    setIsOpen(false);
                },
            },
        );
    };

    return (
        <AlertDialog open={isOpen} onOpenChange={setIsOpen}>
            <AlertDialogTrigger asChild>
                <DropdownMenuItem onSelect={(e) => e.preventDefault()}>
                    <KeyRound className="mr-2 size-4" />
                    Send password reset
                </DropdownMenuItem>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Send password reset</AlertDialogTitle>
                    <AlertDialogDescription>
                        This will send a password reset email to{' '}
                        <strong>{user.email}</strong>. The user will receive
                        instructions to create a new password.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={isSending}>
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleSend}
                        disabled={isSending}
                    >
                        {isSending ? 'Sending...' : 'Send email'}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
