import { useCallback, useState } from 'react';

interface UseModalFormReturn<TErrors> {
    isOpen: boolean;
    setIsOpen: (open: boolean) => void;
    handleOpenChange: (open: boolean) => void;
    isSubmitting: boolean;
    setIsSubmitting: (submitting: boolean) => void;
    errors: TErrors;
    setErrors: (errors: TErrors) => void;
    clearErrors: () => void;
}

/**
 * Hook for managing common modal form state patterns.
 *
 * Provides:
 * - Open/close state with automatic error clearing on close
 * - Form submission state (isSubmitting)
 * - Validation errors state
 *
 * For controlled modals (where parent manages open state), you can ignore
 * `isOpen`, `setIsOpen`, and `handleOpenChange` and just use the error
 * and submission state helpers.
 */
export function useModalForm<
    TErrors extends Record<string, string | undefined> = Record<
        string,
        string | undefined
    >,
>(): UseModalFormReturn<TErrors> {
    const [isOpen, setIsOpen] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<TErrors>({} as TErrors);

    const clearErrors = useCallback(() => {
        setErrors({} as TErrors);
    }, []);

    const handleOpenChange = useCallback((open: boolean) => {
        if (open === false) {
            setErrors({} as TErrors);
        }
        setIsOpen(open);
    }, []);

    return {
        isOpen,
        setIsOpen,
        handleOpenChange,
        isSubmitting,
        setIsSubmitting,
        errors,
        setErrors,
        clearErrors,
    };
}
