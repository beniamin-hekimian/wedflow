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

class AdminDashboardTest extends TestCase
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

    private function responses(Invitation $invitation, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $invitation->responses()->create([
                'guest_name' => 'Guest ' . ($i + 1),
                'is_attending' => true,
                'count' => 1,
                'message' => null,
                'is_hidden' => false,
            ]);
        }
    }

    public function test_admin_can_view_dashboard_statistics(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();

        $this->invitation($customer, ['status' => 'pending']);
        $this->invitation($customer, ['status' => 'active']);
        $this->invitation($customer, ['status' => 'inactive']);
        $withResponses = $this->invitation($customer, ['status' => 'active']);
        $this->responses($withResponses, 3);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.total', 4)
                ->where('stats.active', 2)
                ->where('stats.pending', 1)
                ->where('stats.users', 2)
                ->where('stats.admins', 1));
    }

    public function test_top_three_invitations_are_ordered_by_most_received_rsvps(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();

        $three = $this->invitation($customer);
        $one = $this->invitation($customer);
        $zero = $this->invitation($customer);
        $five = $this->invitation($customer);
        $two = $this->invitation($customer);

        $this->responses($three, 3);
        $this->responses($one, 1);
        $this->responses($zero, 0);
        $this->responses($five, 5);
        $this->responses($two, 2);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('topInvitations', 3)
                ->where('topInvitations.0.slug', $five->slug)
                ->where('topInvitations.0.responses_count', 5)
                ->where('topInvitations.1.slug', $three->slug)
                ->where('topInvitations.1.responses_count', 3)
                ->where('topInvitations.2.slug', $two->slug)
                ->where('topInvitations.2.responses_count', 2));
    }

    public function test_customer_is_redirected_from_the_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'));
    }
}