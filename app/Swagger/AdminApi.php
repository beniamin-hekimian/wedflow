<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

final class AdminApi
{
    #[OA\Get(
        path: '/admin',
        tags: ['Admin'],
        summary: 'Show the admin dashboard',
        description: 'Renders the admin dashboard. Props: `stats` (object: `total`, `active`, `pending`'
        . ' invitation counts, `users`, `admins`) and `topInvitations` (array of up to 3 invitations with'
        . ' `id`, `slug`, `groom_name`, `bride_name`, `event_date`, `status`, `responses_count`).',
        responses: [
            new OA\Response(response: 200, description: 'Dashboard (`Admin/Dashboard`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function dashboard(): void
    {
    }

    #[OA\Get(
        path: '/admin/invitations',
        tags: ['Admin'],
        summary: 'List all invitations',
        description: 'Renders the paginated admin invitations report. Props: `invitations` (Paginator of'
        . ' Invitation with `user` and `template` loaded), `filters` (`{ status, search, sort, direction }`)'
        . ' and `statusCounts` (`{ total, pending, active, inactive }`).',
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter by status.', schema: new OA\Schema(type: 'string', enum: ['pending', 'active', 'inactive'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches groom, bride or venue name, or owner name/email.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`groom_name`, `owner_name`, `template_name`, `event_date`, `status` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Invitations report (`Admin/Invitations/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminInvitationsIndex(): void
    {
    }

    #[OA\Patch(
        path: '/admin/invitations/{invitation}/status',
        tags: ['Admin'],
        summary: 'Update an invitation\'s status',
        description: 'Changes the status of an invitation. Redirects back with a success flash.',
        parameters: [
            new OA\Parameter(name: 'invitation', in: 'path', required: true, description: 'Invitation id.', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateInvitationStatusRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect back with a success flash.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
            new OA\Response(response: 404, description: 'Unknown invitation id.'),
            new OA\Response(response: 422, description: 'Invalid status.'),
        ],
    )]
    public function adminInvitationsStatus(): void
    {
    }

    #[OA\Get(
        path: '/admin/users',
        tags: ['Admin'],
        summary: 'List all users',
        description: 'Renders the paginated admin users report. Props: `users` (Paginator of UserRow),'
        . ' `filters` (`{ role, search, sort, direction }`) and `roleCounts` (`{ total, admin, customer }`).',
        parameters: [
            new OA\Parameter(name: 'role', in: 'query', required: false, description: 'Filter by role.', schema: new OA\Schema(type: 'string', enum: ['admin', 'customer'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches name or email.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`name`, `email`, `role`, `invitations_count` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Users report (`Admin/Users/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminUsersIndex(): void
    {
    }

    #[OA\Patch(
        path: '/admin/users/{user}/role',
        tags: ['Admin'],
        summary: 'Update a user\'s role',
        description: 'Promotes/demotes a user between `admin` and `customer`. Admins cannot change their own'
        . ' role (an error flash is set instead). Redirects back.',
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, description: 'User id.', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateRoleRequest')),
        responses: [
            new OA\Response(response: 302, description: 'Redirect back (success flash, or error when changing own role).'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
            new OA\Response(response: 404, description: 'Unknown user id.'),
            new OA\Response(response: 422, description: 'Invalid role.'),
        ],
    )]
    public function adminUsersRole(): void
    {
    }

    #[OA\Get(
        path: '/admin/templates',
        tags: ['Admin'],
        summary: 'List all templates',
        description: 'Renders the paginated admin template report. Props: `templates` (Paginator of'
        . ' Template with `invitations_count`), `filters` (`{ usage, search, sort, direction }`) and'
        . ' `usageCounts` (`{ total, inUse, unused }`).',
        parameters: [
            new OA\Parameter(name: 'usage', in: 'query', required: false, description: 'Filter by usage.', schema: new OA\Schema(type: 'string', enum: ['in-use', 'unused'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches name, slug or description.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`name`, `slug`, `invitations_count` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Templates report (`Admin/Templates/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminTemplatesIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/melodies',
        tags: ['Admin'],
        summary: 'List all melodies',
        description: 'Renders the paginated admin melody report. Props: `melodies` (Paginator of Melody'
        . ' with `invitations_count`), `filters` (`{ usage, search, sort, direction }`) and `usageCounts`'
        . ' (`{ total, inUse, unused }`).',
        parameters: [
            new OA\Parameter(name: 'usage', in: 'query', required: false, description: 'Filter by usage.', schema: new OA\Schema(type: 'string', enum: ['in-use', 'unused'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches name or file path.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`name`, `file_path`, `invitations_count` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Melodies report (`Admin/Melodies/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminMelodiesIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/events',
        tags: ['Admin'],
        summary: 'List all timeline events',
        description: 'Renders the paginated admin event report. Props: `events` (Paginator of Event'
        . ' with `invitations_count`), `filters` (`{ usage, search, sort, direction }`) and `usageCounts`'
        . ' (`{ total, inUse, unused }`). Defaults to ascending `sort_order`.',
        parameters: [
            new OA\Parameter(name: 'usage', in: 'query', required: false, description: 'Filter by usage.', schema: new OA\Schema(type: 'string', enum: ['in-use', 'unused'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches event name.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`name`, `sort_order`, `invitations_count` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Events report (`Admin/Events/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminEventsIndex(): void
    {
    }

    #[OA\Get(
        path: '/admin/responses',
        tags: ['Admin'],
        summary: 'List all responses',
        description: 'Renders the paginated admin responses report grouped by invitation. Props:'
        . ' `invitations` (Paginator of Invitation with `user`, filtered `responses`, and counts'
        . ' `responses_count`, `attending_count`, `declined_count`, `wishes_count`, `guests_count`),'
        . ' `filters` (`{ attending, search, sort, direction }`) and `attendanceCounts`'
        . ' (`{ total, attending, declined }`).',
        parameters: [
            new OA\Parameter(name: 'attending', in: 'query', required: false, description: 'Filter responses by attendance.', schema: new OA\Schema(type: 'string', enum: ['attending', 'declined'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Matches guest name or message.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', required: false, description: '`couple_name`, `event_date`, `responses_count` or `created_at`.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'direction', in: 'query', required: false, description: '`asc` or `desc`.', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Responses report (`Admin/Responses/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 302, description: 'Redirected to login when not authenticated.'),
            new OA\Response(response: 403, description: 'Requires the `admin` role.'),
        ],
    )]
    public function adminResponsesIndex(): void
    {
    }
}