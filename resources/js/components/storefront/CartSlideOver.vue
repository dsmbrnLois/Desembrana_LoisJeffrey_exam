<script setup lang="ts">
import { computed } from 'vue';
import { X, Trash2, ShoppingCart, Package, Minus, Plus } from 'lucide-vue-next';
import { useCartStore } from '@/stores/cart';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const cart = useCartStore();

function formatPrice(price: number): string {
    return Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

async function incrementQty(itemId: number, currentQty: number) {
    await cart.updateQuantity(itemId, currentQty + 1);
}

async function decrementQty(itemId: number, currentQty: number) {
    if (currentQty <= 1) {
        await cart.removeItem(itemId);
    } else {
        await cart.updateQuantity(itemId, currentQty - 1);
    }
}

async function handleCheckout() {
    await cart.checkout();
}
</script>

<template>
    <!-- Overlay -->
    <Transition
        enter-active-class="transition-opacity duration-300"
        leave-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        leave-to-class="opacity-0"
    >
        <div
            v-if="cart.showCart"
            class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
            @click="cart.toggleCart()"
        />
    </Transition>

    <!-- Slide-over Panel -->
    <Transition
        enter-active-class="transition-transform duration-300 ease-out"
        leave-active-class="transition-transform duration-300 ease-in"
        enter-from-class="translate-x-full"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="cart.showCart"
            class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-background shadow-2xl"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-border px-6 py-4">
                <div class="flex items-center gap-2">
                    <ShoppingCart class="h-5 w-5 text-primary" />
                    <h2 class="text-xl font-bold text-foreground">Cart</h2>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        v-if="!cart.isEmpty"
                        size="sm"
                        class="gap-1.5"
                        :disabled="cart.loading"
                        @click="handleCheckout"
                    >
                        <Spinner v-if="cart.loading" class="h-4 w-4" />
                        PLACE ORDER
                    </Button>

                    <button
                        class="rounded-lg p-1 text-muted-foreground hover:bg-accent hover:text-foreground"
                        @click="cart.toggleCart()"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div v-if="cart.isEmpty" class="flex h-full flex-col items-center justify-center">
                    <ShoppingCart class="h-16 w-16 text-muted-foreground/30" />
                    <p class="mt-4 text-lg font-medium text-muted-foreground">Your cart is empty</p>
                    <p class="text-sm text-muted-foreground/70">Add some products to get started</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="item in cart.items"
                        :key="item.id"
                        class="flex gap-4 rounded-xl border border-border/60 bg-card p-3 transition-all duration-200 hover:border-primary/20 hover:shadow-sm"
                    >
                        <!-- Product image -->
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-muted/50">
                            <img
                                v-if="item.product.image"
                                :src="`/storage/${item.product.image}`"
                                :alt="item.product.name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <Package class="h-8 w-8 text-muted-foreground/30" />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="flex flex-1 flex-col">
                            <h3 class="text-sm font-semibold text-primary">{{ item.product.name }}</h3>
                            <p class="text-sm font-bold text-foreground">
                                <span class="text-xs">₱</span>{{ formatPrice(item.product.price) }}
                            </p>

                            <!-- Quantity controls -->
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-muted-foreground">Qty:</span>
                                <div class="flex items-center rounded-lg border border-input">
                                    <button
                                        class="px-2 py-1 text-muted-foreground hover:text-foreground"
                                        @click="decrementQty(item.id, item.quantity)"
                                    >
                                        <Minus class="h-3 w-3" />
                                    </button>
                                    <span class="min-w-[2rem] text-center text-sm font-medium">
                                        {{ item.quantity }}
                                    </span>
                                    <button
                                        class="px-2 py-1 text-muted-foreground hover:text-foreground"
                                        @click="incrementQty(item.id, item.quantity)"
                                    >
                                        <Plus class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Remove -->
                        <button
                            class="self-start rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                            @click="cart.removeItem(item.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total -->
            <div v-if="!cart.isEmpty" class="bg-primary px-6 py-5 text-white">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold uppercase tracking-wide">Total:</span>
                    <span class="text-2xl font-bold">
                        <span class="text-sm">₱</span> {{ formatPrice(cart.total) }}
                    </span>
                </div>
            </div>
        </div>
    </Transition>
</template>
