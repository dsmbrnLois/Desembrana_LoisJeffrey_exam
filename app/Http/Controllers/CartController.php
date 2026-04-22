<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get the current user's cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = $request->user()
            ->cartItems()
            ->with('product')
            ->get();

        $total = $cartItems->sum(fn (CartItem $item) => $item->quantity * $item->product->price);

        return response()->json([
            'items' => $cartItems,
            'total' => round($total, 2),
            'count' => $cartItems->sum('quantity'),
        ]);
    }

    /**
     * Add a product to the cart (or increment quantity if already there).
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        // Check stock
        if (! $product->inStock()) {
            return response()->json([
                'message' => 'This product is out of stock.',
            ], 422);
        }

        // Check if requested quantity exceeds stock
        $existingCartItem = $request->user()
            ->cartItems()
            ->where('product_id', $product->id)
            ->first();

        $currentQty = $existingCartItem ? $existingCartItem->quantity : 0;
        $newTotalQty = $currentQty + $validated['quantity'];

        if ($newTotalQty > $product->stocks) {
            return response()->json([
                'message' => "Only {$product->stocks} items available in stock.",
            ], 422);
        }

        // Add or increment
        $cartItem = $request->user()->cartItems()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $newTotalQty]
        );

        $cartItem->load('product');

        ActivityLogger::log('cart_add', "Added {$product->name} to cart", $product);

        return response()->json([
            'message' => 'Product added to cart.',
            'item' => $cartItem,
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        // Ensure user owns this cart item
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = $cartItem->product;

        if ($request->quantity > $product->stocks) {
            return response()->json([
                'message' => "Only {$product->stocks} items available in stock.",
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);
        $cartItem->load('product');

        return response()->json([
            'message' => 'Cart updated.',
            'item' => $cartItem,
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart.',
        ]);
    }
}
