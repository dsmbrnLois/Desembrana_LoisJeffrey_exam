<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Pencil, Trash2, Search, UserCog } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { User, PaginationLink } from '@/types';

const props = defineProps<{
    users: {
        data: User[];
        links: PaginationLink[];
        last_page: number;
    };
    filters: {
        search: string | null;
    };
}>();

const search = ref(props.filters.search || '');
const showForm = ref(false);
const editingUser = ref<User | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'guest',
});

function openCreate() {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
}

function openEdit(user: User) {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.role = user.role;
    form.clearErrors();
    showForm.value = true;
}

function submit() {
    if (editingUser.value) {
        form.put(`/admin/users/${editingUser.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/users', {
            preserveScroll: true,
            onSuccess: () => {
                showForm.value = false;
                form.reset();
            },
        });
    }
}

function deleteUser(user: User) {
    if (confirm(`Delete "${user.name}"? This action cannot be undone.`)) {
        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    }
}

function toggleStatus(user: User) {
    const action = user.status === 'active' ? 'deactivate' : 'activate';
    if (confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} "${user.name}"?`)) {
        router.patch(`/admin/users/${user.id}/toggle-status`, {}, { preserveScroll: true });
    }
}

let searchTimeout: ReturnType<typeof setTimeout>;
function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/admin/users', {
            search: search.value || undefined,
        }, { preserveState: true, preserveScroll: true });
    }, 400);
}

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}
</script>

<template>
    <Head title="Users Management" />

    <div>
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="rounded-xl bg-card border border-border/60 px-6 py-4 shadow-sm flex-1 mr-4">
                <h1 class="text-xl font-bold text-foreground">Users Management</h1>
            </div>
            <Button class="gap-1.5 shadow-sm" @click="openCreate">
                <Plus class="h-4 w-4" />
                Add User
            </Button>
        </div>

        <!-- Search -->
        <div class="mt-4 relative max-w-sm">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search users..." class="pl-10" @input="onSearch" />
        </div>

        <!-- Table -->
        <div class="mt-4 overflow-hidden rounded-xl border border-border/60 bg-card shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-primary text-primary-foreground">
                        <th class="px-5 py-3 text-left text-sm font-semibold">Full Name</th>
                        <th class="px-5 py-3 text-left text-sm font-semibold">Email</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Status</th>
                        <th class="px-5 py-3 text-center text-sm font-semibold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    <tr
                        v-for="user in users.data"
                        :key="user.id"
                        class="transition-colors hover:bg-muted/30"
                    >
                        <td class="px-5 py-3.5 text-sm font-medium text-foreground">{{ user.name }}</td>
                        <td class="px-5 py-3.5 text-sm text-foreground">{{ user.email }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <button
                                @click="toggleStatus(user)"
                                :class="[
                                    'inline-block cursor-pointer rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors hover:opacity-80',
                                    user.status === 'active'
                                        ? 'bg-green-100 text-green-700 border-green-200'
                                        : 'bg-red-100 text-red-700 border-red-200',
                                ]"
                            >
                                {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-accent hover:text-primary"
                                    @click="openEdit(user)"
                                    title="Edit"
                                >
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    class="rounded-lg p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                    @click="deleteUser(user)"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="4" class="px-5 py-12 text-center text-sm text-muted-foreground">
                            No users found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-4 flex items-center justify-center gap-1">
            <button
                v-for="link in users.links"
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

    <!-- User Form Modal -->
    <Dialog :open="showForm" @update:open="(val: boolean) => !val && (showForm = false)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <div class="flex items-center justify-between rounded-t-lg bg-primary px-4 py-3 -mx-6 -mt-6 mb-4">
                    <DialogTitle class="text-white font-semibold">
                        {{ editingUser ? 'Edit User' : 'Add User' }}
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
                    <div>
                        <Label for="name">Full Name</Label>
                        <Input id="name" v-model="form.name" class="mt-1" placeholder="Full name" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" class="mt-1" placeholder="email@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="password">Password {{ editingUser ? '(leave blank to keep current)' : '' }}</Label>
                        <PasswordInput id="password" v-model="form.password" name="password" class="mt-1" placeholder="Password" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div>
                        <Label for="password_confirmation">Confirm Password</Label>
                        <PasswordInput id="password_confirmation" v-model="form.password_confirmation" name="password_confirmation" class="mt-1" placeholder="Confirm password" />
                    </div>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
