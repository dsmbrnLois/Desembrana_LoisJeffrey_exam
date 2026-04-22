import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { CartItem, CartData } from '@/types/cart';
import axios from 'axios';

export const useCartStore = defineStore('cart', () => {
    const items = ref<CartItem[]>([]);
    const total = ref(0);
    const loading = ref(false);
    const showCart = ref(false);
    const showThankYou = ref(false);

    // Getters
    const cartCount = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));
    const isEmpty = computed(() => items.value.length === 0);

    // Actions
    async function fetchCart() {
        try {
            loading.value = true;
            const { data } = await axios.get<CartData>('/api/cart');
            items.value = data.items;
            total.value = data.total;
        } catch {
            // Not logged in or error — reset cart
            items.value = [];
            total.value = 0;
        } finally {
            loading.value = false;
        }
    }

    async function addToCart(productId: number, quantity: number = 1) {
        try {
            loading.value = true;
            await axios.post('/api/cart', { product_id: productId, quantity });
            await fetchCart();
            return { success: true, message: 'Product added to cart.' };
        } catch (error: any) {
            const message = error.response?.data?.message || 'Failed to add product to cart.';
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    async function updateQuantity(cartItemId: number, quantity: number) {
        try {
            await axios.put(`/api/cart/${cartItemId}`, { quantity });
            await fetchCart();
            return { success: true };
        } catch (error: any) {
            const message = error.response?.data?.message || 'Failed to update quantity.';
            return { success: false, message };
        }
    }

    async function removeItem(cartItemId: number) {
        try {
            await axios.delete(`/api/cart/${cartItemId}`);
            await fetchCart();
        } catch {
            // Silently ignore
        }
    }

    async function checkout() {
        try {
            loading.value = true;
            const { data } = await axios.post('/api/checkout');
            items.value = [];
            total.value = 0;
            showCart.value = false;
            showThankYou.value = true;
            return { success: true, order: data.order };
        } catch (error: any) {
            const message = error.response?.data?.message || 'Checkout failed.';
            return { success: false, message };
        } finally {
            loading.value = false;
        }
    }

    function toggleCart() {
        showCart.value = !showCart.value;
    }

    function closeThankYou() {
        showThankYou.value = false;
    }

    return {
        items,
        total,
        loading,
        showCart,
        showThankYou,
        cartCount,
        isEmpty,
        fetchCart,
        addToCart,
        updateQuantity,
        removeItem,
        checkout,
        toggleCart,
        closeThankYou,
    };
});
