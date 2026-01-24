import TabNavLayout from '@/components/navigation/tab-nav-layout';
import { useStaffAreaNavItems } from '@/components/staff/staff-area-tab-nav';
import { type PropsWithChildren } from 'react';

interface StaffAreaLayoutProps extends PropsWithChildren {
    fullWidth?: boolean;
}

export default function StaffAreaLayout({
    children,
    fullWidth = false,
}: StaffAreaLayoutProps) {
    const items = useStaffAreaNavItems();

    return (
        <TabNavLayout
            title="Staff Area"
            items={items}
            ariaLabel="Staff Area"
            fullWidth={fullWidth}
        >
            {children}
        </TabNavLayout>
    );
}
