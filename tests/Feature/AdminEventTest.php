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

class AdminEventTest extends TestCase
{
    use RefreshDatabase;

    private Template $template;

    private Melody $melody;

    private Event $defaultEvent;

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

        $this->defaultEvent = $this->event('Welcome Drinks', 0);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function event(string $name, int $sortOrder): Event
    {
        return Event::create([
            'name' => $name,
            'sort_order' => $sortOrder,
        ]);
    }

    private function invitation(User $user, Event $event): Invitation
    {
        $invitation = Invitation::create([
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
        ]);

        $invitation->events()->sync([
            $event->id => ['time' => '18:00', 'position' => 0],
        ]);

        return $invitation;
    }

    public function test_admin_can_view_the_paginated_events_report(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->event("Event {$i}", $i);
        }

        $this->actingAs($this->admin())
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Events/Index')
                ->has('events.data', 10)
                ->where('events.total', 13)
                ->where('events.last_page', 2)
                ->where('usageCounts.total', 13)
                ->where('usageCounts.inUse', 0)
                ->where('usageCounts.unused', 13));
    }

    public function test_admin_events_are_ordered_by_sort_order_by_default(): void
    {
        $this->event('Last Dance', 8);
        $this->event('First Kiss', 3);
        $this->event('Family Photos', 1);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'sort_order')
                ->where('filters.direction', 'asc')
                ->where('events.data.0.name', 'Welcome Drinks')
                ->where('events.data.1.name', 'Family Photos')
                ->where('events.data.2.name', 'First Kiss')
                ->where('events.data.3.name', 'Last Dance'));
    }

    public function test_admin_can_search_events_by_name(): void
    {
        $this->event('Dance Party', 1);
        $this->event('First Dance', 2);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['search' => 'party']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'party')
                ->where('events.total', 1)
                ->where('events.data.0.name', 'Dance Party'));

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['search' => 'drinks']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('events.total', 1)
                ->where('events.data.0.name', 'Welcome Drinks'));
    }

    public function test_admin_can_sort_events_by_name(): void
    {
        $this->event('Zulu', 1);
        $this->event('Alpha', 2);
        $this->event('Mike', 3);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['sort' => 'name', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
                ->where('events.data.0.name', 'Alpha')
                ->where('events.data.1.name', 'Mike')
                ->where('events.data.2.name', 'Welcome Drinks')
                ->where('events.data.3.name', 'Zulu'));
    }

    public function test_admin_can_sort_events_by_invitations_count(): void
    {
        $unused = $this->event('Unused', 1);
        $popular = $this->event('Popular', 2);
        $solo = $this->event('Solo', 3);
        $user = User::factory()->create();

        $this->invitation($user, $popular);
        $this->invitation($user, $popular);
        $this->invitation($user, $solo);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['sort' => 'invitations_count', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('events.data.0.name', 'Welcome Drinks')
                ->where('events.data.0.invitations_count', 0)
                ->where('events.data.1.name', $unused->name)
                ->where('events.data.1.invitations_count', 0)
                ->where('events.data.2.name', $solo->name)
                ->where('events.data.2.invitations_count', 1)
                ->where('events.data.3.name', $popular->name)
                ->where('events.data.3.invitations_count', 2));
    }

    public function test_admin_can_filter_events_by_usage(): void
    {
        $customer = User::factory()->create();
        $custom = $this->event('Custom', 1);
        $this->invitation($customer, $custom);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['usage' => 'in-use']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'in-use')
                ->where('events.total', 1)
                ->where('events.data.0.name', 'Custom')
                ->where('usageCounts.inUse', 1));

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['usage' => 'unused']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'unused')
                ->where('events.total', 1)
                ->where('events.data.0.name', 'Welcome Drinks')
                ->where('usageCounts.unused', 1));
    }

    public function test_invalid_sort_falls_back_to_catalog_order(): void
    {
        $this->event('Last', 9);
        $this->event('First', 1);

        $this->actingAs($this->admin())
            ->get(route('admin.events.index', ['sort' => 'bogus', 'direction' => 'sideways']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'sort_order')
                ->where('filters.direction', 'asc')
                ->where('events.data.0.name', 'Welcome Drinks')
                ->where('events.data.1.name', 'First')
                ->where('events.data.2.name', 'Last'));
    }

    public function test_customers_cannot_view_events_report(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.events.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }
}