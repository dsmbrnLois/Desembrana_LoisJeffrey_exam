<?php

use App\Models\User;

test('guests can view the product listing page', function () {
    $response = $this->get(route('home'));
    $response->assertOk();
});

test('authenticated users can visit the product listing page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('home'));
    $response->assertOk();
});