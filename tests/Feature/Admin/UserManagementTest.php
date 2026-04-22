<?php

use App\Models\User;

test('admin can view users management page', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
});

test('guest cannot access users management', function () {
    $guest = createGuest();

    $response = $this->actingAs($guest)->get('/admin/users');

    $response->assertForbidden();
});

test('admin can create a user', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'New Guest',
        'email' => 'guest@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'guest@example.com',
        'role' => 'guest',
        'status' => 'active',
    ]);
});

test('admin can deactivate a guest user', function () {
    $admin = createAdmin();
    $guest = createGuest();

    $this->assertSame('active', $guest->status);

    $response = $this->actingAs($admin)->patch("/admin/users/{$guest->id}/toggle-status");

    $response->assertRedirect();

    $guest->refresh();
    $this->assertSame('deactivated', $guest->status);
});

test('admin can reactivate a deactivated guest', function () {
    $admin = createAdmin();
    $guest = User::factory()->deactivated()->create();

    $response = $this->actingAs($admin)->patch("/admin/users/{$guest->id}/toggle-status");

    $response->assertRedirect();

    $guest->refresh();
    $this->assertSame('active', $guest->status);
});

test('admin can delete a user', function () {
    $admin = createAdmin();
    $guest = createGuest();

    $response = $this->actingAs($admin)->delete("/admin/users/{$guest->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $guest->id]);
});
