<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Eye, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Order, OrderStatus, PaginationLink } from '@/types';

const props = defineProps<{
    orders: {
        data: Order[];
        links: PaginationLink[];
        last_page: number;
    };
    filters: {
        search: string | null;
        status: string | null;
    };
    statuses: string[];
}>();

const search = ref(props.filters.search || '');
const showDetail = ref(false);
const selectedOrder = ref<Order | null>(null);
const editStatus = ref('');

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

function openDetail(order: Order) {
    selectedOrder.value = order;
    editStatus.value = order.status;
    showDetail.value = true;
}

function saveStatus() {
    if (!selectedOrder.value) return;
    router.patch(`/admin/orders/${selectedOrder.value.id}/status`, {
        status: editStatus.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showDetail.value = false;
        },
    });
}

function deleteOrder(order: Order) {
    if (confirm(`Delete order #${order.id}? This action cannot be undone.`)) {
        router.delete(`/admin/orders/${order.id}`, { preserveScroll: true });
    }
}

let searchTimeout: ReturnType<typeof setTimeout>;
function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/orders', {
            search: search.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, 400);
}

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}
</script>

<template>
    <Head title="Orders Management" />

    <div>
        <!-- Header -->
        <div class="rounded-xl bg-card border border-border/60 px-6 py-4 shadow-sm">
            <h1 class="text-xl font-bold text-foreground">Orders</h1>
        </div>

        <!-- Search -->
        <div class="mt-4 relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search by customer name..." class="pl-10" @input="onSearch" />
        </div>

        <!-- Table -->
        <div class="mt-4 overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="px-5 py-3 text-left text-sm font-semibold">Full Name</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold">Product</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Status</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    <tr
                        v-for="order in orders.data"
                        :key="order.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-5 py-3.5 text-sm font-medium text-foreground">{{ order.user?.name }}</td>
                        <td class="px-5 py-3.5 text-sm text-foreground">
                            {{ order.items?.map(i => i.product?.name).join(', ') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span
                                :class="[
                                    'inline-block rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                    statusColors[order.status] || 'bg-gray-100 text-gray-600',
                                ]"
                            >
                                {{ statusLabels[order.status] || order.status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-accent hover:text-primary"
                                    @click="openDetail(order)"
                                    title="View"
                                >
                                    <Eye class="h-4 w-4" />
                                </button>
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                    @click="deleteOrder(order)"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="orders.data.length === 0">
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-muted-foreground">
                            No orders found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="orders.last_page > 1" class="mt-4 flex items-center justify-center gap-1">
            <button
                v-for="link in orders.links"
                :key="link.label"
                :disabled="!link.url || link.active"
                :class="[
                    'rounded-lg px-3 py-1.5 text-sm',
                    link.active ? 'bg-primary text-white' : link.url ? 'text-muted-foreground hover:bg-accent' : 'text-muted-foreground/40',
                ]"
                @click="goToPage(link.url)"
                v-html="link.label"
            />
        </div>
    </div>

    <!-- Order Detail Modal -->
    <Dialog :open="showDetail" @update:open="(val: boolean) => !val && (showDetail = false)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <div class="flex items-center justify-between rounded-t-lg bg-primary px-4 py-3 -mx-6 -mt-6 mb-4">
                    <DialogTitle class="text-white font-semibold uppercase">
                        {{ selectedOrder?.user?.name }}
                    </DialogTitle>
                    <div class="flex gap-2">
                        <Button size="sm" variant="outline" class="bg-background text-foreground hover:bg-accent hover:text-accent-foreground" @click="saveStatus">
                            SAVE
                        </Button>
                        <Button size="sm" variant="outline" class="bg-background text-foreground hover:bg-accent hover:text-accent-foreground" @click="showDetail = false">
                            CLOSE
                        </Button>
                    </div>
                </div>
            </DialogHeader>

            <div v-if="selectedOrder" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-muted-foreground text-xs">Full Name</Label>
                        <Input :model-value="selectedOrder.user?.name" disabled class="mt-1 bg-muted/50" />
                    </div>
                    <div>
                        <Label class="text-muted-foreground text-xs">Product Ordered</Label>
                        <Input :model-value="selectedOrder.items?.map(i => i.product?.name).join(', ')" disabled class="mt-1 bg-muted/50" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label class="text-muted-foreground text-xs">Status</Label>
                        <select
                            v-model="editStatus"
                            class="mt-1 block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        >
                            <option v-for="status in statuses" :key="status" :value="status">
                                {{ statusLabels[status] || status }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label class="text-muted-foreground text-xs">Quantity</Label>
                        <Input :model-value="String(selectedOrder.items?.reduce((s: number, i: any) => s + i.quantity, 0))" disabled class="mt-1 bg-muted/50" />
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
