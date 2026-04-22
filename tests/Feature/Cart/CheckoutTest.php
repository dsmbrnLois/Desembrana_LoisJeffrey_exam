<?php

use App\Models\Product;
use App\Models\User;

test('user can checkout cart', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10, 'price' => 100.00]);

    // Add items to cart
    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // Checkout
    $response = $this->actingAs($user)->postJson('/api/checkout');

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Order placed successfully!']);
});

test('checkout creates order with correct total', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10, 'price' => 250.50]);

    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)->postJson('/api/checkout');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total' => 751.50,
        'status' => 'pending',
    ]);
});

test('checkout decrements product stock', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10, 'price' => 100]);

    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)->postJson('/api/checkout');

    $product->refresh();
    $this->assertSame(7, $product->stocks);
});

test('checkout clears cart', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10, 'price' => 100]);

    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)->postJson('/api/checkout');

    $this->assertDatabaseCount('cart_items', 0);
});

test('cannot checkout empty cart', function () {
    $user = createGuest();

    $response = $this->actingAs($user)->postJson('/api/checkout');

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'Your cart is empty.']);
});

test('checkout creates order items with price snapshot', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10, 'price' => 499.99]);

    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)->postJson('/api/checkout');

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 499.99,
    ]);
});
