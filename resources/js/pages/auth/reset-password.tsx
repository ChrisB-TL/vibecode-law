import { FormField } from '@/components/ui/form-field';
import { Input } from '@/components/ui/input';
import { SubmitButton } from '@/components/ui/submit-button';
import AuthLayout from '@/layouts/auth-layout';
import { update } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';

interface ResetPasswordProps {
    token: string;
    email: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    return (
        <AuthLayout title="Reset password">
            <Head title="Reset password" />

            <Form
                {...update.form()}
                transform={(data) => ({ ...data, token, email })}
                resetOnSuccess={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <FormField
                            label="Email"
                            htmlFor="email"
                            error={errors.email}
                        >
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                value={email}
                                readOnly
                            />
                        </FormField>

                        <FormField
                            label="Password"
                            htmlFor="password"
                            error={errors.password}
                        >
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                autoComplete="new-password"
                                autoFocus
                                placeholder="Password"
                            />
                        </FormField>

                        <FormField
                            label="Confirm password"
                            htmlFor="password_confirmation"
                            error={errors.password_confirmation}
                        >
                            <Input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                autoComplete="new-password"
                                placeholder="Confirm password"
                            />
                        </FormField>

                        <SubmitButton
                            className="mt-4 w-full"
                            processing={processing}
                            data-test="reset-password-button"
                        >
                            Reset password
                        </SubmitButton>
                    </div>
                )}
            </Form>
        </AuthLayout>
    );
}
