<?php

use App\Models\Product;
use App\Models\User;

test('admin can view products management page', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->get('/admin/products');

    $response->assertOk();
});

test('guest cannot access products management', function () {
    $guest = createGuest();

    $response = $this->actingAs($guest)->get('/admin/products');

    $response->assertForbidden();
});

test('admin can create a product', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->post('/admin/products', [
        'name' => 'Test Product',
        'description' => 'A test product description.',
        'price' => 1499.99,
        'stocks' => 50,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'price' => 1499.99,
        'stocks' => 50,
    ]);
});

test('admin can update a product', function () {
    $admin = createAdmin();
    $product = Product::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
        'name' => 'Updated Name',
        'price' => 999.99,
        'stocks' => 25,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 999.99,
    ]);
});

test('admin can delete a product', function () {
    $admin = createAdmin();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('product creation validates required fields', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->post('/admin/products', [
        'name' => '',
        'price' => '',
        'stocks' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'price', 'stocks']);
});
