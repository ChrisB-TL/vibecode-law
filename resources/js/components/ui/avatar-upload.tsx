import { useRef, useState } from 'react';
import { Camera, Trash2 } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

interface AvatarUploadProps {
    name: string;
    currentAvatarUrl?: string | null;
    fallbackName: string;
    className?: string;
    allowRemove?: boolean;
    error?: string;
}

export function AvatarUpload({
    name,
    currentAvatarUrl,
    fallbackName,
    className,
    allowRemove = false,
    error,
}: AvatarUploadProps) {
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [isRemoved, setIsRemoved] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);
    const getInitials = useInitials();

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            const url = URL.createObjectURL(file);
            setPreviewUrl(url);
            setIsRemoved(false);
        }
    };

    const handleClick = () => {
        inputRef.current?.click();
    };

    const handleRemove = () => {
        setPreviewUrl(null);
        setIsRemoved(true);
        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };

    const displayUrl = isRemoved === true ? null : (previewUrl ?? currentAvatarUrl);
    const hasAvatar = displayUrl !== null;
    const showRemoveButton = allowRemove === true && (hasAvatar === true || isRemoved === false) && currentAvatarUrl !== null;

    return (
        <div className={cn('flex flex-col items-center gap-3', className)}>
            <button
                type="button"
                onClick={handleClick}
                className="group relative cursor-pointer"
            >
                <Avatar className="h-24 w-24">
                    <AvatarImage src={displayUrl ?? undefined} alt={fallbackName} />
                    <AvatarFallback className="bg-neutral-200 text-2xl text-black dark:bg-neutral-700 dark:text-white">
                        {getInitials(fallbackName)}
                    </AvatarFallback>
                </Avatar>
                <div className="absolute inset-0 flex items-center justify-center rounded-full bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                    <Camera className="h-8 w-8 text-white" />
                </div>
            </button>
            <input
                ref={inputRef}
                type="file"
                name={name}
                accept="image/png,image/jpeg,image/gif,image/webp"
                onChange={handleFileChange}
                className="sr-only"
            />
            {isRemoved === true && (
                <input type="hidden" name="remove_avatar" value="1" />
            )}
            <div className="flex flex-col items-center gap-1">
                <span className="text-sm text-muted-foreground">
                    Click to upload avatar
                </span>
                {showRemoveButton === true && isRemoved === false && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="h-auto px-2 py-1 text-xs text-destructive hover:bg-destructive/10 hover:text-destructive"
                        onClick={handleRemove}
                    >
                        <Trash2 className="mr-1 h-3 w-3" />
                        Remove avatar
                    </Button>
                )}
                {isRemoved === true && (
                    <span className="text-xs text-amber-600 dark:text-amber-400">
                        Avatar will be removed on save
                    </span>
                )}
            </div>
            {error !== undefined && (
                <p className="text-sm text-destructive">{error}</p>
            )}
        </div>
    );
}
