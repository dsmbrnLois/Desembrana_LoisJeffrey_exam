<script setup lang="ts">
import type { Product } from '@/types';
import { Package } from 'lucide-vue-next';

defineProps<{
    product: Product;
}>();

defineEmits<{
    click: [];
}>();

function formatPrice(price: number): string {
    return `₱ ${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}
</script>

<template>
    <div
        class="group cursor-pointer overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5"
        @click="$emit('click')"
    >
        <!-- Image -->
        <div class="relative aspect-square overflow-hidden bg-muted/50">
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

            <!-- Out of stock overlay -->
            <div
                v-if="product.stocks <= 0"
                class="absolute inset-0 flex items-center justify-center bg-black/50"
            >
                <span class="rounded-full bg-destructive px-4 py-1.5 text-sm font-semibold text-white">
                    Out of Stock
                </span>
            </div>
        </div>

        <!-- Info -->
        <div class="p-4">
            <h3 class="text-sm font-semibold text-primary transition-colors group-hover:text-primary/80">
                {{ product.name }}
            </h3>
            <p class="mt-1 text-base font-bold text-foreground">
                {{ formatPrice(product.price) }}
            </p>
        </div>
    </div>
</template>
