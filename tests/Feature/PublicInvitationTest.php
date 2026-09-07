<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Melody;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicInvitationTest extends TestCase
{
    use RefreshDatabase;

    private Template $template;

    private Melody $melody;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'name' => 'Classic',
            'slug' => 'classic',
            'description' => 'An elegant classic wedding invitation template',
            'thumbnail_path' => 'templates-assets/classic/thumbnail.jpeg',
            'intro_video_path' => 'templates-assets/classic/intro.mp4',
        ]);

        $this->melody = Melody::create([
            'name' => 'Classic',
            'file_path' => 'melodies-assets/classic.mp3',
        ]);
    }

    private function invitation(User $user, array $overrides = []): Invitation
    {
        $invitation = Invitation::create(array_merge([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'slug' => 'romantic-slug-' . Str::uuid(),
            'groom_name' => 'John',
            'bride_name' => 'Jane',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => "1 Main Street\nSpringfield",
            'contact_phone' => '555-0100',
            'status' => 'pending',
        ], $overrides));

        $invitation->events()->createMany([
            ['name' => 'Ceremony', 'time' => '16:00', 'position' => 0],
            ['name' => 'Reception', 'time' => '19:00', 'position' => 1],
        ]);

        return $invitation;
    }

    public function test_active_invitation_is_publicly_visible(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'active']);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Classic')
                ->where('invitation.slug', $invitation->slug)
                ->where('invitation.status', 'active')
                ->where('invitation.groom_name', 'John')
                ->where('invitation.events.0.name', 'Ceremony')
                ->where('invitation.events.1.name', 'Reception'));
    }

    public function test_pending_invitation_is_not_publicly_visible(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'pending']);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertNotFound();
    }

    public function test_inactive_invitation_is_not_publicly_visible(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'inactive']);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get(route('invitations.show', 'does-not-exist'))
            ->assertNotFound();
    }
}