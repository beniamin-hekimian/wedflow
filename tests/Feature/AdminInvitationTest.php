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

class AdminInvitationTest extends TestCase
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

    public function test_admin_can_view_the_paginated_invitations_report(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $i) {
            $this->invitation($user);
        }

        $this->actingAs($this->admin())
            ->get(route('admin.invitations.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Invitations/Index')
                ->has('invitations.data', 10)
                ->where('invitations.total', 12)
                ->where('invitations.last_page', 2)
                ->where('statusCounts.total', 12)
                ->where('statusCounts.pending', 12));
    }

    public function test_admin_can_filter_invitations_by_status(): void
    {
        $user = User::factory()->create();

        $this->invitation($user, ['status' => 'pending']);
        $this->invitation($user, ['status' => 'pending']);
        $this->invitation($user, ['status' => 'active']);

        $this->actingAs($this->admin())
            ->get(route('admin.invitations.index', ['status' => 'pending']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.status', 'pending')
                ->where('invitations.total', 2)
                ->where('invitations.data.0.status', 'pending'));
    }

    public function test_admin_can_search_invitations(): void
    {
        $user = User::factory()->create();

        $this->invitation($user, ['groom_name' => 'Albert', 'venue_name' => 'Grand Palace']);
        $this->invitation($user, ['groom_name' => 'Blake', 'venue_name' => 'Marina Bay']);

        $this->actingAs($this->admin())
            ->get(route('admin.invitations.index', ['search' => 'grand palace']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'grand palace')
                ->where('invitations.total', 1)
                ->where('invitations.data.0.groom_name', 'Albert'));
    }

    public function test_admin_can_update_invitation_status(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'pending']);

        $this->actingAs($this->admin())
            ->patch(route('admin.invitations.status', $invitation), ['status' => 'active'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('active', $invitation->fresh()->status);

        $this->actingAs($this->admin())
            ->get(route('admin.invitations.index'))
            ->assertInertia(fn (Assert $page) => $page->where(
                'flash.success',
                "Invitation #{$invitation->id} status updated to Active.",
            ));
    }

    public function test_invalid_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $invitation = $this->invitation($user, ['status' => 'pending']);

        $this->actingAs($this->admin())
            ->patch(route('admin.invitations.status', $invitation), ['status' => 'bogus'])
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $invitation->fresh()->status);
    }

    public function test_customers_cannot_update_invitation_status(): void
    {
        $customer = User::factory()->create();
        $invitation = $this->invitation($customer, ['status' => 'pending']);

        $this->actingAs($customer)
            ->patch(route('admin.invitations.status', $invitation), ['status' => 'active'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');

        $this->assertSame('pending', $invitation->fresh()->status);
    }
}