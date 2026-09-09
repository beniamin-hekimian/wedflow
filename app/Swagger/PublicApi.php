<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

final class PublicApi
{
    #[OA\Get(
        path: '/',
        tags: ['Public'],
        summary: 'Render the homepage',
        description: 'Renders the marketing homepage listing all invitation templates.'
        . ' Props: `canLogin` (bool), `canRegister` (bool), `laravelVersion` (string), `phpVersion` (string),'
        . ' `templates` (array of Template).',
        responses: [
            new OA\Response(response: 200, description: 'Home page (`Home`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function home(): void
    {
    }

    #[OA\Get(
        path: '/templates',
        tags: ['Public'],
        summary: 'List invitation templates',
        description: 'Renders the template gallery. Props: `templates` (array of Template).',
        responses: [
            new OA\Response(response: 200, description: 'Template gallery (`Templates/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function templatesIndex(): void
    {
    }

    #[OA\Get(
        path: '/melodies',
        tags: ['Public'],
        summary: 'List background melodies',
        description: 'Renders the melody browser. Props: `melodies` (array of Melody).',
        responses: [
            new OA\Response(response: 200, description: 'Melody browser (`Melodies/Index`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
        ],
    )]
    public function melodiesIndex(): void
    {
    }

    #[OA\Get(
        path: '/{slug}',
        tags: ['Public'],
        summary: 'Render a public invitation page',
        description: 'Renders the public invitation page for a slug. Guests need `status` `active`; the '
        . 'owner can also view their pending/inactive invitation, and admins can view any status. A missing'
        . ' slug or a non-active invitation (for non-owners/admins) returns 404, as does a missing frontend'
        . ' template component. Props: `invitation` (Invitation with `template`, `melody`, `events`, `photos`'
        . ' loaded), `wishes` (array of Response with only `id`, `guest_name`, `message`, `created_at` for'
        . ' visible wishes) and `preview` (bool, true when the invitation is not active).',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Public invitation page (`Public/Templates/{Template}`).', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 404, description: 'Unknown slug, non-active invitation without owner/admin access, or missing template component.'),
        ],
    )]
    public function invitationShow(): void
    {
    }

    #[OA\Post(
        path: '/{slug}/rsvp',
        tags: ['Public'],
        summary: 'Submit an RSVP',
        description: 'Records a guest RSVP for an active invitation, flashes a success message and '
        . 're-renders the public invitation page.',
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, description: 'Invitation slug.', schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RsvpRequest')),
        responses: [
            new OA\Response(response: 200, description: 'Re-rendered public invitation page.', content: new OA\JsonContent(ref: '#/components/schemas/PageResponse')),
            new OA\Response(response: 404, description: 'Inactive invitation or missing template component.'),
            new OA\Response(response: 422, description: 'Validation error; the page is re-rendered with `props.errors`.'),
        ],
    )]
    public function rsvpStore(): void
    {
    }
}