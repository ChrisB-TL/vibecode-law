import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { destroy } from '@/routes/staff/users';
import { router } from '@inertiajs/react';
import { useState } from 'react';

interface DeleteUserModalProps {
    user: App.Http.Resources.User.AdminUserResource;
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}

export function DeleteUserModal({
    user,
    isOpen,
    onOpenChange,
}: DeleteUserModalProps) {
    const [isDeleting, setIsDeleting] = useState(false);

    const handleDelete = () => {
        setIsDeleting(true);

        router.delete(destroy.url({ user: user.handle }), {
            onFinish: () => {
                setIsDeleting(false);
                onOpenChange(false);
            },
        });
    };

    return (
        <AlertDialog open={isOpen} onOpenChange={onOpenChange}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete user</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete{' '}
                        <strong>
                            {user.first_name} {user.last_name}
                        </strong>
                        ? This action cannot be undone. The user's showcases
                        will be preserved but unlinked from this account.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={isDeleting}>
                        Cancel
                    </AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleDelete}
                        disabled={isDeleting}
                        className="bg-red-600 hover:bg-red-700 focus:ring-red-600"
                    >
                        {isDeleting ? 'Deleting...' : 'Delete'}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
