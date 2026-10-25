<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('new users use the device theme by default', function () {
    $user = User::factory()->create();

    expect($user->fresh()->tema)->toBe('sistema');
});

test('appearance page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get('/settings/appearance')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('settings/appearance'));
});

test('users can change their theme and it is saved in their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/settings/appearance', ['tema' => 'oscuro'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($user->fresh()->tema)->toBe('oscuro');
});

test('the theme is shared with every page', function () {
    $user = User::factory()->create(['tema' => 'oscuro']);

    $this->actingAs($user)
        ->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('auth.user.tema', 'oscuro'));
});

test('invalid themes are rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/settings/appearance', ['tema' => 'morado'])
        ->assertSessionHasErrors('tema');

    expect($user->fresh()->tema)->toBe('sistema');
});
