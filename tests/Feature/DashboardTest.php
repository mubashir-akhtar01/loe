<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->followingRedirects()->get(route('dashboard'));
    $response->assertOk()
        ->assertSeeText('Your reporting cockpit for')
        ->assertSeeText('What needs your attention')
        ->assertSeeText('Recent months');
});
