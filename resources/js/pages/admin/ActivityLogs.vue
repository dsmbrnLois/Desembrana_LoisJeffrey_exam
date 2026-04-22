<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, Activity } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import type { PaginationLink } from '@/types';

type ActivityLogEntry = {
    id: number;
    user_id: number | null;
    action: string;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    properties: Record<string, any> | null;
    created_at: string;
    user?: { id: number; name: string; email: string } | null;
};

const props = defineProps<{
    logs: {
        data: ActivityLogEntry[];
        links: PaginationLink[];
        last_page: number;
    };
    filters: {
        search: string | null;
    };
}>();

const search = ref(props.filters.search || '');

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const actionColors: Record<string, string> = {
    login: 'bg-blue-100 text-blue-700',
    logout: 'bg-gray-100 text-gray-700',
    registered: 'bg-green-100 text-green-700',
    product_created: 'bg-purple-100 text-purple-700',
    product_updated: 'bg-yellow-100 text-yellow-700',
    product_deleted: 'bg-red-100 text-red-700',
    order_status_updated: 'bg-orange-100 text-orange-700',
    user_created: 'bg-emerald-100 text-emerald-700',
    user_updated: 'bg-teal-100 text-teal-700',
    user_deleted: 'bg-red-100 text-red-700',
    user_status_changed: 'bg-amber-100 text-amber-700',
    checkout: 'bg-indigo-100 text-indigo-700',
    cart_add: 'bg-violet-100 text-violet-700',
};

let searchTimeout: ReturnType<typeof setTimeout>;
function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/activity-logs', {
            search: search.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, 400);
}

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}
</script>

<template>
    <Head title="Activity Logs" />

    <div>
        <div class="rounded-xl bg-card border border-border/60 px-6 py-4 shadow-sm">
            <h1 class="text-xl font-bold text-foreground flex items-center gap-2">
                <Activity class="h-5 w-5 text-primary" />
                Activity Logs
            </h1>
        </div>

        <div class="mt-4 relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search logs..." class="pl-10" @input="onSearch" />
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="px-5 py-3 text-left text-sm font-semibold">User</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold">Action</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold">Description</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    <tr v-for="log in logs.data" :key="log.id" class="transition-colors hover:bg-muted/30">
                        <td class="px-5 py-3 text-sm font-medium text-foreground">
                            {{ log.user?.name || 'System' }}
                        </td>
                        <td class="px-5 py-3">
                            <span
                                :class="[
                                    'inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    actionColors[log.action] || 'bg-gray-100 text-gray-700',
                                ]"
                            >
                                {{ log.action.replaceAll('_', ' ') }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-muted-foreground">{{ log.description }}</td>
                        <td class="px-5 py-3 text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
                    </tr>
                    <tr v-if="logs.data.length === 0">
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-muted-foreground">
                            No activity logs found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="logs.last_page > 1" class="mt-4 flex items-center justify-center gap-1">
            <button
                v-for="link in logs.links"
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
</template>
