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

class AdminResponseTest extends TestCase
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

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function invitation(User $user, array $overrides = []): Invitation
    {
        return Invitation::create(array_merge([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'slug' => 'invitation-' . Str::uuid(),
            'groom_name' => 'John',
            'bride_name' => 'Jane',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => '1 Main Street',
            'status' => 'pending',
        ], $overrides));
    }

    private function response(Invitation $invitation, array $overrides = []): Response
    {
        return $invitation->responses()->create(array_merge([
            'guest_name' => 'Amy',
            'is_attending' => true,
            'count' => 1,
            'message' => null,
            'is_hidden' => false,
        ], $overrides));
    }

    public function test_admin_can_view_the_grouped_paginated_responses_report(): void
    {
        $customer = User::factory()->create();

        for ($i = 1; $i <= 7; $i++) {
            $this->response($this->invitation($customer), [
                'guest_name' => "Guest {$i}",
                'is_attending' => $i <= 4,
            ]);
        }

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Responses/Index')
                ->has('invitations.data', 5)
                ->where('invitations.total', 7)
                ->where('invitations.last_page', 2)
                ->where('invitations.data.0.responses_count', 1)
                ->where('invitations.data.0.attending_count', 1)
                ->where('invitations.data.0.declined_count', 0)
                ->where('invitations.data.0.guests_count', 1)
                ->where('invitations.data.0.wishes_count', 0)
                ->has('invitations.data.0.responses', 1)
                ->where('attendanceCounts.total', 7)
                ->where('attendanceCounts.attending', 4)
                ->where('attendanceCounts.declined', 3));
    }

    public function test_admin_can_sort_invitation_groups_by_couple_name(): void
    {
        $customer = User::factory()->create();

        $this->response($this->invitation($customer, ['groom_name' => 'Bbb', 'bride_name' => 'Yyy']));
        $this->response($this->invitation($customer, ['groom_name' => 'Aaa', 'bride_name' => 'Zzz']));
        $this->response($this->invitation($customer, ['groom_name' => 'Ccc', 'bride_name' => 'Xxx']));

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['sort' => 'couple_name', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'couple_name')
                ->where('filters.direction', 'asc')
                ->where('invitations.data.0.groom_name', 'Aaa')
                ->where('invitations.data.1.groom_name', 'Bbb')
                ->where('invitations.data.2.groom_name', 'Ccc'));

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['sort' => 'couple_name', 'direction' => 'desc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('invitations.data.0.groom_name', 'Ccc'));
    }

    public function test_admin_can_filter_invitation_groups_by_attendance(): void
    {
        $customer = User::factory()->create();
        $invitation = $this->invitation($customer);

        $this->response($invitation, ['guest_name' => 'Accepting Amy', 'is_attending' => true]);
        $this->response($invitation, ['guest_name' => 'Declining Dan', 'is_attending' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['attending' => 'attending']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.attending', 'attending')
                ->where('invitations.total', 1)
                ->where('invitations.data.0.attending_count', 1)
                ->where('invitations.data.0.declined_count', 0)
                ->has('invitations.data.0.responses', 1)
                ->where('invitations.data.0.responses.0.guest_name', 'Accepting Amy'));

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['attending' => 'declined']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('invitations.total', 1)
                ->where('invitations.data.0.responses.0.guest_name', 'Declining Dan'));
    }

    public function test_admin_can_search_invitation_groups_by_guest_name_and_message(): void
    {
        $customer = User::factory()->create();

        $this->response($this->invitation($customer), [
            'guest_name' => 'Carmen',
            'message' => 'Best wishes on your big day',
        ]);
        $this->response($this->invitation($customer), [
            'guest_name' => 'Zed',
            'message' => null,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['search' => 'carmen']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'carmen')
                ->where('invitations.total', 1)
                ->where('invitations.data.0.responses_count', 1)
                ->where('invitations.data.0.responses.0.guest_name', 'Carmen'));

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['search' => 'big day']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('invitations.total', 1)
                ->where('invitations.data.0.responses.0.message', 'Best wishes on your big day'));
    }

    public function test_invalid_sort_falls_back_to_oldest_group_first(): void
    {
        $customer = User::factory()->create();

        $this->response($this->invitation($customer, ['groom_name' => 'Old']));
        $this->response($this->invitation($customer, ['groom_name' => 'New']));

        $this->actingAs($this->admin())
            ->get(route('admin.responses.index', ['sort' => 'bogus', 'direction' => 'sideways']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'asc')
                ->where('invitations.data.0.groom_name', 'Old'));
    }

    public function test_customers_cannot_view_responses_report(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.responses.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }
}