import ShowcaseUpvoteController from '@/actions/App/Http/Controllers/Showcase/ShowcaseUpvoteController';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ArrowUp } from 'lucide-react';
import { useState } from 'react';
import { AuthPromptModal } from './upvote-prompt-modal';

interface UpvoteButtonProps {
    showcaseSlug: string;
    upvotesCount: number;
    hasUpvoted: boolean;
}

export function UpvoteButton({
    showcaseSlug,
    upvotesCount,
    hasUpvoted,
}: UpvoteButtonProps) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const isAuthenticated = auth?.user !== undefined && auth?.user !== null;
    const [showAuthModal, setShowAuthModal] = useState(false);

    const handleUpvote = () => {
        if (isAuthenticated === false) {
            setShowAuthModal(true);
            return;
        }

        router.post(
            ShowcaseUpvoteController.url({ showcase: showcaseSlug }),
            {},
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Button
                variant={hasUpvoted ? 'default' : 'outline'}
                size="sm"
                onClick={handleUpvote}
                className="flex h-auto flex-col gap-1 px-3 py-2"
            >
                <ArrowUp
                    className={cn(
                        'size-5',
                        hasUpvoted === true && 'text-primary-foreground',
                    )}
                />
                <span className="text-sm font-semibold">{upvotesCount}</span>
            </Button>
            <AuthPromptModal
                isOpen={showAuthModal}
                onClose={() => setShowAuthModal(false)}
            />
        </>
    );
}
