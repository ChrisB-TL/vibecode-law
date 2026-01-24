import { Button } from '@/components/ui/button';
import { Transition } from '@headlessui/react';
import { Check, ExternalLink, Save, Send } from 'lucide-react';

export interface SaveButtonGroupProps {
    recentlySuccessful: boolean;
    processing: boolean;
    saveButtonText: string;
    showSubmitButton: boolean;
    className?: string;
    size?: 'default' | 'sm' | 'lg' | 'icon';
    previewUrl?: string;
}

export function SaveButtonGroup({
    recentlySuccessful,
    processing,
    saveButtonText,
    showSubmitButton,
    className,
    size,
    previewUrl,
}: SaveButtonGroupProps) {
    return (
        <div className={className}>
            <Transition
                show={recentlySuccessful}
                enter="transition ease-in-out duration-200"
                enterFrom="opacity-0 translate-x-2"
                leave="transition ease-in-out duration-200"
                leaveTo="opacity-0 translate-x-2"
            >
                <span className="flex items-center gap-1.5 text-sm font-medium text-green-600 dark:text-green-400">
                    <Check className="size-4" />
                    Saved
                </span>
            </Transition>
            {previewUrl !== undefined && (
                <Button variant="outline" size={size} asChild>
                    <a href={previewUrl} target="_blank" rel="noopener">
                        <ExternalLink className="size-4" />
                        Preview
                    </a>
                </Button>
            )}
            <Button
                type="submit"
                name="submit"
                value=""
                disabled={processing}
                size={size}
            >
                <Save className="size-4" />
                {processing ? 'Saving...' : saveButtonText}
            </Button>
            {showSubmitButton === true && (
                <Button
                    type="submit"
                    name="submit"
                    value="1"
                    disabled={processing}
                    size={size}
                    className="bg-green-600 hover:bg-green-700 dark:bg-green-600 dark:hover:bg-green-700"
                >
                    <Send className="size-4" />
                    {processing ? 'Submitting...' : 'Save & Submit'}
                </Button>
            )}
        </div>
    );
}
