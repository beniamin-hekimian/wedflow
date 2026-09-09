<?php

namespace Tests\Feature;

use App\Models\Event;
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

    private Event $eventCeremony;

    private Event $eventReception;

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

        $this->eventCeremony = Event::create(['name' => 'Ceremony', 'sort_order' => 0]);
        $this->eventReception = Event::create(['name' => 'Reception', 'sort_order' => 1]);
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

        $invitation->events()->sync([
            $this->eventCeremony->id => ['time' => '16:00', 'position' => 0],
            $this->eventReception->id => ['time' => '19:00', 'position' => 1],
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

    public function test_owner_can_preview_their_pending_invitation(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'pending']);

        $this->actingAs($user)
            ->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Classic')
                ->where('invitation.slug', $invitation->slug)
                ->where('invitation.status', 'pending')
                ->where('preview', true));
    }

    public function test_owner_can_preview_their_inactive_invitation(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'inactive']);

        $this->actingAs($user)
            ->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Classic')
                ->where('invitation.slug', $invitation->slug)
                ->where('invitation.status', 'inactive')
                ->where('preview', true));
    }

    public function test_non_owner_cannot_view_pending_invitation(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $invitation = $this->invitation($owner, ['status' => 'pending']);

        $this->actingAs($other)
            ->get(route('invitations.show', $invitation->slug))
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

    public function test_active_invitation_renders_the_royal_template(): void
    {
        $royal = Template::create([
            'name' => 'Royal',
            'slug' => 'royal',
            'description' => 'A luxurious royal wedding invitation template',
            'thumbnail_path' => 'templates-assets/royal/thumbnail.jpg',
            'intro_video_path' => 'templates-assets/royal/intro.mp4',
        ]);

        $user = User::factory()->create();
        $invitation = $this->invitation($user, [
            'template_id' => $royal->id,
            'status' => 'active',
        ]);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Royal')
                ->where('invitation.template.slug', 'royal'));
    }

    public function test_active_invitation_renders_the_majestic_template(): void
    {
        $majestic = Template::create([
            'name' => 'Majestic',
            'slug' => 'majestic',
            'description' => 'A soft romantic wedding invitation template',
            'thumbnail_path' => 'templates-assets/majestic/thumbnail.jpg',
            'intro_video_path' => 'templates-assets/majestic/intro.mp4',
        ]);

        $user = User::factory()->create();
        $invitation = $this->invitation($user, [
            'template_id' => $majestic->id,
            'status' => 'active',
        ]);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Majestic')
                ->where('invitation.template.slug', 'majestic'));
    }
}