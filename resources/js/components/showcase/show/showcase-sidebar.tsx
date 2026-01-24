import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { ArrowUp, Share2 } from 'lucide-react';

interface ShowcaseSidebarProps {
    monthlyRank: number | null;
    lifetimeRank: number | null;
    hasUpvoted?: boolean;
    upvotesCount?: number;
    onUpvote: () => void;
    linkedinShareUrl: string;
}

export function ShowcaseSidebar({
    monthlyRank,
    lifetimeRank,
    hasUpvoted,
    upvotesCount,
    onUpvote,
    linkedinShareUrl,
}: ShowcaseSidebarProps) {
    return (
        <div className="w-full lg:w-72">
            <Card className="py-4 lg:sticky lg:top-4 lg:py-6">
                <CardContent className="flex flex-row items-center justify-between gap-4 lg:flex-col">
                    <div className="grid w-full gap-4 lg:grid-cols-2 lg:py-2">
                        <RankDisplay
                            rank={monthlyRank}
                            label="Monthly Rank"
                            className={
                                monthlyRank !== null ? 'flex-1' : 'w-full'
                            }
                        />
                        <RankDisplay
                            rank={lifetimeRank}
                            label="Lifetime Rank"
                            className={
                                lifetimeRank !== null ? 'flex-1' : 'w-full'
                            }
                        />
                    </div>

                    <div className="flex w-full flex-col gap-4">
                        <Button
                            variant={
                                hasUpvoted === true ? 'default' : 'outline'
                            }
                            className="w-full"
                            onClick={onUpvote}
                        >
                            <ArrowUp className="size-4" />
                            Upvote
                            {upvotesCount !== undefined && upvotesCount > 0 && (
                                <span className="ml-1">
                                    &bull; {upvotesCount}
                                </span>
                            )}
                        </Button>
                        <Button variant="outline" className="w-full" asChild>
                            <a href={linkedinShareUrl} target="_blank">
                                <Share2 className="size-4" />
                                Share
                            </a>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

function RankDisplay({
    rank,
    label,
    className,
}: {
    rank: number | null;
    label: string;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex items-center gap-4 text-center lg:flex-col lg:gap-0',
                className,
            )}
        >
            <div className="text-xl font-bold text-neutral-900 lg:text-3xl dark:text-white">
                #{rank}
            </div>
            <div className="text-xs text-neutral-500 dark:text-neutral-400">
                {label}
            </div>
        </div>
    );
}
