<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Package, ShoppingCart, X } from 'lucide-vue-next';
import { useCartStore } from '@/stores/cart';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { Product } from '@/types';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    product: Product | null;
    open: boolean;
}>();

const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const cart = useCartStore();
const quantity = ref(1);
const adding = ref(false);
const message = ref('');
const messageType = ref<'success' | 'error'>('success');

const user = computed(() => page.props.auth?.user);
const isLoggedIn = computed(() => !!user.value);
const isOutOfStock = computed(() => !props.product || props.product.stocks <= 0);

// Reset state when modal opens
watch(() => props.open, (val) => {
    if (val) {
        quantity.value = 1;
        message.value = '';
    }
});

// Generate quantity options
const quantityOptions = computed(() => {
    if (!props.product) return [];
    const max = Math.min(props.product.stocks, 10);
    return Array.from({ length: max }, (_, i) => i + 1);
});

function formatPrice(price: number): string {
    return Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

async function addToCart() {
    if (!isLoggedIn.value) {
        router.visit('/login');
        return;
    }

    if (!props.product) return;

    adding.value = true;
    message.value = '';

    const result = await cart.addToCart(props.product.id, quantity.value);

    message.value = result.message;
    messageType.value = result.success ? 'success' : 'error';
    adding.value = false;

    if (result.success) {
        setTimeout(() => {
            emit('close');
        }, 1000);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val: boolean) => !val && emit('close')">
        <DialogContent class="sm:max-w-xl">
            <DialogHeader>
                <DialogTitle class="sr-only">Product Details</DialogTitle>
            </DialogHeader>

            <div v-if="product" class="flex flex-col gap-6 sm:flex-row">
                <!-- Image -->
                <div class="relative aspect-square w-full overflow-hidden rounded-xl bg-muted/50 sm:w-56 sm:shrink-0">
                    <img
                        v-if="product.image"
                        :src="`/storage/${product.image}`"
                        :alt="product.name"
                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center"
                    >
                        <Package class="h-16 w-16 text-muted-foreground/30" />
                    </div>
                </div>

                <!-- Details -->
                <div class="flex flex-1 flex-col">
                    <h2 class="text-xl font-bold text-primary">{{ product.name }}</h2>
                    <p class="mt-1 text-2xl font-bold text-foreground">
                        <span class="text-sm align-top">₱</span>{{ formatPrice(product.price) }}
                    </p>

                    <p v-if="product.description" class="mt-3 text-sm text-muted-foreground">
                        {{ product.description }}
                    </p>

                    <p class="mt-2 text-xs text-muted-foreground">
                        {{ product.stocks > 0 ? `${product.stocks} in stock` : '' }}
                    </p>

                    <!-- Quantity selector -->
                    <div class="mt-4">
                        <label class="text-sm font-medium text-foreground">Quantity</label>
                        <select
                            v-model.number="quantity"
                            :disabled="isOutOfStock"
                            class="mt-1 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                            <option v-for="n in quantityOptions" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <!-- Message -->
                    <p
                        v-if="message"
                        :class="[
                            'mt-3 text-sm font-medium',
                            messageType === 'success' ? 'text-green-600' : 'text-destructive',
                        ]"
                    >
                        {{ message }}
                    </p>

                    <!-- Add to Cart button -->
                    <Button
                        class="mt-4 w-full gap-2"
                        :disabled="isOutOfStock || adding"
                        @click="addToCart"
                    >
                        <Spinner v-if="adding" class="h-4 w-4" />
                        <ShoppingCart v-else class="h-4 w-4" />
                        {{ isOutOfStock ? 'Out of Stock' : 'Add To Cart' }}
                    </Button>

                    <p v-if="!isLoggedIn && !isOutOfStock" class="mt-2 text-center text-xs text-muted-foreground">
                        You must be logged in to add items to your cart.
                    </p>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
