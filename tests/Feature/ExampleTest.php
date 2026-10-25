<?php

use App\Models\User;

it('redirects guests from the buscador to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});

it('shows the buscador to authenticated users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertOk();
});
