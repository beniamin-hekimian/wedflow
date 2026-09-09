<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

final class UserApi
{
    #[OA\Get(
        path: '/profile',
        tags: ['User'],
        summary: 'Show the profile form',
        description: 'Renders the profile edit page. Props: `mustVerifyEmail` (bool) and `status` (optional).',
        responses: [
            new OA\Response(response: 200, description: 'Profile page (`Profile/Edit`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
        ],
    )]
    public function profileEdit(): void
    {
    }

    #[OA\Patch(
        path: '/profile',
        tags: ['User'],
        summary: 'Update the profile',
        description: 'Updates the user\'s name/email. Changing the email resets `email_verified_at` and '
        . 'a verification email is sent. Redirects back to `profile.edit`.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProfileUpdateRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `profile.edit`.'),
            new OA\Response(response: 422, description: 'Validation error (redirected back with `props.errors`).'),
        ],
    )]
    public function profileUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/profile',
        tags: ['User'],
        summary: 'Delete the account',
        description: 'Deletes the authenticated user\'s account and logs out (session invalidated and '
        . 'regenerated). Redirects to `/`.',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DeleteProfileRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `/`.'),
            new OA\Response(response: 422, description: 'Wrong current password.'),
        ],
    )]
    public function profileDestroy(): void
    {
    }

    #[OA\Get(
        path: '/invitations',
        tags: ['User'],
        summary: 'List own invitations',
        description: 'Renders the authenticated user\'s invitation list (newest first). Props: '
        . '`invitations` (array of Invitation with `template` loaded).',
        responses: [
            new OA\Response(response: 200, description: 'Invitation list (`Invitations/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
        ],
    )]
    public function invitationsIndex(): void
    {
    }

    #[OA\Get(
        path: '/invitations/create',
        tags: ['User'],
        summary: 'Show the invitation creation form',
        description: 'Renders the creation wizard for a chosen template. Props: `template` (Template),'
        . ' `melodies` (array of Melody) and `events` (array of Event catalog types).',
        parameters: [
            new OA\Parameter(name: 'template', in: 'query', required: true, description: 'Template id to build the invitation with.', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Creation form (`Invitations/Create`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 404, description: 'Unknown template id.'),
        ],
    )]
    public function invitationsCreate(): void
    {
    }

    #[OA\Post(
        path: '/invitations',
        tags: ['User'],
        summary: 'Create an invitation',
        description: 'Creates an invitation with a catalog-based timeline and photo uploads as `multipart/form-data`. '
        . 'The slug is generated from the couple\'s names. Redirects to `invitations.index` on success.',
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(ref: '#/components/schemas/InvitationCreateRequest'),
        )),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `invitations.index` with a success flash.'),
            new OA\Response(response: 403, description: 'Not permitted (unlikely for this endpoint).'),
            new OA\Response(response: 422, description: 'Validation error (redirected back with `props.errors`).'),
        ],
    )]
    public function invitationsStore(): void
    {
    }

    #[OA\Get(
        path: '/invitations/{slug}/edit',
        tags: ['User'],
        summary: 'Show the invitation edit form',
        description: 'Renders the edit page for an owned invitation. Props: `invitation` (Invitation with'
        . ' `template`, `melody`, `events` (timeline projections), `photos` loaded), `template` (Template),'
        . ' `melodies` (array of Melody) and `events` (array of Event catalog types).',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Edit form (`Invitations/Edit`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 403, description: 'Invitation does not belong to the user.'),
            new OA\Response(response: 404, description: 'Unknown slug.'),
        ],
    )]
    public function invitationsEdit(): void
    {
    }

    #[OA\Put(
        path: '/invitations/{slug}',
        tags: ['User'],
        summary: 'Update an invitation',
        description: 'Updates an owned invitation as `multipart/form-data`: replays the timeline, deletes'
        . ' removed photos, keeps `existing_photo_ids` (max 5 photos in total) and adds/uploads new ones.'
        . ' Redirects to `invitations.index` on success.',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(ref: '#/components/schemas/InvitationUpdateRequest'),
        )),
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `invitations.index` with a success flash.'),
            new OA\Response(response: 403, description: 'Invitation does not belong to the user.'),
            new OA\Response(response: 404, description: 'Unknown slug.'),
            new OA\Response(response: 422, description: 'Validation error (redirected back with `props.errors`).'),
        ],
    )]
    public function invitationsUpdate(): void
    {
    }

    #[OA\Delete(
        path: '/invitations/{slug}',
        tags: ['User'],
        summary: 'Delete an invitation',
        description: 'Deletes an owned invitation, its related records and the uploaded photo directory. '
        . 'Redirects to `invitations.index` on success.',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect to `invitations.index` with a success flash.'),
            new OA\Response(response: 403, description: 'Invitation does not belong to the user.'),
            new OA\Response(response: 404, description: 'Unknown slug.'),
        ],
    )]
    public function invitationsDestroy(): void
    {
    }

    #[OA\Get(
        path: '/invitations/{slug}/responses',
        tags: ['User'],
        summary: 'List responses of an invitation',
        description: 'Renders the guestbook/RSVP admin view for an owned invitation. Props: `invitation`'
        . ' (with `template`, `melody`), `rows` (Paginator of Response), `summary` (object: `total`,'
        . ' `attending`, `declined`, `confirmed_guests`, `wishes`) and `filters` (`{ sort, direction }`).',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: 'Sort field: `guest_name`, `is_attending`, `count`, `message`, `is_hidden` or `created_at`. Defaults to `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`. Defaults to `asc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Responses page (`Invitations/Responses`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 403, description: 'Invitation does not belong to the user.'),
            new OA\Response(response: 404, description: 'Unknown slug.'),
        ],
    )]
    public function invitationsResponses(): void
    {
    }

    #[OA\Patch(
        path: '/invitations/{slug}/responses/{response}/visibility',
        tags: ['User'],
        summary: 'Toggle a response\'s visibility',
        description: 'Toggles `is_hidden` on a wish belonging to an owned invitation. Redirects back with'
        . ' a success flash.',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'response', in: 'path', required: true, description: 'Response id.', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirect back with a success flash.'),
            new OA\Response(response: 403, description: 'Invitation does not belong to the user.'),
            new OA\Response(response: 404, description: 'Unknown slug or response not part of the invitation.'),
        ],
    )]
    public function invitationsResponsesVisibility(): void
    {
    }
}