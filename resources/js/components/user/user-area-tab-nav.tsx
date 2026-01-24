import { TabNav, type TabNavItem } from '@/components/navigation/tab-nav';
import { edit as editAppearance } from '@/routes/user-area/appearance';
import { edit as editPassword } from '@/routes/user-area/password';
import { edit as editProfile } from '@/routes/user-area/profile';
import { index as showcasesIndex } from '@/routes/user-area/showcases';

export const userAreaNavItems: TabNavItem[] = [
    {
        title: 'Profile',
        href: editProfile().url,
    },
    {
        title: 'My Showcases',
        href: showcasesIndex().url,
    },
    {
        title: 'Password',
        href: editPassword().url,
    },
    {
        title: 'Appearance',
        href: editAppearance().url,
    },
];

export function UserAreaTabNav() {
    return <TabNav items={userAreaNavItems} ariaLabel="User Area" />;
}
