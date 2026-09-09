<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

final class AuthApi
{
    #[OA\Get(
        path: '/register',
        tags: ['Auth'],
        summary: 'Show the registration form',
        description: 'Renders the registration page for guests.',
        responses: [
            new OA\Response(response: 200, description: 'Registration form (`Auth/Register`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to home when already authenticated.'),
        ],
    )]
    public function registerCreate(): void
    {
    }

    #[OA\Post(
        path: '/register',
        tags: ['Auth'],
        summary: 'Register a new account',
        description: 'Creates the account, fires the `Registered` event, logs the user in and redirects home.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to home.'),
            new OA\Response(response: 422, description: 'Validation error (re-shown on the register page).'),
        ],
    )]
    public function registerStore(): void
    {
    }

    #[OA\Get(
        path: '/login',
        tags: ['Auth'],
        summary: 'Show the login form',
        description: 'Renders the login page. Props: `status` (optional password-reset status message).',
        responses: [
            new OA\Response(response: 200, description: 'Login form (`Auth/Login`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to home when already authenticated.'),
        ],
    )]
    public function loginCreate(): void
    {
    }

    #[OA\Post(
        path: '/login',
        tags: ['Auth'],
        summary: 'Authenticate a user',
        description: 'Attempts login (rate limited to 5 attempts), regenerates the session and redirects '
        . 'to the intended URL. Sets the session and `XSRF-TOKEN` cookies.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `intended` URL or home.'),
            new OA\Response(response: 422, description: 'Invalid credentials or throttled (`auth.failed` / `auth.throttle`).'),
        ],
    )]
    public function loginStore(): void
    {
    }

    #[OA\Post(
        path: '/logout',
        tags: ['Auth'],
        summary: 'Log the user out',
        description: 'Logs out the current session, invalidates it and redirects to `/`.',
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `/`.'),
        ],
    )]
    public function logout(): void
    {
    }

    #[OA\Get(
        path: '/forgot-password',
        tags: ['Auth'],
        summary: 'Show the password reset request form',
        description: 'Renders the forgot-password page. Props: `status` (optional).',
        responses: [
            new OA\Response(response: 200, description: 'Forgot-password form (`Auth/ForgotPassword`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function forgotPasswordCreate(): void
    {
    }

    #[OA\Post(
        path: '/forgot-password',
        tags: ['Auth'],
        summary: 'Send a password reset link',
        description: 'Emails a password reset link. Redirects back with a `status` flash when the link '
        . 'was sent; a validation error on `email` otherwise (also used for non-existent emails).',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect back (with `status` flash on success).'),
            new OA\Response(response: 422, description: 'Invalid email or reset-link failure.'),
        ],
    )]
    public function forgotPasswordStore(): void
    {
    }

    #[OA\Get(
        path: '/reset-password/{token}',
        tags: ['Auth'],
        summary: 'Show the password reset form',
        description: 'Renders the reset-password page. Props: `email` (from query string) and `token`.',
        parameters: [
            new OA\Parameter(name: 'token', in: 'path', required: true, description: 'Password reset token.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'email', in: 'query', required: false, description: 'Email the reset applies to.', schema: new OA\Schema(type: 'string', format: 'email')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reset-password form (`Auth/ResetPassword`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function resetPasswordCreate(): void
    {
    }

    #[OA\Post(
        path: '/reset-password',
        tags: ['Auth'],
        summary: 'Reset the password',
        description: 'Resets the password using the token, then redirects to login with a `status` flash. '
        . 'On failure a validation error is raised on `email`.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to login (success) or back (failure).'),
            new OA\Response(response: 422, description: 'Invalid token/email/password.'),
        ],
    )]
    public function resetPasswordStore(): void
    {
    }

    #[OA\Get(
        path: '/verify-email',
        tags: ['Auth'],
        summary: 'Show the email verification notice',
        description: 'Renders the verification-notice page for authenticated users with unverified emails.',
        responses: [
            new OA\Response(response: 200, description: 'Verification notice (`Auth/VerifyEmail`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected home when already verified.'),
        ],
    )]
    public function verificationNotice(): void
    {
    }

    #[OA\Get(
        path: '/verify-email/{id}/{hash}',
        tags: ['Auth'],
        summary: 'Verify an email address',
        description: 'Marks the user\'s email as verified after checking the signed URL (validated by the '
        . '`signed` middleware, rate limited at 6 requests per minute). Redirects to the verification '
        . 'notice with a `status` flash.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'User id.', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'hash', in: 'path', required: true, description: 'Signed verification hash.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `verification.notice` (with `status`).'),
            new OA\Response(response: 403, description: 'Invalid signature or mismatched id/hash.'),
        ],
    )]
    public function verifyEmail(): void
    {
    }

    #[OA\Post(
        path: '/email/verification-notification',
        tags: ['Auth'],
        summary: 'Resend the verification email',
        description: 'Re-sends the verification email (rate limited at 6 requests per minute). Redirects '
        . 'back with a `status` flash.',
        responses: [
            new OA\Response(response: 302, description: 'Redirect back with `status` flash.'),
        ],
    )]
    public function verificationSend(): void
    {
    }

    #[OA\Get(
        path: '/confirm-password',
        tags: ['Auth'],
        summary: 'Show the password confirmation form',
        description: 'Renders the password confirmation page for sensitive actions.',
        responses: [
            new OA\Response(response: 200, description: 'Password confirmation (`Auth/ConfirmPassword`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function confirmPasswordShow(): void
    {
    }

    #[OA\Post(
        path: '/confirm-password',
        tags: ['Auth'],
        summary: 'Confirm the current password',
        description: 'Confirms the user\'s password, storing a time-boxed confirmation timestamp, and '
        . 'redirects to the intended URL. Redirects back with errors on failure.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ConfirmPasswordRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to the intended URL or back.'),
            new OA\Response(response: 422, description: 'Wrong password (redirected back with `props.errors`).'),
        ],
    )]
    public function confirmPasswordStore(): void
    {
    }

    #[OA\Put(
        path: '/password',
        tags: ['Auth'],
        summary: 'Update the password',
        description: 'Updates the authenticated user\'s password (requires the current password).',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdatePasswordRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect back on success.'),
            new OA\Response(response: 422, description: 'Validation error (redirected back with `props.errors`).'),
        ],
    )]
    public function passwordUpdate(): void
    {
    }
}