<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Process checkout — create order, decrement stock, clear cart.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
            ], 422);
        }

        // Validate stock for all items before processing
        foreach ($cartItems as $cartItem) {
            if ($cartItem->quantity > $cartItem->product->stocks) {
                return response()->json([
                    'message' => "Insufficient stock for {$cartItem->product->name}. Only {$cartItem->product->stocks} available.",
                ], 422);
            }
        }

        // Use DB transaction for data integrity
        $order = DB::transaction(function () use ($user, $cartItems) {
            $total = $cartItems->sum(fn (CartItem $item) => $item->quantity * $item->product->price);

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
            ]);

            // Create order items and decrement stock
            foreach ($cartItems as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);

                $cartItem->product->decrement('stocks', $cartItem->quantity);
            }

            // Clear the cart
            $user->cartItems()->delete();

            return $order;
        });

        $order->load('items.product');

        ActivityLogger::log('checkout', "Placed order #{$order->id} — Total: ₱{$order->total}", $order);

        return response()->json([
            'message' => 'Order placed successfully!',
            'order' => $order,
        ]);
    }
}
