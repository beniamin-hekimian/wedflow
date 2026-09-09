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

class AdminUserTest extends TestCase
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

    private function admin(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin'], $overrides));
    }

    private function invitation(User $user): Invitation
    {
        return Invitation::create([
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
    }

    public function test_admin_can_view_the_paginated_users_report(): void
    {
        User::factory()->count(12)->create();

        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 10)
                ->where('users.total', 13)
                ->where('users.last_page', 2)
                ->where('roleCounts.total', 13)
                ->where('roleCounts.customer', 12)
                ->where('roleCounts.admin', 1));
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        User::factory()->count(3)->create(['role' => 'customer']);
        User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['role' => 'admin']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.role', 'admin')
                ->where('users.total', 2)
                ->where('users.data.0.role', 'admin'));

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['role' => 'customer']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('users.total', 3));
    }

    public function test_admin_can_search_users_by_name_and_email(): void
    {
        User::factory()->create(['name' => 'Albert Wonder', 'email' => 'albert@example.com']);
        User::factory()->create(['name' => 'Blake Jones', 'email' => 'blake@example.com']);

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['search' => 'wonder']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'wonder')
                ->where('users.total', 1)
                ->where('users.data.0.email', 'albert@example.com'));

        $this->actingAs($this->admin())
            ->get(route('admin.users.index', ['search' => 'blake@example.com']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.total', 1)
                ->where('users.data.0.name', 'Blake Jones'));
    }

    public function test_admin_can_sort_users_by_name(): void
    {
        $admin = $this->admin(['name' => 'Aaa Admin']);

        User::factory()->create(['name' => 'Zoe']);
        User::factory()->create(['name' => 'Amy']);
        User::factory()->create(['name' => 'Max']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
                ->where('users.data.0.name', 'Aaa Admin')
                ->where('users.data.1.name', 'Amy')
                ->where('users.data.2.name', 'Max')
                ->where('users.data.3.name', 'Zoe'));

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['sort' => 'name', 'direction' => 'desc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.data.0.name', 'Zoe')
                ->where('users.data.3.name', 'Aaa Admin'));
    }

    public function test_admin_can_sort_users_by_invitations_count(): void
    {
        $alice = User::factory()->create(['name' => 'Aaa Alice']);
        $bob = User::factory()->create(['name' => 'Bbb Bob']);
        $cara = User::factory()->create(['name' => 'Ccc Cara']);

        $this->invitation($bob);
        $this->invitation($bob);
        $this->invitation($cara);

        $admin = $this->admin(['name' => 'Zzz Admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['sort' => 'invitations_count', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('users.data.0.name', 'Aaa Alice')
                ->where('users.data.0.invitations_count', 0)
                ->where('users.data.1.name', 'Zzz Admin')
                ->where('users.data.1.invitations_count', 0)
                ->where('users.data.2.name', 'Ccc Cara')
                ->where('users.data.2.invitations_count', 1)
                ->where('users.data.3.name', 'Bbb Bob')
                ->where('users.data.3.invitations_count', 2));
    }

    public function test_invalid_sort_falls_back_to_oldest_first(): void
    {
        $old = User::factory()->create(['name' => 'Old']);
        User::factory()->create(['name' => 'Latest']);

        $admin = $this->admin(['name' => 'Newest Admin']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['sort' => 'bogus', 'direction' => 'sideways']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'asc')
                ->where('users.data.0.id', $old->id));
    }

    public function test_customers_cannot_view_users_report(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_admin_can_update_another_users_role(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['name' => 'Sam Customer', 'role' => 'customer']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $customer), ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('admin', $customer->fresh()->role);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $customer), ['role' => 'customer'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('customer', $customer->fresh()->role);
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin(['name' => 'Boss Admin']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'customer'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_updating_user_role_validates_value(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.users.role', $user), ['role' => 'superadmin'])
            ->assertSessionHasErrors('role');

        $this->assertSame('customer', $user->fresh()->role);
    }

    public function test_role_count_refreshes_after_update(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $customer), ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('roleCounts.admin', 2)
                ->where('roleCounts.customer', 0));
    }
}