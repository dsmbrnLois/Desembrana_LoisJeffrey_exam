<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Search, ArrowUpDown } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import ProductCard from '@/components/storefront/ProductCard.vue';
import ProductDetailModal from '@/components/storefront/ProductDetailModal.vue';
import type { Product, PaginationLink } from '@/types';

const props = defineProps<{
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        total: number;
        links: PaginationLink[];
    };
    filters: {
        search: string | null;
        sort: string;
    };
}>();

const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');
const selectedProduct = ref<Product | null>(null);
const showProductModal = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    router.get('/', {
        search: search.value || undefined,
        sort: sort.value !== 'latest' ? sort.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
}

function setSort(newSort: string) {
    sort.value = newSort;
    applyFilters();
}

function openProduct(product: Product) {
    selectedProduct.value = product;
    showProductModal.value = true;
}

function goToPage(url: string | null) {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Shop" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Search & Sort Controls -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full max-w-sm">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search products..."
                    class="pl-10"
                    @input="onSearchInput"
                />
            </div>

            <div class="flex gap-2">
                <Button
                    :variant="sort === 'price_asc' ? 'default' : 'outline'"
                    size="sm"
                    class="gap-1.5 text-xs"
                    @click="setSort('price_asc')"
                >
                    <ArrowUpDown class="h-3.5 w-3.5" />
                    Price ascending
                </Button>
                <Button
                    :variant="sort === 'price_desc' ? 'default' : 'outline'"
                    size="sm"
                    class="gap-1.5 text-xs"
                    @click="setSort('price_desc')"
                >
                    <ArrowUpDown class="h-3.5 w-3.5" />
                    Price descending
                </Button>
            </div>
        </div>

        <!-- Product Grid -->
        <div
            v-if="products.data.length > 0"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
        >
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
                @click="openProduct(product)"
            />
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border py-20"
        >
            <Search class="h-12 w-12 text-muted-foreground/50" />
            <p class="mt-4 text-lg font-medium text-muted-foreground">No products found</p>
            <p class="text-sm text-muted-foreground/70">Try adjusting your search or filters</p>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="mt-8 flex items-center justify-center gap-1">
            <button
                v-for="link in products.links"
                :key="link.label"
                :disabled="!link.url || link.active"
                :class="[
                    'rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200',
                    link.active
                        ? 'bg-primary text-primary-foreground shadow-sm'
                        : link.url
                        ? 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                        : 'cursor-not-allowed text-muted-foreground/40',
                ]"
                @click="goToPage(link.url)"
                v-html="link.label"
            />
        </div>
    </div>

    <!-- Product Detail Modal -->
    <ProductDetailModal
        :product="selectedProduct"
        :open="showProductModal"
        @close="showProductModal = false"
    />
</template>
