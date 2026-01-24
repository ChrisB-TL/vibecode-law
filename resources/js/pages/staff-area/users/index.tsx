import HeadingSmall from '@/components/heading/heading-small';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    ListCard,
    ListCardContent,
    ListCardEmpty,
    ListCardFooter,
    ListCardHeader,
    ListCardTitle,
} from '@/components/ui/list-card';
import { Pagination } from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { DeleteUserModal } from '@/components/user/admin/delete-user-modal';
import { SendPasswordResetButton } from '@/components/user/admin/send-password-reset-button';
import { UserListItem } from '@/components/user/admin/user-list-item';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { create, edit, toggleSubmissions } from '@/routes/staff/users';
import { type PaginatedData } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search, X } from 'lucide-react';
import { useState } from 'react';

interface UsersIndexProps {
    users: PaginatedData<App.Http.Resources.User.AdminUserResource>;
    roles: string[];
    filters: {
        search: string;
        role: string;
        blocked: boolean | null;
    };
}

export default function UsersIndex({ users, roles, filters }: UsersIndexProps) {
    const [search, setSearch] = useState(filters.search);
    const [deletingUser, setDeletingUser] =
        useState<App.Http.Resources.User.AdminUserResource | null>(null);

    const applyFilters = (newFilters: Partial<typeof filters>) => {
        router.get(
            window.location.pathname,
            {
                ...filters,
                ...newFilters,
                page: 1,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters({ search });
    };

    const clearFilters = () => {
        setSearch('');
        router.get(window.location.pathname, {}, { preserveState: true });
    };

    const handleToggleSubmissions = (
        user: App.Http.Resources.User.AdminUserResource,
    ) => {
        router.post(toggleSubmissions.url({ user: user.handle }), {});
    };

    const hasActiveFilters =
        filters.search !== '' ||
        filters.role !== '' ||
        filters.blocked !== null;

    return (
        <StaffAreaLayout fullWidth>
            <Head title="Users" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <HeadingSmall
                        title="Users"
                        description="Manage users, roles, and permissions"
                    />
                    <Button asChild>
                        <Link href={create.url()}>
                            <Plus className="mr-1.5 size-4" />
                            Create User
                        </Link>
                    </Button>
                </div>

                {/* Filters */}
                <div className="flex flex-wrap items-end gap-4">
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                            <Input
                                type="text"
                                placeholder="Search users..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-64 pl-9"
                            />
                        </div>
                        <Button type="submit" variant="outline">
                            Search
                        </Button>
                    </form>

                    <Select
                        value={filters.role}
                        onValueChange={(value) =>
                            applyFilters({ role: value === 'all' ? '' : value })
                        }
                    >
                        <SelectTrigger className="w-40">
                            <SelectValue placeholder="Filter by role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All roles</SelectItem>
                            {roles.map((role) => (
                                <SelectItem key={role} value={role}>
                                    {role}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select
                        value={
                            filters.blocked === null
                                ? 'all'
                                : filters.blocked === true
                                  ? 'blocked'
                                  : 'unblocked'
                        }
                        onValueChange={(value) =>
                            applyFilters({
                                blocked:
                                    value === 'all'
                                        ? null
                                        : value === 'blocked',
                            })
                        }
                    >
                        <SelectTrigger className="w-44">
                            <SelectValue placeholder="Submission status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All users</SelectItem>
                            <SelectItem value="blocked">Blocked</SelectItem>
                            <SelectItem value="unblocked">
                                Not Blocked
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    {hasActiveFilters === true && (
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={clearFilters}
                            className="gap-1.5"
                        >
                            <X className="size-4" />
                            Clear filters
                        </Button>
                    )}
                </div>

                {/* User list */}
                <ListCard>
                    <ListCardHeader>
                        <ListCardTitle>Users</ListCardTitle>
                        <Badge variant="secondary">
                            {users.meta.total}{' '}
                            {users.meta.total === 1 ? 'user' : 'users'}
                        </Badge>
                    </ListCardHeader>

                    {users.data.length > 0 ? (
                        <ListCardContent>
                            {users.data.map((user) => (
                                <UserListItem
                                    key={user.id}
                                    user={user}
                                    onEdit={() =>
                                        router.visit(
                                            edit.url({ user: user.handle }),
                                        )
                                    }
                                    onDelete={() => setDeletingUser(user)}
                                    onToggleSubmissions={() =>
                                        handleToggleSubmissions(user)
                                    }
                                    renderPasswordResetButton={() => (
                                        <SendPasswordResetButton user={user} />
                                    )}
                                />
                            ))}
                        </ListCardContent>
                    ) : (
                        <ListCardEmpty>No users found</ListCardEmpty>
                    )}

                    <ListCardFooter>
                        <Pagination meta={users.meta} links={users.links} />
                    </ListCardFooter>
                </ListCard>
            </div>

            {deletingUser !== null && (
                <DeleteUserModal
                    user={deletingUser}
                    isOpen={deletingUser !== null}
                    onOpenChange={(open) => {
                        if (open === false) {
                            setDeletingUser(null);
                        }
                    }}
                />
            )}
        </StaffAreaLayout>
    );
}
