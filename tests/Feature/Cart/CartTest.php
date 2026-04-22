<?php

use App\Models\Product;
use App\Models\User;

test('authenticated user can add product to cart', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10]);

    $response = $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertOk();
    $response->assertJsonFragment(['message' => 'Product added to cart.']);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('adding same product to cart increments quantity', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10]);

    // Add first time
    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    // Add second time
    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    // Should only have one cart item row, not two
    $this->assertDatabaseCount('cart_items', 1);
});

test('cannot add out-of-stock product to cart', function () {
    $user = createGuest();
    $product = Product::factory()->outOfStock()->create();

    $response = $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertStatus(422);
    $response->assertJsonFragment(['message' => 'This product is out of stock.']);
});

test('unauthenticated user cannot add to cart', function () {
    $product = Product::factory()->create(['stocks' => 10]);

    $response = $this->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can view cart', function () {
    $user = createGuest();

    $response = $this->actingAs($user)->getJson('/api/cart');

    $response->assertOk();
    $response->assertJsonStructure(['items', 'total', 'count']);
});

test('user can remove item from cart', function () {
    $user = createGuest();
    $product = Product::factory()->create(['stocks' => 10]);

    $this->actingAs($user)->postJson('/api/cart', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $cartItem = $user->cartItems()->first();

    $response = $this->actingAs($user)->deleteJson("/api/cart/{$cartItem->id}");

    $response->assertOk();
    $this->assertDatabaseCount('cart_items', 0);
});
