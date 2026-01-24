import LinkedinAuthRedirectController from '@/actions/App/Http/Controllers/Auth/LinkedinAuthRedirectController';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { LogIn } from 'lucide-react';

interface AuthPromptModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export function AuthPromptModal({ isOpen, onClose }: AuthPromptModalProps) {
    return (
        <Dialog
            open={isOpen}
            onOpenChange={(open) => open === false && onClose()}
        >
            <DialogContent className="sm:max-w-md">
                <DialogHeader className="flex flex-col items-center text-center">
                    <div className="mb-3 rounded-full border border-border bg-card p-0.5 shadow-sm">
                        <div className="rounded-full border border-border bg-muted p-2.5">
                            <LogIn className="size-6 text-foreground" />
                        </div>
                    </div>
                    <DialogTitle>Sign in to upvote</DialogTitle>
                    <DialogDescription className="text-center">
                        Join our community to upvote projects and showcase your
                        own work.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter className="items-center justify-center sm:justify-center">
                    <a
                        href={LinkedinAuthRedirectController.url()}
                        className="inline-block cursor-pointer hover:brightness-90"
                    >
                        <img
                            src="/static/sign-in-with-linkedin.png"
                            alt="Login with Linkedin"
                        />
                    </a>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
