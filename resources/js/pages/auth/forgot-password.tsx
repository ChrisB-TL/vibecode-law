import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { StatusMessage } from '@/components/ui/status-message';
import { SubmitButton } from '@/components/ui/submit-button';
import TextLink from '@/components/ui/text-link';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <AuthLayout title="Forgot password">
            <Head title="Forgot password" />

            <StatusMessage message={status} className="mb-4" />

            <div className="space-y-6">
                <Form {...email.form()}>
                    {({ processing, errors }) => (
                        <>
                            <FormField
                                label="Email address"
                                htmlFor="email"
                                error={errors.email}
                            >
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="off"
                                    autoFocus
                                    placeholder="email@example.com"
                                />
                            </FormField>

                            <div className="my-6 flex items-center justify-start">
                                <SubmitButton
                                    className="w-full"
                                    processing={processing}
                                    data-test="email-password-reset-link-button"
                                >
                                    Email password reset link
                                </SubmitButton>
                            </div>
                        </>
                    )}
                </Form>

                <div className="space-x-1 text-center text-sm text-muted-foreground">
                    <span>Or, return to</span>
                    <TextLink href={login()}>log in</TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
