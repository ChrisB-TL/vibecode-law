import ShowcaseEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcase/ShowcaseEditController';
import ShowcaseDraftEditController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftEditController';
import HeadingSmall from '@/components/heading/heading-small';
import {
    DraftListItem,
    ShowcaseListItem,
    ShowcaseUserInfo,
} from '@/components/showcase/showcase-list-item';
import { ShowcaseSection } from '@/components/showcase/showcase-section';
import StaffAreaLayout from '@/layouts/staff-area/layout';
import { Head } from '@inertiajs/react';

interface ShowcaseModerationIndexProps {
    pendingShowcases: App.Http.Resources.Showcase.ShowcaseResource[];
    rejectedShowcases: App.Http.Resources.Showcase.ShowcaseResource[];
    pendingDrafts: App.Http.Resources.Showcase.ShowcaseDraftResource[];
    rejectedDrafts: App.Http.Resources.Showcase.ShowcaseDraftResource[];
}

export default function ShowcaseModerationIndex({
    pendingShowcases,
    rejectedShowcases,
    pendingDrafts,
    rejectedDrafts,
}: ShowcaseModerationIndexProps) {
    return (
        <StaffAreaLayout fullWidth>
            <Head title="Showcase Moderation" />

            <div className="space-y-6">
                <HeadingSmall
                    title="Showcase Moderation"
                    description="Review and moderate showcase submissions and changes"
                />

                <div className="grid gap-4 lg:grid-cols-2">
                    <ShowcaseSection
                        title="New Showcases"
                        items={pendingShowcases}
                        emptyMessage="No showcases pending review"
                    >
                        {(showcase) => (
                            <ShowcaseListItem
                                key={showcase.id}
                                showcase={showcase}
                                href={ShowcaseEditController.url({
                                    showcase: showcase.slug,
                                })}
                                linkIcon="edit"
                                metaSlot={
                                    showcase.user !== null &&
                                    showcase.user !== undefined && (
                                        <ShowcaseUserInfo
                                            user={showcase.user}
                                        />
                                    )
                                }
                            />
                        )}
                    </ShowcaseSection>

                    <ShowcaseSection
                        title="Showcase Changes"
                        items={pendingDrafts}
                        emptyMessage="No changes pending review"
                    >
                        {(draft) => (
                            <DraftListItem
                                key={draft.id}
                                draft={draft}
                                href={ShowcaseDraftEditController.url({
                                    draft: draft.id,
                                })}
                                linkIcon="edit"
                                metaSlot={
                                    draft.user !== null &&
                                    draft.user !== undefined && (
                                        <ShowcaseUserInfo user={draft.user} />
                                    )
                                }
                            />
                        )}
                    </ShowcaseSection>

                    <ShowcaseSection
                        title="Rejected Showcases"
                        items={rejectedShowcases}
                        emptyMessage="No rejected showcases"
                        defaultOpen={false}
                    >
                        {(showcase) => (
                            <ShowcaseListItem
                                key={showcase.id}
                                showcase={showcase}
                                href={ShowcaseEditController.url({
                                    showcase: showcase.slug,
                                })}
                                linkIcon="edit"
                                metaSlot={
                                    showcase.user !== null &&
                                    showcase.user !== undefined && (
                                        <ShowcaseUserInfo
                                            user={showcase.user}
                                        />
                                    )
                                }
                            />
                        )}
                    </ShowcaseSection>

                    <ShowcaseSection
                        title="Rejected Changes"
                        items={rejectedDrafts}
                        emptyMessage="No rejected changes"
                        defaultOpen={false}
                    >
                        {(draft) => (
                            <DraftListItem
                                key={draft.id}
                                draft={draft}
                                href={ShowcaseDraftEditController.url({
                                    draft: draft.id,
                                })}
                                linkIcon="edit"
                                metaSlot={
                                    draft.user !== null &&
                                    draft.user !== undefined && (
                                        <ShowcaseUserInfo user={draft.user} />
                                    )
                                }
                            />
                        )}
                    </ShowcaseSection>
                </div>
            </div>
        </StaffAreaLayout>
    );
}
