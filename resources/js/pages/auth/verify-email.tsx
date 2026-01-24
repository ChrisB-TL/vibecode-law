import { StatusMessage } from '@/components/ui/status-message';
import { SubmitButton } from '@/components/ui/submit-button';
import TextLink from '@/components/ui/text-link';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/react';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <AuthLayout title="Verify email">
            <Head title="Email verification" />

            <StatusMessage
                message={
                    status === 'verification-link-sent'
                        ? 'A new verification link has been sent to the email address you provided during registration.'
                        : undefined
                }
                className="mb-4"
            />

            <Form {...send.form()} className="space-y-6 text-center">
                {({ processing }) => (
                    <>
                        <SubmitButton
                            processing={processing}
                            variant="secondary"
                        >
                            Resend verification email
                        </SubmitButton>

                        <TextLink
                            href={logout()}
                            className="mx-auto block text-sm"
                        >
                            Log out
                        </TextLink>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
