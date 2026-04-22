<script setup lang="ts">
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Package, ShoppingCart, Users, LogOut, LayoutDashboard, Activity } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const currentPath = computed(() => {
    return page.url;
});

function isActive(path: string): boolean {
    return currentPath.value.startsWith(path);
}

function logout() {
    router.post('/logout');
}

const navItems = [
    { label: 'Dashboard', href: '/admin', icon: LayoutDashboard, match: '/admin' },
    { label: 'Products Management', href: '/admin/products', icon: Package, match: '/admin/products' },
    { label: 'Orders', href: '/admin/orders', icon: ShoppingCart, match: '/admin/orders' },
    { label: 'Users Management', href: '/admin/users', icon: Users, match: '/admin/users' },
    { label: 'Activity Logs', href: '/admin/activity-logs', icon: Activity, match: '/admin/activity-logs' },
];
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 flex w-56 flex-col bg-gradient-to-b from-[hsl(271,50%,42%)] to-[hsl(271,50%,32%)] text-white shadow-xl lg:w-60">
            <!-- Logo -->
            <div class="flex items-center gap-2 border-b border-white/10 px-5 py-5">
                <img src="/images/purplebug-logo.png" alt="PurpleBug Logo" class="h-8 w-auto object-contain" />
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                        isActive(item.match) && currentPath === item.href
                            ? 'bg-white/20 text-white shadow-sm'
                            : isActive(item.match) && item.match !== '/admin'
                            ? 'bg-white/20 text-white shadow-sm'
                            : 'text-white/70 hover:bg-white/10 hover:text-white',
                    ]"
                >
                    <component :is="item.icon" class="h-4.5 w-4.5" />
                    {{ item.label }}
                </Link>
            </nav>

            <!-- User info -->
            <div class="border-t border-white/10 px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                        <Users class="h-5 w-5 text-white" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-white">Hi, {{ user?.name }}!</p>
                        <p class="text-xs text-white/60">Administrator</p>
                    </div>
                    <button
                        class="rounded-lg p-2 text-white/60 transition-colors hover:bg-white/10 hover:text-white"
                        @click="logout"
                        title="Logout"
                    >
                        <LogOut class="h-4 w-4 text-red-300" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-56 flex-1 bg-background p-6 lg:ml-60 lg:p-8">
            <slot />
        </main>
    </div>
</template>
