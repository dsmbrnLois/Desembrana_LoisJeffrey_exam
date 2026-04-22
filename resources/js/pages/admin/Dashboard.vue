<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Package, ShoppingCart, Users, DollarSign, Clock } from 'lucide-vue-next';

defineProps<{
    stats: {
        totalProducts: number;
        totalOrders: number;
        totalUsers: number;
        totalRevenue: number;
        pendingOrders: number;
    };
}>();

function formatPrice(price: number): string {
    return `₱ ${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

const cards = [
    { key: 'totalProducts', label: 'Total Products', icon: Package, color: 'bg-purple-50 text-purple-600 border-purple-100' },
    { key: 'totalOrders', label: 'Total Orders', icon: ShoppingCart, color: 'bg-blue-50 text-blue-600 border-blue-100' },
    { key: 'totalUsers', label: 'Registered Users', icon: Users, color: 'bg-emerald-50 text-emerald-600 border-emerald-100' },
    { key: 'pendingOrders', label: 'Pending Orders', icon: Clock, color: 'bg-amber-50 text-amber-600 border-amber-100' },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <div>
        <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
        <p class="mt-1 text-sm text-muted-foreground">Overview of your store</p>

        <!-- Stats Grid -->
        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="card in cards"
                :key="card.key"
                class="overflow-hidden rounded-xl border bg-card p-5 shadow-sm transition-all duration-200 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">{{ card.label }}</p>
                        <p class="mt-2 text-3xl font-bold text-foreground">
                            {{ (stats as any)[card.key] }}
                        </p>
                    </div>
                    <div :class="['flex h-12 w-12 items-center justify-center rounded-xl border', card.color]">
                        <component :is="card.icon" class="h-6 w-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="mt-5 overflow-hidden rounded-xl border bg-gradient-to-r from-primary to-primary/80 p-6 text-white shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-white/80">Total Revenue (Delivered)</p>
                    <p class="mt-2 text-4xl font-bold">{{ formatPrice(stats.totalRevenue) }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20">
                    <DollarSign class="h-7 w-7" />
                </div>
            </div>
        </div>
    </div>
</template>
