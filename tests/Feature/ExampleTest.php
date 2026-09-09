<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_homepage_redirects_to_the_dashboard(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_guests_can_view_the_dashboard_without_logging_in(): void
    {
        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard');
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
    }

    public function test_guests_are_sent_to_login_before_creating_a_complaint(): void
    {
        $response = $this->get(route('pengaduan.create'));

        $response->assertRedirect(route('login'));
    }
}
