<script setup lang="ts">
import { ShoppingCart } from 'lucide-vue-next';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-background px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="mb-8 flex flex-col items-center">
                <div class="flex items-center gap-2">
                    <img src="/images/purplebug-logo.png" alt="PurpleBug Logo" class="h-10 w-auto object-contain" />
                </div>
            </div>

            <!-- Card -->
            <div class="rounded-2xl border border-border/60 bg-card p-8 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
