<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('guests visiting the home page are redirected to the employee login page', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('filament.employee.auth.login'));
});

test('authenticated employees visiting the home page are redirected to their dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('dashboard'));
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
