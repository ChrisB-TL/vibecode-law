import TabNavLayout from '@/components/navigation/tab-nav-layout';
import { userAreaNavItems } from '@/components/user/user-area-tab-nav';
import { type PropsWithChildren } from 'react';

interface UserAreaLayoutProps extends PropsWithChildren {
    fullWidth?: boolean;
}

export default function UserAreaLayout({
    children,
    fullWidth = false,
}: UserAreaLayoutProps) {
    return (
        <TabNavLayout
            title="User Area"
            items={userAreaNavItems}
            ariaLabel="User Area"
            fullWidth={fullWidth}
        >
            {children}
        </TabNavLayout>
    );
}
