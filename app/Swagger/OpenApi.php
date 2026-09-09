<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Wedflow API',
    description: 'HTTP surface of the Wedflow wedding invitation platform (Laravel + Inertia + React).'
    . ' Every response is an **Inertia page payload** - a full HTML document on initial load, or a JSON'
    . ' envelope `{ component, props, url, version }` for XHR (axios) requests. Session-cookie authentication'
    . ' (Laravel Breeze) is used throughout; state-changing requests must carry the CSRF token, which the'
    . ' frontend sends as the `X-XSRF-TOKEN` header (matching the `XSRF-TOKEN` cookie).'
    . ' Operations are tagged **Public**, **Auth**, **User** (authenticated customers) and **Admin**'
    . ' (requires the `admin` role). The `props` shape of each page is listed in the operation description.',
    contact: new OA\Contact(name: 'Wedflow'),
)]
#[OA\Server(url: L5_SWAGGER_CONST_HOST)]
#[OA\Tag(name: 'Public', description: 'Publicly accessible endpoints (no authentication).')]
#[OA\Tag(name: 'Auth', description: 'Registration, login, logout, password reset and email verification flows.')]
#[OA\Tag(name: 'User', description: 'Authenticated customer endpoints: profile and invitations.')]
#[OA\Tag(name: 'Admin', description: 'Admin-only endpoints (requires the `admin` role).')]
#[OA\SecurityScheme(
    securityScheme: 'cookieAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-XSRF-TOKEN',
    description: 'Session-cookie authentication. Obtain the session and CSRF cookies via `POST /login`.'
    . ' State-changing requests submit the CSRF token as the `X-XSRF-TOKEN` header. Documented for'
    . ' reference only - endpoints are not intended to be executed from this UI.',
)]
final class OpenApi
{
}

#[OA\Schema(schema: 'PageResponse', description: 'Inertia page envelope returned by every endpoint.')]
final class PageResponse
{
    #[OA\Property(description: 'Inertia page component name.', example: 'Invitations/Index')]
    public string $component;

    #[OA\Property(description: 'Page-specific props (see each operation description).', type: 'object')]
    public array $props;

    #[OA\Property(description: 'Current request URL.', example: '/invitations')]
    public string $url;

    #[OA\Property(description: 'Frontend asset version fingerprint.', nullable: true)]
    public ?string $version;
}

#[OA\Schema(schema: 'Paginator', description: 'Laravel length-aware paginator envelope.')]
final class Paginator
{
    #[OA\Property(type: 'array', items: new OA\Items(description: 'Page rows'))]
    public array $data;

    #[OA\Property(description: '1-based current page number.', example: 1)]
    public int $current_page;

    #[OA\Property(description: 'Items per page.', example: 10)]
    public int $per_page;

    #[OA\Property(description: 'Total number of records.', example: 42)]
    public int $total;

    #[OA\Property(description: 'Last page number.', example: 5)]
    public int $last_page;

    #[OA\Property(description: 'Index of the first item on this page (1-based), or `null` when empty.', nullable: true)]
    public ?int $from;

    #[OA\Property(description: 'Index of the last item on this page, or `null` when empty.', nullable: true)]
    public ?int $to;

    #[OA\Property(description: 'URL of the next page, or `null` on the last page.', nullable: true)]
    public ?string $next_page_url;

    #[OA\Property(description: 'URL of the previous page, or `null` on the first page.', nullable: true)]
    public ?string $prev_page_url;

    #[OA\Property(description: 'Base pagination path.', example: 'http://localhost/invitations')]
    public string $path;
}

#[OA\Schema(schema: 'AuthUser', description: 'The authenticated user, shared as `auth.user` on every page.')]
final class AuthUser
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property]
    public string $name;

    #[OA\Property(example: 'jane@example.com')]
    public string $email;

    #[OA\Property(description: 'ISO-8601 timestamp, `null` when the email is unverified.', format: 'date-time', nullable: true)]
    public ?string $email_verified_at;

    #[OA\Property(enum: ['customer', 'admin'], example: 'customer')]
    public string $role;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}

#[OA\Schema(schema: 'UserRow', description: 'User row shown in the admin users report.')]
final class UserRow
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property]
    public string $name;

    #[OA\Property(example: 'jane@example.com')]
    public string $email;

    #[OA\Property(description: 'ISO-8601 timestamp, `null` when the email is unverified.', format: 'date-time', nullable: true)]
    public ?string $email_verified_at;

    #[OA\Property(enum: ['customer', 'admin'])]
    public string $role;

    #[OA\Property(description: 'Number of invitations owned by the user (only when sorted by it).', example: 3, nullable: true)]
    public ?int $invitations_count;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}

#[OA\Schema(schema: 'Template', description: 'Invitation template.')]
final class Template
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'Majestic')]
    public string $name;

    #[OA\Property(example: 'majestic')]
    public string $slug;

    #[OA\Property(description: 'Marketing description of the template.', nullable: true)]
    public ?string $description;

    #[OA\Property(description: 'Path to the thumbnail asset.', example: 'templates-assets/majestic/thumbnail.jpeg')]
    public string $thumbnail_path;

    #[OA\Property(description: 'Path to the intro video asset.', example: 'templates-assets/majestic/intro.mp4', nullable: true)]
    public ?string $intro_video_path;

    #[OA\Property(description: 'Number of invitations using the template (admin reports only).', example: 4, nullable: true)]
    public ?int $invitations_count;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}

#[OA\Schema(schema: 'Melody', description: 'Background melody available to invitations.')]
final class Melody
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'Classic')]
    public string $name;

    #[OA\Property(example: 'melodies-assets/classic.mp3')]
    public string $file_path;

    #[OA\Property(description: 'Number of invitations using the melody (admin reports only).', example: 3, nullable: true)]
    public ?int $invitations_count;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}

#[OA\Schema(schema: 'InvitationEvent', description: 'An event of an invitation timeline, picked from the event catalog.')]
final class InvitationEvent
{
    #[OA\Property(example: 8)]
    public int $id;

    #[OA\Property(example: 'Exchange of Vows')]
    public string $name;

    #[OA\Property(description: 'Time in 24h format, chosen by the couple.', example: '10:00')]
    public string $time;
}

#[OA\Schema(schema: 'Event', description: 'A catalog event type users can add to an invitation timeline.')]
final class Event
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'Exchange of Vows')]
    public string $name;

    #[OA\Property(description: 'Catalog display position.', example: 2)]
    public int $sort_order;
}

#[OA\Schema(schema: 'Photo', description: 'A photo uploaded to an invitation.')]
final class Photo
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 8)]
    public int $invitation_id;

    #[OA\Property(example: 'invitations/8/0.jpg')]
    public string $photo_path;

    #[OA\Property(example: 0)]
    public int $position;
}

#[OA\Schema(schema: 'Response', description: 'An RSVP/wish received for an invitation.')]
final class Response
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 8)]
    public int $invitation_id;

    #[OA\Property(example: 'Jordan Smith')]
    public string $guest_name;

    #[OA\Property(description: 'Whether the guest is attending.')]
    public bool $is_attending;

    #[OA\Property(description: 'Confirmed guest count (attending responses only; 0 when declining).', example: 2)]
    public int $count;

    #[OA\Property(description: 'Optional wish/message shown to the couple.', nullable: true)]
    public ?string $message;

    #[OA\Property(description: 'Whether the wish is hidden from public guests.')]
    public bool $is_hidden;

    #[OA\Property(format: 'date-time')]
    public string $created_at;
}

#[OA\Schema(schema: 'Invitation', description: 'A wedding invitation. Relations (`template`, `melody`, `events`, `photos`, `user`) are loaded on specific pages.')]
final class Invitation
{
    #[OA\Property(example: 8)]
    public int $id;

    #[OA\Property(description: 'Owning user id.', example: 2)]
    public int $user_id;

    #[OA\Property(example: 1)]
    public int $template_id;

    #[OA\Property(example: 1)]
    public int $melody_id;

    #[OA\Property(example: 'john-jane')]
    public string $slug;

    #[OA\Property(example: 'John')]
    public string $groom_name;

    #[OA\Property(example: 'Jane')]
    public string $bride_name;

    #[OA\Property(description: 'Wedding date.', example: '2026-11-14')]
    public string $event_date;

    #[OA\Property(example: '18:00')]
    public string $event_time;

    #[OA\Property(example: 'Grand Hall')]
    public string $venue_name;

    #[OA\Property(example: '1 Main Street')]
    public string $venue_address;

    #[OA\Property(nullable: true)]
    public ?string $contact_phone;

    #[OA\Property(description: 'Free-form note displayed to guests.', nullable: true)]
    public ?string $note;

    #[OA\Property(enum: ['pending', 'active', 'inactive'], example: 'active')]
    public string $status;

    #[OA\Property(description: 'Payment timestamp, `null` until paid.', format: 'date-time', nullable: true)]
    public ?string $paid_at;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}

#[OA\Schema(schema: 'RegisterRequest')]
final class RegisterRequest
{
    #[OA\Property(description: 'Full name.', type: 'string', maxLength: 255)]
    public ?string $name;

    #[OA\Property(description: 'Unique lowercase email.', type: 'string', format: 'email', maxLength: 255)]
    public ?string $email;

    #[OA\Property(description: 'Password (default Laravel password rules).', type: 'string')]
    public ?string $password;

    #[OA\Property(description: 'Must match `password`.', type: 'string')]
    public ?string $password_confirmation;
}

#[OA\Schema(schema: 'LoginRequest')]
final class LoginRequest
{
    #[OA\Property(description: 'Account email.', type: 'string', format: 'email')]
    public ?string $email;

    #[OA\Property(description: 'Account password.', type: 'string')]
    public ?string $password;

    #[OA\Property(description: 'Persist the session with a remember cookie.', type: 'boolean')]
    public ?bool $remember;
}

#[OA\Schema(schema: 'ForgotPasswordRequest')]
final class ForgotPasswordRequest
{
    #[OA\Property(description: 'Email to send the password reset link to.', type: 'string', format: 'email')]
    public ?string $email;
}

#[OA\Schema(schema: 'ResetPasswordRequest')]
final class ResetPasswordRequest
{
    #[OA\Property(description: 'Token from the reset link.', type: 'string')]
    public ?string $token;

    #[OA\Property(type: 'string', format: 'email')]
    public ?string $email;

    #[OA\Property(description: 'New password (default Laravel password rules).', type: 'string')]
    public ?string $password;

    #[OA\Property(description: 'Must match `password`.', type: 'string')]
    public ?string $password_confirmation;
}

#[OA\Schema(schema: 'UpdatePasswordRequest')]
final class UpdatePasswordRequest
{
    #[OA\Property(description: 'Current password.', type: 'string')]
    public ?string $current_password;

    #[OA\Property(description: 'New password (default Laravel password rules).', type: 'string')]
    public ?string $password;

    #[OA\Property(description: 'Must match `password`.', type: 'string')]
    public ?string $password_confirmation;
}

#[OA\Schema(schema: 'ConfirmPasswordRequest')]
final class ConfirmPasswordRequest
{
    #[OA\Property(type: 'string')]
    public ?string $password;
}

#[OA\Schema(schema: 'ProfileUpdateRequest')]
final class ProfileUpdateRequest
{
    #[OA\Property(description: 'Full name.', type: 'string', maxLength: 255)]
    public ?string $name;

    #[OA\Property(description: 'Unique lowercase email (ignores current user).', type: 'string', format: 'email', maxLength: 255)]
    public ?string $email;
}

#[OA\Schema(schema: 'DeleteProfileRequest')]
final class DeleteProfileRequest
{
    #[OA\Property(description: 'Current password confirmation.', type: 'string')]
    public ?string $password;
}

#[OA\Schema(schema: 'InvitationEventInput', description: 'One timeline entry of an invitation form.')]
final class InvitationEventInput
{
    #[OA\Property(description: 'Catalog event type id.', type: 'integer')]
    public ?int $event_id;

    #[OA\Property(description: 'Event time.', type: 'string', example: '18:00')]
    public ?string $time;
}

#[OA\Schema(schema: 'InvitationCreateRequest', description: '`multipart/form-data` body of `POST /invitations`.')]
final class InvitationCreateRequest
{
    #[OA\Property(description: 'Chosen template id.', type: 'integer')]
    public ?int $template_id;

    #[OA\Property(description: 'Chosen melody id.', type: 'integer')]
    public ?int $melody_id;

    #[OA\Property(description: 'Groom name.', type: 'string', maxLength: 255)]
    public ?string $groom_name;

    #[OA\Property(description: 'Bride name.', type: 'string', maxLength: 255)]
    public ?string $bride_name;

    #[OA\Property(description: 'Wedding date.', type: 'string', format: 'date')]
    public ?string $event_date;

    #[OA\Property(description: 'Wedding start time.', type: 'string', example: '18:00')]
    public ?string $event_time;

    #[OA\Property(description: 'Venue name.', type: 'string', maxLength: 255)]
    public ?string $venue_name;

    #[OA\Property(description: 'Venue address.', type: 'string')]
    public ?string $venue_address;

    #[OA\Property(description: 'Contact phone shown to guests.', type: 'string', maxLength: 255, nullable: true)]
    public ?string $contact_phone;

    #[OA\Property(description: 'Free-form note shown to guests.', type: 'string', nullable: true)]
    public ?string $note;

    #[OA\Property(
        description: 'Ordered timeline of catalog event ids, each with a chosen time (2 to 4 items).',
        type: 'array',
        minItems: 2,
        maxItems: 4,
        items: new OA\Items(ref: '#/components/schemas/InvitationEventInput'),
    )]
    public ?array $events;

    #[OA\Property(
        description: 'Up to 5 new photo uploads (jpeg/png/webp, max 5MB each).',
        type: 'array',
        maxItems: 5,
        items: new OA\Items(type: 'string', format: 'binary'),
    )]
    public ?array $photos;
}

#[OA\Schema(schema: 'InvitationUpdateRequest', description: '`multipart/form-data` body of `PUT /invitations/{slug}`.')]
final class InvitationUpdateRequest
{
    #[OA\Property(description: 'Chosen template id.', type: 'integer')]
    public ?int $template_id;

    #[OA\Property(description: 'Chosen melody id.', type: 'integer')]
    public ?int $melody_id;

    #[OA\Property(description: 'Groom name.', type: 'string', maxLength: 255)]
    public ?string $groom_name;

    #[OA\Property(description: 'Bride name.', type: 'string', maxLength: 255)]
    public ?string $bride_name;

    #[OA\Property(description: 'Wedding date.', type: 'string', format: 'date')]
    public ?string $event_date;

    #[OA\Property(description: 'Wedding start time.', type: 'string', example: '18:00')]
    public ?string $event_time;

    #[OA\Property(description: 'Venue name.', type: 'string', maxLength: 255)]
    public ?string $venue_name;

    #[OA\Property(description: 'Venue address.', type: 'string')]
    public ?string $venue_address;

    #[OA\Property(description: 'Contact phone shown to guests.', type: 'string', maxLength: 255, nullable: true)]
    public ?string $contact_phone;

    #[OA\Property(description: 'Free-form note shown to guests.', type: 'string', nullable: true)]
    public ?string $note;

    #[OA\Property(
        description: 'Ordered timeline of catalog event ids, each with a chosen time (2 to 4 items).',
        type: 'array',
        minItems: 2,
        maxItems: 4,
        items: new OA\Items(ref: '#/components/schemas/InvitationEventInput'),
    )]
    public ?array $events;

    #[OA\Property(
        description: 'Ids of photos already on the invitation that should be kept (order = display order).',
        type: 'array',
        items: new OA\Items(type: 'integer'),
    )]
    public ?array $existing_photo_ids;

    #[OA\Property(
        description: 'New photo uploads (max 5 photos in total, jpeg/png/webp, max 5MB each).',
        type: 'array',
        maxItems: 5,
        items: new OA\Items(type: 'string', format: 'binary'),
    )]
    public ?array $photos;
}

#[OA\Schema(schema: 'RsvpRequest', description: 'Body of `POST /{slug}/rsvp`.')]
final class RsvpRequest
{
    #[OA\Property(description: 'Name of the guest.', type: 'string', maxLength: 255)]
    public ?string $guest_name;

    #[OA\Property(description: 'Whether the guest is attending.', type: 'boolean')]
    public ?bool $is_attending;

    #[OA\Property(description: 'Confirmed guest count (1-10). Required only when attending.', type: 'integer', minimum: 1, maximum: 10, nullable: true)]
    public ?int $count;

    #[OA\Property(description: 'Optional wish/message.', type: 'string', maxLength: 1000, nullable: true)]
    public ?string $message;
}

#[OA\Schema(schema: 'UpdateInvitationStatusRequest')]
final class UpdateInvitationStatusRequest
{
    #[OA\Property(enum: ['pending', 'active', 'inactive'])]
    public ?string $status;
}

#[OA\Schema(schema: 'UpdateRoleRequest')]
final class UpdateRoleRequest
{
    #[OA\Property(enum: ['admin', 'customer'])]
    public ?string $role;
}