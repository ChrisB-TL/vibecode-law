import ShowcaseDraftUpdateController from '@/actions/App/Http/Controllers/Showcase/ManageShowcaseDraft/ShowcaseDraftUpdateController';
import ShowcaseShowController from '@/actions/App/Http/Controllers/Showcase/Public/ShowcaseShowController';
import ApproveDraftController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/ApproveDraftController';
import RejectDraftController from '@/actions/App/Http/Controllers/Staff/ShowcaseModeration/RejectDraftController';
import UserShowcaseIndexController from '@/actions/App/Http/Controllers/User/UserShowcaseIndexController';
import { ShowcaseForm } from '@/components/showcase/form/showcase-form';
import { normalizeDraft } from '@/components/showcase/form/types';
import { usePermissions } from '@/hooks/use-permissions';
import { home } from '@/routes';
import { type FrontendEnum } from '@/types';

interface EditDraftProps {
    draft: App.Http.Resources.Showcase.ShowcaseDraftResource;
    practiceAreas: App.Http.Resources.PracticeAreaResource[];
    sourceStatuses: FrontendEnum<number>[];
}

export default function EditDraft(props: EditDraftProps) {
    // Use key to reset all form state when draft changes
    return <EditDraftWrapper key={props.draft.id} {...props} />;
}

function EditDraftWrapper({
    draft,
    practiceAreas,
    sourceStatuses,
}: EditDraftProps) {
    const { hasPermission } = usePermissions();

    // Normalize data for the form
    const initialData = normalizeDraft(draft);

    // Build form action - PUT to update the draft
    const formAction = ShowcaseDraftUpdateController.form(draft.id);

    // Determine permissions and capabilities
    const canApproveReject = hasPermission('showcase.approve-reject');
    const canModerate =
        canApproveReject &&
        (draft.status.name === 'Pending' || draft.status.name === 'Rejected');

    // Can submit if draft is not pending or rejected
    const canSubmit =
        draft.status.name === 'Draft' || draft.status.name === 'Rejected';

    // Build breadcrumbs - link back to parent showcase
    const breadcrumbs = [
        { label: 'Home', href: home.url() },
        { label: 'My Showcases', href: UserShowcaseIndexController.url() },
        {
            label: draft.showcase_title,
            href: ShowcaseShowController.url({ showcase: draft.showcase_slug }),
        },
        { label: 'Edit Draft' },
    ];

    // Build page title
    const pageTitle = `Edit Draft: ${draft.showcase_title}`;

    // Build preview URL - link to parent showcase
    const previewUrl = ShowcaseShowController.url({
        showcase: draft.showcase_slug,
    });

    // Build moderation URLs
    const moderationUrls = canModerate
        ? {
              approveUrl: ApproveDraftController.url(draft.id),
              rejectUrl:
                  draft.status.name === 'Pending'
                      ? RejectDraftController.url(draft.id)
                      : undefined,
          }
        : undefined;

    return (
        <ShowcaseForm
            mode="edit-draft"
            formAction={formAction}
            initialData={initialData}
            practiceAreas={practiceAreas}
            sourceStatuses={sourceStatuses}
            imageDeletionConfig={{
                removedImagesFieldName: 'removed_images',
                deletedNewImagesFieldName: 'deleted_new_images',
            }}
            moderationUrls={moderationUrls}
            previewUrl={previewUrl}
            breadcrumbs={breadcrumbs}
            pageTitle={pageTitle}
            showSlugField={false}
            canSubmit={canSubmit}
        />
    );
}
