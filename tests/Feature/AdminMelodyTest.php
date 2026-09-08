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

class AdminMelodyTest extends TestCase
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

    private function melody(string $name, string $filePath): Melody
    {
        return Melody::create([
            'name' => $name,
            'file_path' => $filePath,
        ]);
    }

    private function invitation(User $user, Melody $melody): Invitation
    {
        return Invitation::create([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $melody->id,
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

    public function test_admin_can_view_the_paginated_melodies_report(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->melody("Melody {$i}", "melodies-assets/melody-{$i}.mp3");
        }

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Melodies/Index')
                ->has('melodies.data', 10)
                ->where('melodies.total', 13)
                ->where('melodies.last_page', 2)
                ->where('usageCounts.total', 13)
                ->where('usageCounts.inUse', 0)
                ->where('usageCounts.unused', 13));
    }

    public function test_admin_can_search_melodies_by_name_and_file_path(): void
    {
        $this->melody('Canon', 'melodies-assets/canon.mp3');
        $this->melody('Vegas', 'melodies-assets/vegas.mp3');

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['search' => 'canon']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'canon')
                ->where('melodies.total', 1)
                ->where('melodies.data.0.name', 'Canon'));

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['search' => 'melodies-assets/vegas']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('melodies.total', 1)
                ->where('melodies.data.0.file_path', 'melodies-assets/vegas.mp3'));
    }

    public function test_admin_can_sort_melodies_by_name(): void
    {
        $this->melody('Zulu', 'melodies-assets/zulu.mp3');
        $this->melody('Alpha', 'melodies-assets/alpha.mp3');
        $this->melody('Mike', 'melodies-assets/mike.mp3');

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['sort' => 'name', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
                ->where('melodies.data.0.name', 'Alpha')
                ->where('melodies.data.1.name', 'Classic')
                ->where('melodies.data.2.name', 'Mike')
                ->where('melodies.data.3.name', 'Zulu'));
    }

    public function test_admin_can_sort_melodies_by_invitations_count(): void
    {
        $unused = $this->melody('Unused', 'melodies-assets/unused.mp3');
        $popular = $this->melody('Popular', 'melodies-assets/popular.mp3');
        $solo = $this->melody('Solo', 'melodies-assets/solo.mp3');
        $user = User::factory()->create();

        $this->invitation($user, $popular);
        $this->invitation($user, $popular);
        $this->invitation($user, $solo);

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['sort' => 'invitations_count', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('melodies.data.0.name', 'Classic')
                ->where('melodies.data.0.invitations_count', 0)
                ->where('melodies.data.1.name', $unused->name)
                ->where('melodies.data.1.invitations_count', 0)
                ->where('melodies.data.2.name', $solo->name)
                ->where('melodies.data.2.invitations_count', 1)
                ->where('melodies.data.3.name', $popular->name)
                ->where('melodies.data.3.invitations_count', 2));
    }

    public function test_admin_can_filter_melodies_by_usage(): void
    {
        $customer = User::factory()->create();
        $custom = $this->melody('Custom', 'melodies-assets/custom.mp3');
        $this->invitation($customer, $custom);

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['usage' => 'in-use']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'in-use')
                ->where('melodies.total', 1)
                ->where('melodies.data.0.name', 'Custom')
                ->where('usageCounts.inUse', 1));

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['usage' => 'unused']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.usage', 'unused')
                ->where('melodies.total', 1)
                ->where('melodies.data.0.name', 'Classic')
                ->where('usageCounts.unused', 1));
    }

    public function test_invalid_sort_falls_back_to_oldest_first(): void
    {
        $this->melody('Oldest', 'melodies-assets/oldest.mp3');
        $this->melody('Latest', 'melodies-assets/latest.mp3');

        $this->actingAs($this->admin())
            ->get(route('admin.melodies.index', ['sort' => 'bogus', 'direction' => 'sideways']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.sort', 'created_at')
                ->where('filters.direction', 'asc')
                ->where('melodies.data.0.name', 'Classic'));
    }

    public function test_customers_cannot_view_melodies_report(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.melodies.index'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }
}