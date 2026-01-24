import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';

export function usePermissions() {
    const { auth } = usePage<SharedData>().props;
    const permissions = useMemo(
        () => auth?.permissions ?? [],
        [auth?.permissions],
    );

    const isAdmin = useMemo(() => permissions.includes('*'), [permissions]);

    const hasPermission = useCallback(
        (permission: string): boolean => {
            if (isAdmin) {
                return true;
            }

            return permissions.includes(permission);
        },
        [permissions, isAdmin],
    );

    const hasAnyPermission = useCallback(
        (requiredPermissions: string[]): boolean => {
            if (isAdmin) {
                return true;
            }

            return requiredPermissions.some((permission) =>
                permissions.includes(permission),
            );
        },
        [permissions, isAdmin],
    );

    return {
        permissions,
        isAdmin,
        hasPermission,
        hasAnyPermission,
    };
}
