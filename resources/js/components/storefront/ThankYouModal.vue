<script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { X } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
} from '@/components/ui/dialog';

const cart = useCartStore();
</script>

<template>
    <Dialog :open="cart.showThankYou" @update:open="(val: boolean) => !val && cart.closeThankYou()">
        <DialogContent class="sm:max-w-md text-center [&>button]:hidden">
            <div class="flex flex-col items-center py-6">
                
                <div class="flex items-center gap-2">
                    <img src="/images/purplebug-logo.png" alt="PurpleBug Logo" class="h-8 w-auto object-contain" />
                </div>

                <div class="my-8 flex items-center justify-center">
                    <img 
                        src="/images/pb-thank-you.png" 
                        alt="Thank You from PurpleBug" 
                        class="h-70 w-auto object-contain drop-shadow-md" 
                    />
                </div>
                <p class="mt-2 text-sm text-muted-foreground">
                    Your order has been placed successfully!
                </p>

                <button
                    class="mt-6 flex h-8 w-8 items-center justify-center rounded-full border border-border text-muted-foreground transition-colors hover:bg-accent"
                    @click="cart.closeThankYou()"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>