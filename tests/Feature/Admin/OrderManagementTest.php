<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('admin can view orders page', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->get('/admin/orders');

    $response->assertOk();
});

test('guest cannot access orders management', function () {
    $guest = createGuest();

    $response = $this->actingAs($guest)->get('/admin/orders');

    $response->assertForbidden();
});

test('admin can update order status', function () {
    $admin = createAdmin();
    $guest = createGuest();
    $product = Product::factory()->create(['stocks' => 10]);

    $order = Order::create([
        'user_id' => $guest->id,
        'total' => 1000,
        'status' => Order::STATUS_PENDING,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 500,
    ]);

    $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
        'status' => Order::STATUS_DELIVERED,
    ]);

    $response->assertRedirect();

    $order->refresh();
    $this->assertSame(Order::STATUS_DELIVERED, $order->status);
});

test('order status update validates status value', function () {
    $admin = createAdmin();
    $guest = createGuest();

    $order = Order::create([
        'user_id' => $guest->id,
        'total' => 500,
        'status' => Order::STATUS_PENDING,
    ]);

    $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}/status", [
        'status' => 'invalid_status',
    ]);

    $response->assertSessionHasErrors('status');
});
