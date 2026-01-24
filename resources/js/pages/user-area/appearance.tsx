import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance/appearance-tabs';
import HeadingSmall from '@/components/heading/heading-small';
import UserAreaLayout from '@/layouts/user-area/layout';

export default function Appearance() {
    return (
        <UserAreaLayout>
            <Head title="Appearance settings" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />
                <AppearanceTabs />
            </div>
        </UserAreaLayout>
    );
}
