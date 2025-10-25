<script setup lang="ts">
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Search, UserPlus } from 'lucide-vue-next';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

interface Customer {
    id: number;
    email: string;
    first_name: string;
    last_name: string;
    sex?: string;
    birth_date?: string;
    groups: Array<{ id: number; name: string }>;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    customers: {
        data: Customer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLink[];
    };
    search?: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search || '');
const showDialog = ref(false);
const selectedCustomerId = ref<number | null>(null);
const isDeleting = ref(false);

const handleSearch = () => {
    router.get(
        '/customers',
        { search: searchQuery.value },
        { preserveState: true }
    );
};

const clearSearch = () => {
    searchQuery.value = '';
    router.get('/customers', {}, { preserveState: true });
};

const openDeleteDialog = (id: number) => {
    selectedCustomerId.value = id;
    showDialog.value = true;
};

const confirmDelete = () => {
    if (selectedCustomerId.value && !isDeleting.value) {
        isDeleting.value = true; // ✅ Set loading state

        router.delete(`/customers/${selectedCustomerId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                // ✅ Close dialog after successful deletion
                showDialog.value = false;
                selectedCustomerId.value = null;
                isDeleting.value = false;
            },
            onError: (errors) => {
                // ✅ Keep dialog open on error, show error
                console.error('Failed to delete customer:', errors);
                isDeleting.value = false;
                // Optionally show error message to user
            },
            onFinish: () => {
                isDeleting.value = false;
            }
        });
    }
};

const handleDialogUpdate = (open: boolean) => {
    if (!isDeleting.value) {
        showDialog.value = open;
        if (!open) {
            selectedCustomerId.value = null;
        }
    }
};
</script>

<template>
    <AppLayout title="Customers">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Customers</h1>
                <Link href="/customers/create">
                    <Button>
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Customer
                    </Button>
                </Link>
            </div>

            <div class="mb-4 flex gap-2">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search by name or email..."
                        class="pl-10"
                        @keyup.enter="handleSearch"
                    />
                </div>
                <Button @click="handleSearch">Search</Button>
                <Button
                    v-if="props.search"
                    variant="outline"
                    @click="clearSearch"
                >
                    Clear
                </Button>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Sex</TableHead>
                            <TableHead>Birth Date</TableHead>
                            <TableHead>Groups</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-if="customers.data.length === 0">
                            <TableCell colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center space-y-3">
                                    <UserPlus class="h-12 w-12 text-gray-300" />
                                    <div class="text-gray-500">
                                        <p class="font-medium" v-if="props.search">
                                            No customers found for "{{ props.search }}"
                                        </p>
                                        <p class="font-medium" v-else>
                                            No customers yet
                                        </p>
                                        <p class="text-sm text-gray-400 mt-1" v-if="!props.search">
                                            Get started by adding your first customer
                                        </p>
                                    </div>
                                    <Link href="/customers/create" v-if="!props.search">
                                        <Button>
                                            <UserPlus class="mr-2 h-4 w-4" />
                                            Add First Customer
                                        </Button>
                                    </Link>
                                </div>
                            </TableCell>
                        </TableRow>

                        <!-- Customer Rows -->
                        <TableRow v-for="customer in customers.data" :key="customer.id">
                            <TableCell class="font-medium">
                                {{ customer.first_name }} {{ customer.last_name }}
                            </TableCell>
                            <TableCell>{{ customer.email }}</TableCell>
                            <TableCell>
                                <span class="capitalize">{{ customer.sex || '-' }}</span>
                            </TableCell>
                            <TableCell>
                                {{ customer.birth_date ? new Date(customer.birth_date).toLocaleDateString() : '-' }}
                            </TableCell>
                            <TableCell>
                                <div class="flex gap-1 flex-wrap">
                                    <Badge
                                        v-for="group in customer.groups"
                                        :key="group.id"
                                        variant="secondary"
                                    >
                                        {{ group.name }}
                                    </Badge>
                                    <span v-if="customer.groups.length === 0" class="text-gray-400 text-sm">
                                        No groups
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <Link :href="`/customers/${customer.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="openDeleteDialog(customer.id)"
                                        :disabled="isDeleting && selectedCustomerId === customer.id"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div
                class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4"
                v-if="customers.data.length > 0"
            >
                <div class="text-sm text-gray-600">
                    Showing {{ customers.from || 0 }} to {{ customers.to || 0 }} of
                    {{ customers.total }} customer{{ customers.total !== 1 ? 's' : '' }}
                    <span v-if="props.search" class="font-medium">
                        for "{{ props.search }}"
                    </span>
                </div>
                <div class="flex gap-1 flex-wrap justify-center" v-if="customers.last_page > 1">
                    <Link
                        v-for="(link, index) in customers.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1 rounded border text-sm transition-colors',
                            link.active
                                ? 'bg-blue-500 text-white border-blue-500'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                            !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                        ]"
                        preserve-scroll
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <ConfirmDialog
            :open="showDialog"
            :loading="isDeleting"
            title="Delete Customer"
            description="Are you sure you want to delete this customer? This action cannot be undone and will remove them from all groups."
            confirmText="Delete Customer"
            cancelText="Cancel"
            variant="destructive"
            @confirm="confirmDelete"
            @update:open="handleDialogUpdate"
        />
    </AppLayout>
</template>
