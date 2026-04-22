<script setup lang="ts">
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { ShoppingCart, LogIn, UserPlus, LogOut, Package, Menu, X } from 'lucide-vue-next';
import { useCartStore } from '@/stores/cart';
import CartSlideOver from '@/components/storefront/CartSlideOver.vue';
import ThankYouModal from '@/components/storefront/ThankYouModal.vue';
import { Button } from '@/components/ui/button';

const page = usePage();
const cart = useCartStore();
const mobileMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user);
const isLoggedIn = computed(() => !!user.value);

onMounted(() => {
    if (isLoggedIn.value) {
        cart.fetchCart();
    }
});

function logout() {
    router.post('/logout');
}
</script>

<template>
    <!-- Navbar -->
    <header class="sticky top-0 z-40 border-b border-border/50 bg-background/95 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2">
                <img src="/images/purplebug-logo.png" alt="PurpleBug Logo" class="h-8 w-auto object-contain" />
            </Link>

            <!-- Desktop Navigation -->
            <div class="hidden items-center gap-4 md:flex">
                <!-- Greeting -->
                <span class="text-sm text-muted-foreground">
                    Hi, {{ isLoggedIn ? user?.name : 'Guest' }}!
                </span>

                <!-- Orders link (authenticated only) -->
                <Link
                    v-if="isLoggedIn && user?.role === 'guest'"
                    href="/orders"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                >
                    <Package class="mr-1 inline h-4 w-4" />
                    My Orders
                </Link>

                <!-- Cart button -->
                <button
                    v-if="isLoggedIn"
                    class="relative rounded-full p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-primary"
                    @click="cart.toggleCart()"
                    aria-label="Shopping cart"
                >
                    <ShoppingCart class="h-5 w-5" />
                    <span
                        v-if="cart.cartCount > 0"
                        class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground"
                    >
                        {{ cart.cartCount > 99 ? '99+' : cart.cartCount }}
                    </span>
                </button>

                <!-- Auth buttons -->
                <template v-if="!isLoggedIn">
                    <Link href="/login">
                        <Button variant="outline" size="sm" class="gap-1.5 border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                            <LogIn class="h-4 w-4" />
                            LOGIN
                        </Button>
                    </Link>
                    <Link href="/register">
                        <Button size="sm" class="gap-1.5 bg-primary hover:bg-primary/90">
                            <UserPlus class="h-4 w-4" />
                            SIGN UP
                        </Button>
                    </Link>
                </template>

                <Button
                    v-else
                    variant="ghost"
                    size="sm"
                    class="gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    @click="logout"
                >
                    <LogOut class="h-4 w-4" />
                    Logout
                </Button>
            </div>

            <!-- Mobile menu toggle -->
            <button class="md:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
                <Menu v-if="!mobileMenuOpen" class="h-6 w-6 text-muted-foreground" />
                <X v-else class="h-6 w-6 text-muted-foreground" />
            </button>
        </div>

        <!-- Mobile Menu -->
        <div
            v-if="mobileMenuOpen"
            class="border-t border-border/50 bg-background px-4 py-4 md:hidden"
        >
            <div class="flex flex-col gap-3">
                <span class="text-sm text-muted-foreground">
                    Hi, {{ isLoggedIn ? user?.name : 'Guest' }}!
                </span>

                <Link
                    v-if="isLoggedIn && user?.role === 'guest'"
                    href="/orders"
                    class="text-sm font-medium text-muted-foreground"
                >
                    My Orders
                </Link>

                <button
                    v-if="isLoggedIn"
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                    @click="cart.toggleCart(); mobileMenuOpen = false"
                >
                    <ShoppingCart class="h-4 w-4" />
                    Cart ({{ cart.cartCount }})
                </button>

                <template v-if="!isLoggedIn">
                    <Link href="/login" class="text-sm font-medium text-primary">Login</Link>
                    <Link href="/register" class="text-sm font-medium text-primary">Sign Up</Link>
                </template>
                <button v-else class="text-left text-sm text-destructive" @click="logout">Logout</button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-[calc(100vh-8rem)]">
        <slot />
    </main>

    <!-- Footer -->
    <footer class="border-t border-border/50 bg-background py-8">
        <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-2">
                <img src="/images/purplebug-logo.png" alt="PurpleBug Logo" class="h-8 w-auto object-contain" />
            </div>
            <p class="mt-2 text-sm text-muted-foreground">
                Copyright {{ new Date().getFullYear() }} PurpleBug Inc.
            </p>
        </div>
    </footer>

    <!-- Cart Slide Over -->
    <CartSlideOver />

    <!-- Thank You Modal -->
    <ThankYouModal />
</template>
