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
    search: string;
}

const props = defineProps<Props>();

const searchQuery = ref(props.search);

const handleSearch = () => {
    router.get(
        '/customers',
        { search: searchQuery.value },
        { preserveState: true }
    );
};

const deleteCustomer = (id: number) => {
    if (confirm('Are you sure you want to delete this customer?')) {
        router.delete(`/customers/${id}`);
    }
};
</script>

<template>
    <AppLayout title="Customers">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Customers</h1>
                <Link href="/customers/create">
                    <Button>Add Customer</Button>
                </Link>
            </div>

            <div class="mb-4 flex gap-2">
                <Input
                    v-model="searchQuery"
                    placeholder="Search customers..."
                    class="max-w-sm"
                    @keyup.enter="handleSearch"
                />
                <Button @click="handleSearch">Search</Button>
            </div>

            <div class="bg-white rounded-lg shadow">
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
                            <TableCell colspan="6" class="text-center text-gray-500 py-8">
                                No customers found.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="customer in customers.data" :key="customer.id">
                            <TableCell>
                                {{ customer.first_name }} {{ customer.last_name }}
                            </TableCell>
                            <TableCell>{{ customer.email }}</TableCell>
                            <TableCell>{{ customer.sex || '-' }}</TableCell>
                            <TableCell>{{ customer.birth_date || '-' }}</TableCell>
                            <TableCell>
                                <div class="flex gap-1 flex-wrap">
                                    <Badge
                                        v-for="group in customer.groups"
                                        :key="group.id"
                                        variant="secondary"
                                    >
                                        {{ group.name }}
                                    </Badge>
                                    <span v-if="customer.groups.length === 0" class="text-gray-400">-</span>
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
                                        @click="deleteCustomer(customer.id)"
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
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Showing {{ customers.from || 0 }} to {{ customers.to || 0 }} of
                    {{ customers.total }} customers
                </div>
                <div class="flex gap-1" v-if="customers.last_page > 1">
                    <Link
                        v-for="link in customers.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-1 rounded border text-sm',
                            link.active ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                        :preserve-state="true"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
