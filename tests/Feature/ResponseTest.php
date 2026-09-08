<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Melody;
use App\Models\Response;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResponseTest extends TestCase
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

    private function invitation(?User $user = null, array $overrides = []): Invitation
    {
        $user ??= User::factory()->create();

        return Invitation::create(array_merge([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'slug' => 'rsvp-slug-' . Str::uuid(),
            'groom_name' => 'John',
            'bride_name' => 'Jane',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => "1 Main Street\nSpringfield",
            'status' => 'active',
        ], $overrides));
    }

    private function postRsvp(Invitation $invitation, array $data = [])
    {
        return $this->post(route('rsvp.store', $invitation->slug), array_merge([
            'guest_name' => 'Amelia Carter',
            'is_attending' => true,
            'count' => 2,
            'message' => '',
        ], $data));
    }

    public function test_attending_guest_creates_response_with_count(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation)->assertOk();

        $response = Response::first();
        $this->assertNotNull($response);
        $this->assertEquals($invitation->id, $response->invitation_id);
        $this->assertEquals('Amelia Carter', $response->guest_name);
        $this->assertEquals(true, $response->is_attending);
        $this->assertEquals(2, $response->count);
        $this->assertNull($response->message);
        $this->assertDatabaseCount('responses', 1);
    }

    public function test_guest_with_message_persists_it_not_hidden_by_default(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, ['message' => 'Congratulations, you two!'])->assertOk();

        $response = Response::first();
        $this->assertNotNull($response);
        $this->assertEquals($invitation->id, $response->invitation_id);
        $this->assertEquals('Amelia Carter', $response->guest_name);
        $this->assertEquals('Congratulations, you two!', $response->message);
        $this->assertEquals(false, $response->is_hidden);
    }

    public function test_declining_guest_stores_zero_count(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, ['is_attending' => false, 'count' => 0])->assertOk();

        $response = Response::first();
        $this->assertNotNull($response);
        $this->assertEquals(false, $response->is_attending);
        $this->assertEquals(0, $response->count);
    }

    public function test_declining_guest_with_message_stores_message(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, [
            'is_attending' => false,
            'count' => 0,
            'message' => 'Sending all our love!',
        ])->assertOk();

        $this->assertDatabaseHas('responses', [
            'invitation_id' => $invitation->id,
            'guest_name' => 'Amelia Carter',
            'is_attending' => false,
            'count' => 0,
            'message' => 'Sending all our love!',
        ]);
    }

    public function test_guest_name_is_required(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, ['guest_name' => ''])
            ->assertSessionHasErrors('guest_name');
    }

    public function test_count_is_required_and_bounded_when_attending(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, ['count' => null])
            ->assertSessionHasErrors('count');
        $this->postRsvp($invitation, ['count' => 0])
            ->assertSessionHasErrors('count');
        $this->postRsvp($invitation, ['count' => 11])
            ->assertSessionHasErrors('count');
    }

    public function test_message_cannot_exceed_max_length(): void
    {
        $invitation = $this->invitation();

        $this->postRsvp($invitation, ['message' => str_repeat('a', 1001)])
            ->assertSessionHasErrors('message');
    }

    public function test_rsvp_is_not_accepted_for_non_active_invitation(): void
    {
        $invitation = $this->invitation(null, ['status' => 'pending']);

        $this->postRsvp($invitation)->assertNotFound();

        $invitation = $this->invitation(null, ['status' => 'inactive']);
        $this->postRsvp($invitation)->assertNotFound();
    }

    public function test_show_page_returns_only_visible_wishes(): void
    {
        $invitation = $this->invitation();

        $invitation->responses()->create([
            'guest_name' => 'Amelia',
            'is_attending' => true,
            'count' => 1,
            'message' => 'Visible wish',
            'is_hidden' => false,
        ]);
        $invitation->responses()->create([
            'guest_name' => 'Oscar',
            'is_attending' => true,
            'count' => 1,
            'message' => 'Hidden wish',
            'is_hidden' => true,
        ]);
        $invitation->responses()->create([
            'guest_name' => 'Ruth',
            'is_attending' => false,
            'count' => 0,
            'message' => null,
            'is_hidden' => false,
        ]);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Templates/Classic')
                ->count('wishes', 1)
                ->where('wishes.0.message', 'Visible wish'));
    }

    public function test_owner_sees_responses_report_with_joined_details(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        $invitation->responses()->create([
            'guest_name' => 'Amelia Carter',
            'is_attending' => true,
            'count' => 2,
            'message' => 'Can not wait!',
            'is_hidden' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Invitations/Responses')
                ->where('invitation.groom_name', 'John')
                ->where('rows.data.0.guest_name', 'Amelia Carter')
                ->where('rows.data.0.is_attending', true)
                ->where('rows.data.0.count', 2)
                ->where('rows.data.0.message', 'Can not wait!')
                ->where('rows.data.0.is_hidden', false));
    }

    public function test_responses_report_summary_counts(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        $invitation->responses()->create([
            'guest_name' => 'Amelia',
            'is_attending' => true,
            'count' => 2,
            'message' => 'Yay',
            'is_hidden' => false,
        ]);
        $invitation->responses()->create([
            'guest_name' => 'Ben',
            'is_attending' => true,
            'count' => 3,
            'message' => null,
            'is_hidden' => false,
        ]);
        $invitation->responses()->create([
            'guest_name' => 'Cara',
            'is_attending' => false,
            'count' => 0,
            'message' => null,
            'is_hidden' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.total', 3)
                ->where('summary.attending', 2)
                ->where('summary.declined', 1)
                ->where('summary.confirmed_guests', 5)
                ->where('summary.wishes', 1));
    }

    public function test_non_owner_cannot_view_responses_report(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        $this->actingAs(User::factory()->create())
            ->get(route('invitations.responses', $invitation))
            ->assertForbidden();
    }

    public function test_responses_can_be_sorted_by_guest_name(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        foreach (['Zoe', 'Amy', 'Mia'] as $name) {
            $invitation->responses()->create([
                'guest_name' => $name,
                'is_attending' => true,
                'count' => 1,
                'message' => null,
                'is_hidden' => false,
            ]);
        }

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation) . '?sort=guest_name&direction=asc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.data.0.guest_name', 'Amy')
                ->where('rows.data.1.guest_name', 'Mia')
                ->where('rows.data.2.guest_name', 'Zoe')
                ->where('filters.sort', 'guest_name')
                ->where('filters.direction', 'asc'));

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation) . '?sort=guest_name&direction=desc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.data.0.guest_name', 'Zoe')
                ->where('filters.direction', 'desc'));
    }

    public function test_invalid_sort_falls_back_to_oldest_first(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        foreach (['Old', 'Mid', 'New'] as $name) {
            $invitation->responses()->create([
                'guest_name' => $name,
                'is_attending' => true,
                'count' => 1,
                'message' => null,
                'is_hidden' => false,
            ]);
        }

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation) . '?sort=bogus&direction=sideways')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.data.0.guest_name', 'Old')
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'asc'));
    }

    public function test_responses_pagination_preserves_sort_query(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        for ($i = 1; $i <= 21; $i++) {
            $invitation->responses()->create([
                'guest_name' => "Guest {$i}",
                'is_attending' => true,
                'count' => 1,
                'message' => null,
                'is_hidden' => false,
            ]);
        }

        $this->actingAs($owner)
            ->get(route('invitations.responses', $invitation) . '?sort=guest_name&direction=asc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.last_page', 2)
                ->where('rows.links.3.url', fn (string $url) => str_contains($url, 'sort=guest_name'))
                ->where('rows.links.3.url', fn (string $url) => str_contains($url, 'direction=asc')));
    }

    public function test_owner_hides_and_reveals_wish_from_customer_side(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        $response = $invitation->responses()->create([
            'guest_name' => 'Amelia',
            'is_attending' => true,
            'count' => 1,
            'message' => 'Congratulations!',
            'is_hidden' => false,
        ]);

        $this->actingAs($owner)
            ->patch(route('invitations.responses.visibility', [$invitation, $response]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($response->fresh()->is_hidden);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->count('wishes', 0));

        $this->actingAs($owner)
            ->patch(route('invitations.responses.visibility', [$invitation, $response]))
            ->assertRedirect();

        $this->assertFalse($response->fresh()->is_hidden);

        $this->get(route('invitations.show', $invitation->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->count('wishes', 1));
    }

    public function test_non_owner_cannot_toggle_wish_visibility(): void
    {
        $owner = User::factory()->create();
        $invitation = $this->invitation($owner);

        $response = $invitation->responses()->create([
            'guest_name' => 'Amelia',
            'is_attending' => true,
            'count' => 1,
            'message' => 'Congratulations!',
            'is_hidden' => false,
        ]);

        $this->actingAs(User::factory()->create())
            ->patch(route('invitations.responses.visibility', [$invitation, $response]))
            ->assertForbidden();
    }

    public function test_toggling_wish_of_another_invitation_returns_404(): void
    {
        $owner = User::factory()->create();
        $first = $this->invitation($owner);
        $second = $this->invitation($owner);

        $response = $first->responses()->create([
            'guest_name' => 'Amelia',
            'is_attending' => true,
            'count' => 1,
            'message' => 'Congratulations!',
            'is_hidden' => false,
        ]);

        $this->actingAs($owner)
            ->patch(route('invitations.responses.visibility', [$second, $response]))
            ->assertNotFound();
    }
}