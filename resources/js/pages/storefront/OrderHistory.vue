<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Package, Calendar } from 'lucide-vue-next';
import type { Order } from '@/types';

defineProps<{
    orders: {
        data: Order[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

function formatPrice(price: number): string {
    return `₱ ${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const statusColors: Record<string, string> = {
    delivered: 'bg-green-100 text-green-700 border-green-200',
    pending: 'bg-red-100 text-red-700 border-red-200',
    for_delivery: 'bg-yellow-100 text-yellow-700 border-yellow-200',
    canceled: 'bg-gray-100 text-gray-600 border-gray-200',
};

const statusLabels: Record<string, string> = {
    delivered: 'Delivered',
    pending: 'Pending',
    for_delivery: 'For Delivery',
    canceled: 'Canceled',
};
</script>

<template>
    <Head title="My Orders" />

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-foreground">My Orders</h1>
        <p class="mt-1 text-sm text-muted-foreground">Track the status of your orders</p>

        <div v-if="orders.data.length === 0" class="mt-12 flex flex-col items-center py-16">
            <Package class="h-16 w-16 text-muted-foreground/30" />
            <p class="mt-4 text-lg font-medium text-muted-foreground">No orders yet</p>
            <p class="text-sm text-muted-foreground/70">Start shopping to see your orders here</p>
        </div>

        <div v-else class="mt-6 space-y-4">
            <div
                v-for="order in orders.data"
                :key="order.id"
                class="overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm transition-all hover:shadow-md"
            >
                <!-- Order Header -->
                <div class="flex items-center justify-between border-b border-border/40 bg-muted/30 px-5 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-foreground">Order #{{ order.id }}</span>
                        <span
                            :class="[
                                'rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                statusColors[order.status] || 'bg-gray-100 text-gray-600',
                            ]"
                        >
                            {{ statusLabels[order.status] || order.status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Calendar class="h-3.5 w-3.5" />
                        {{ formatDate(order.created_at) }}
                    </div>
                </div>

                <!-- Order Items -->
                <div class="divide-y divide-border/30 px-5">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-center gap-4 py-3"
                    >
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-muted/50">
                            <img
                                v-if="item.product.image"
                                :src="`/storage/${item.product.image}`"
                                :alt="item.product.name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center">
                                <Package class="h-5 w-5 text-muted-foreground/30" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-foreground">{{ item.product?.name }}</p>
                            <p class="text-xs text-muted-foreground">Qty: {{ item.quantity }} × {{ formatPrice(item.price) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-foreground">
                            {{ formatPrice(item.quantity * item.price) }}
                        </p>
                    </div>
                </div>

                <!-- Total -->
                <div class="flex items-center justify-between border-t border-border/40 bg-primary/5 px-5 py-3">
                    <span class="text-sm font-semibold text-foreground">Total</span>
                    <span class="text-lg font-bold text-primary">{{ formatPrice(order.total) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
