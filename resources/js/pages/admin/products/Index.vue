<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Package, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { Product, PaginationLink } from '@/types';

const props = defineProps<{
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        links: PaginationLink[];
    };
    filters: {
        search: string | null;
    };
}>();

const search = ref(props.filters.search || '');
const showForm = ref(false);
const editingProduct = ref<Product | null>(null);
const imagePreview = ref<string | null>(null);

const form = useForm({
    name: '',
    description: '',
    price: '',
    stocks: '',
    image: null as File | null,
});

function formatPrice(price: number): string {
    return `₱ ${Number(price).toLocaleString('en-PH', { minimumFractionDigits: 2 })}`;
}

function openCreate() {
    editingProduct.value = null;
    form.reset();
    imagePreview.value = null;
    showForm.value = true;
}

function openEdit(product: Product) {
    editingProduct.value = product;
    form.name = product.name;
    form.description = product.description || '';
    form.price = String(product.price);
    form.stocks = String(product.stocks);
    form.image = null;
    imagePreview.value = product.image_url || null;
    showForm.value = true;
}

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    if (editingProduct.value) {
        // For updates, use POST with _method spoofing for file uploads
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('price', form.price);
        formData.append('stocks', form.stocks);
        if (form.image) {
            formData.append('image', form.image);
        }

        router.post(`/admin/products/${editingProduct.value.id}`, formData, {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    } else {
        const formData = new FormData();
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('price', form.price);
        formData.append('stocks', form.stocks);
        if (form.image) {
            formData.append('image', form.image);
        }

        router.post('/admin/products', formData, {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    }
}

function deleteProduct(product: Product) {
    if (confirm(`Delete "${product.name}"? This action cannot be undone.`)) {
        router.delete(`/admin/products/${product.id}`, { preserveScroll: true });
    }
}

let searchTimeout: ReturnType<typeof setTimeout>;
function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/products', {
            search: search.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, 400);
}

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}
</script>

<template>
    <Head title="Products Management" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="rounded-xl bg-card border border-border/60 px-6 py-4 shadow-sm flex-1 mr-4">
                <h1 class="text-xl font-bold text-foreground">Products Management</h1>
            </div>
            <Button class="gap-1.5 shadow-sm" @click="openCreate">
                <Plus class="h-4 w-4" />
                Add Product
            </Button>
        </div>

        <!-- Search -->
        <div class="mt-4 relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search products..." class="pl-10" @input="onSearch" />
        </div>

        <!-- Table -->
        <div class="mt-4 overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="px-5 py-3 text-left text-sm font-semibold">Product Name</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Price</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Number of Stocks</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-5 py-3.5 text-sm font-medium text-foreground">{{ product.name }}</td>
                        <td class="px-5 py-3.5 text-center text-sm text-foreground">{{ formatPrice(product.price) }}</td>
                        <td class="px-5 py-3.5 text-center text-sm text-foreground">{{ product.stocks }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-accent hover:text-primary"
                                    @click="openEdit(product)"
                                    title="Edit"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                    @click="deleteProduct(product)"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="products.data.length === 0">
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-muted-foreground">
                            No products found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="products.last_page > 1" class="mt-4 flex items-center justify-center gap-1">
            <button
                v-for="link in products.links"
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

    <!-- Product Form Modal -->
    <Dialog :open="showForm" @update:open="(val: boolean) => !val && (showForm = false)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <div class="flex items-center justify-between rounded-t-lg bg-primary px-4 py-3 -mx-6 -mt-6 mb-4">
                    <DialogTitle class="text-white font-semibold">
                        {{ editingProduct ? 'Edit Product' : 'Add Product' }}
                    </DialogTitle>
                    <div class="flex gap-2">
                        <Button size="sm" variant="outline" class="bg-background text-foreground hover:bg-accent hover:text-accent-foreground" @click="submit">
                            SAVE
                        </Button>
                        <Button size="sm" variant="outline" class="bg-background text-foreground hover:bg-accent hover:text-accent-foreground" @click="showForm = false">
                            CANCEL
                        </Button>
                    </div>
                </div>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <!-- Image upload -->
                    <div>
                        <Label>Product Image</Label>
                        <div class="mt-1.5 relative aspect-square w-full overflow-hidden rounded-xl border-2 border-dashed border-border bg-muted/30 cursor-pointer hover:border-primary/50 transition-colors"
                             @click="($refs.imageInput as HTMLInputElement)?.click()">
                            <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full flex-col items-center justify-center">
                                <Package class="h-10 w-10 text-muted-foreground/40" />
                                <span class="mt-2 text-xs text-muted-foreground">Click to upload</span>
                            </div>
                        </div>
                        <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="onImageChange" />
                    </div>

                    <!-- Fields -->
                    <div class="space-y-3">
                        <div>
                            <Label for="price">Price</Label>
                            <div class="relative mt-1">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">₱</span>
                                <Input id="price" v-model="form.price" type="number" step="0.01" min="0" class="pl-7" placeholder="0.00" />
                            </div>
                        </div>
                        <div>
                            <Label for="name">Product Name</Label>
                            <Input id="name" v-model="form.name" class="mt-1" placeholder="Enter product name" />
                        </div>
                        <div>
                            <Label for="stocks">Number of Stocks</Label>
                            <Input id="stocks" v-model="form.stocks" type="number" min="0" class="mt-1" placeholder="0" />
                        </div>
                    </div>
                </div>

                <div>
                    <Label for="description">Description (Optional)</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="2"
                        class="mt-1 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Brief product description..."
                    />
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
