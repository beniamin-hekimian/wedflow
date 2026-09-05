<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard'));
    }

    public function test_customers_are_redirected_from_the_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }

    public function test_guests_are_redirected_to_login_for_the_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}