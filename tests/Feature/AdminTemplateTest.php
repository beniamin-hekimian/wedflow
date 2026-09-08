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

class AdminTemplateTest extends TestCase
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

    private function template(string $name, string $slug): Template
    {
        return Template::create([
            'name' => $name,
            'slug' => $slug,
            'description' => "Description for {$name}",
            'thumbnail_path' => "templates-assets/{$slug}/thumbnail.jpeg",
            'intro_video_path' => "templates-assets/{$slug}/intro.mp4",
        ]);
    }

    private function invitation(User $user, Template $template): Invitation
    {
        return Invitation::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
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

    public function test_admin_can_view_the_paginated_templates_report(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->template("Template {$i}", "template-{$i}");
        }

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Templates/Index')
                ->has('templates.data', 10)
                ->where('templates.total', 13)
                ->where('templates.last_page', 2)
                ->where('usageCounts.total', 13)
                ->where('usageCounts.inUse', 0)
                ->where('usageCounts.unused', 13));
    }

    public function test_admin_can_search_templates_by_name_slug_and_description(): void
    {
        $this->template('Sunset Garden', 'sunset-garden');
        $this->template('Deep Blue', 'deep-blue');

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['search' => 'garden']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'garden')
                ->where('templates.total', 1)
                ->where('templates.data.0.name', 'Sunset Garden'));

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['search' => 'deep-blue']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('templates.total', 1)
                ->where('templates.data.0.slug', 'deep-blue'));

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['search' => 'elegant classic']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('templates.total', 1)
                ->where('templates.data.0.name', 'Classic'));
    }

    public function test_admin_can_sort_templates_by_name(): void
    {
        $this->template('Zulu', 'zulu');
        $this->template('Alpha', 'alpha');
        $this->template('Mike', 'mike');

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['sort' => 'name', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
                ->where('templates.data.0.name', 'Alpha')
                ->where('templates.data.1.name', 'Classic')
                ->where('templates.data.2.name', 'Mike')
                ->where('templates.data.3.name', 'Zulu'));
    }

    public function test_admin_can_sort_templates_by_invitations_count(): void
    {
        $unused = $this->template('Unused', 'unused');
        $popular = $this->template('Popular', 'popular');
        $solo = $this->template('Solo', 'solo');
        $user = User::factory()->create();

        $this->invitation($user, $popular);
        $this->invitation($user, $popular);
        $this->invitation($user, $solo);

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['sort' => 'invitations_count', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('templates.data.0.name', 'Classic')
                ->where('templates.data.0.invitations_count', 0)
                ->where('templates.data.1.name', $unused->name)
                ->where('templates.data.1.invitations_count', 0)
                ->where('templates.data.2.name', $solo->name)
                ->where('templates.data.2.invitations_count', 1)
                ->where('templates.data.3.name', $popular->name)
                ->where('templates.data.3.invitations_count', 2));
    }

    public function test_admin_can_filter_templates_by_usage(): void
    {
        $customer = User::factory()->create();
        $custom = $this->template('Custom', 'custom');
        $this->invitation($customer, $custom);

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['usage' => 'in-use']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'in-use')
                ->where('templates.total', 1)
                ->where('templates.data.0.name', 'Custom')
                ->where('usageCounts.inUse', 1));

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['usage' => 'unused']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'unused')
                ->where('templates.total', 1)
                ->where('templates.data.0.name', 'Classic')
                ->where('usageCounts.unused', 1));
    }

    public function test_invalid_sort_falls_back_to_oldest_first(): void
    {
        $this->template('Oldest', 'oldest');
        $this->template('Latest', 'latest');

        $this->actingAs($this->admin())
            ->get(route('admin.templates.index', ['sort' => 'bogus', 'direction' => 'sideways']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'asc')
                ->where('templates.data.0.name', 'Classic'));
    }

    public function test_customers_cannot_view_templates_report(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.templates.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }
}