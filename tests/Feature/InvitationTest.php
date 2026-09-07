<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Melody;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationTest extends TestCase
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

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'groom_name' => 'John Smith',
            'bride_name' => 'Jane Doe',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => '1 Main Street',
            'contact_phone' => '555-0100',
            'note' => 'Dress code: formal',
            'events' => [
                ['name' => 'Ceremony', 'time' => '16:00'],
                ['name' => 'Reception', 'time' => '19:00'],
            ],
            'photos' => [],
        ], $overrides);
    }

    public function test_auth_user_can_create_an_invitation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('invitations.store'), $this->validPayload());

        $response->assertRedirect(route('invitations.index'));

        $this->assertDatabaseHas('invitations', [
            'user_id' => $user->id,
            'slug' => 'john-jane',
            'status' => 'pending',
        ]);
    }

    public function test_slug_uses_only_the_first_word_of_each_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(
            route('invitations.store'),
            $this->validPayload([
                'groom_name' => 'John Michael Smith',
                'bride_name' => 'Jane Ann Doe',
            ]),
        );

        $this->assertDatabaseHas('invitations', ['slug' => 'john-jane']);
    }

    public function test_duplicate_slug_gets_a_numbered_suffix(): void
    {
        $user = User::factory()->create();

        Invitation::create([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'slug' => 'john-jane',
            'groom_name' => 'John Smith',
            'bride_name' => 'Jane Doe',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => '1 Main Street',
            'status' => 'pending',
        ]);

        $this->actingAs($user)->post(route('invitations.store'), $this->validPayload());

        $this->assertDatabaseHas('invitations', ['slug' => 'john-jane-2']);
    }

    private function makeInvitation(User $user, array $overrides = []): Invitation
    {
        return Invitation::create(array_merge([
            'user_id' => $user->id,
            'template_id' => $this->template->id,
            'melody_id' => $this->melody->id,
            'slug' => 'john-jane',
            'groom_name' => 'John Smith',
            'bride_name' => 'Jane Doe',
            'event_date' => now()->addMonths(2)->toDateString(),
            'event_time' => '18:00',
            'venue_name' => 'Grand Hall',
            'venue_address' => '1 Main Street',
            'status' => 'active',
        ], $overrides));
    }

    public function test_owner_can_view_the_edit_page(): void
    {
        $user = User::factory()->create();
        $invitation = $this->makeInvitation($user);
        $invitation->events()->createMany([
            ['name' => 'Ceremony', 'time' => '16:00', 'position' => 0],
            ['name' => 'Reception', 'time' => '19:00', 'position' => 1],
        ]);

        $response = $this->actingAs($user)->get(route('invitations.edit', $invitation));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Invitations/Edit')
            ->has('melodies')
            ->where('invitation.id', $invitation->id)
            ->has('invitation.events', 2));
    }

    public function test_owner_can_update_without_changing_status_or_slug(): void
    {
        $user = User::factory()->create();
        $invitation = $this->makeInvitation($user);
        $invitation->events()->createMany([
            ['name' => 'Ceremony', 'time' => '16:00', 'position' => 0],
            ['name' => 'Reception', 'time' => '19:00', 'position' => 1],
        ]);

        $response = $this->actingAs($user)->put(
            route('invitations.update', $invitation),
            $this->validPayload([
                'groom_name' => 'Robert Wilson',
                'events' => [
                    ['name' => 'Drinks', 'time' => '15:00'],
                    ['name' => 'Ceremony', 'time' => '16:30'],
                    ['name' => 'Party', 'time' => '22:00'],
                ],
            ]),
        );

        $response->assertRedirect(route('invitations.index'));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'groom_name' => 'Robert Wilson',
            'slug' => 'john-jane',
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('events', 3);
        $this->assertSame('Ceremony', $invitation->events()->get()[1]->name);
        $this->assertSame(2, $invitation->events()->max('position'));
    }

    public function test_update_removes_cropped_out_existing_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $invitation = $this->makeInvitation($user);

        $photoDir = "invitations/{$invitation->id}/";

        Storage::disk('public')->put("{$photoDir}a.jpg", 'a');
        Storage::disk('public')->put("{$photoDir}b.jpg", 'b');

        $photoA = $invitation->photos()->create([
            'photo_path' => "{$photoDir}a.jpg",
            'position' => 0,
        ]);
        $photoB = $invitation->photos()->create([
            'photo_path' => "{$photoDir}b.jpg",
            'position' => 1,
        ]);

        $response = $this->actingAs($user)->put(
            route('invitations.update', $invitation),
            $this->validPayload(['existing_photo_ids' => [$photoA->id]]),
        );

        $response->assertRedirect(route('invitations.index'));

        $this->assertDatabaseMissing('photos', ['id' => $photoB->id]);
        $this->assertDatabaseHas('photos', ['id' => $photoA->id]);
        Storage::disk('public')->assertMissing("{$photoDir}b.jpg");
        Storage::disk('public')->assertExists("{$photoDir}a.jpg");
    }

    public function test_update_rejects_more_than_five_photos_total(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $invitation = $this->makeInvitation($user);

        $photoDir = "invitations/{$invitation->id}/";

        $photos = collect(range(1, 4))->map(function ($i) use ($invitation, $photoDir) {
            return $invitation->photos()->create([
                'photo_path' => "{$photoDir}p{$i}.jpg",
                'position' => $i - 1,
            ])->id;
        })->all();

        $files = array_map(
            fn ($name) => UploadedFile::fake()->createWithContent(
                $name,
                base64_decode(
                    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQAY/goKAAAAAElFTkSuQmCC',
                ),
            ),
            ['new-1.png', 'new-2.png'],
        );

        $response = $this->actingAs($user)->put(
            route('invitations.update', $invitation),
            $this->validPayload(['existing_photo_ids' => $photos, 'photos' => $files]),
        );

        $response->assertSessionHasErrors('photos');
    }

    public function test_owner_can_delete_an_invitation_and_its_photos(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $invitation = $this->makeInvitation($user);

        $photoDir = "invitations/{$invitation->id}/";

        Storage::disk('public')->put("{$photoDir}a.jpg", 'a');
        $invitation->photos()->create(['photo_path' => "{$photoDir}a.jpg", 'position' => 0]);

        $response = $this->actingAs($user)->delete(route('invitations.destroy', $invitation));

        $response->assertRedirect(route('invitations.index'));
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        $this->assertDatabaseMissing('photos', ['photo_path' => "{$photoDir}a.jpg"]);
        Storage::disk('public')->assertMissing($photoDir);
    }

    public function test_non_owner_cannot_edit_update_or_delete(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $invitation = $this->makeInvitation($owner);

        $this->actingAs($intruder)->get(route('invitations.edit', $invitation))->assertForbidden();
        $this->actingAs($intruder)->put(route('invitations.update', $invitation), $this->validPayload())->assertForbidden();
        $this->actingAs($intruder)->delete(route('invitations.destroy', $invitation))->assertForbidden();

        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }
}