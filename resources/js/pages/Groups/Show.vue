<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
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
}

interface Group {
    id: number;
    name: string;
    description?: string;
    customers_count: number;
}

interface Props {
    group: Group;
    customers: {
        data: Customer[];
    };
}

defineProps<Props>();
</script>

<template>
    <AppLayout :title="`Group: ${group.name}`">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold">{{ group.name }}</h1>
                    <p v-if="group.description" class="text-gray-600 mt-2">
                        {{ group.description }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/groups/${group.id}/edit`">
                        <Button variant="outline">Edit Group</Button>
                    </Link>
                    <Link href="/groups">
                        <Button variant="outline">Back to Groups</Button>
                    </Link>
                </div>
            </div>

            <div class="mb-4">
                <Badge variant="secondary" class="text-lg">
                    {{ group.customers_count }} customers in this group
                </Badge>
            </div>

            <div class="bg-white rounded-lg shadow">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="customer in customers.data" :key="customer.id">
                            <TableCell>
                                {{ customer.first_name }} {{ customer.last_name }}
                            </TableCell>
                            <TableCell>{{ customer.email }}</TableCell>
                            <TableCell class="text-right">
                                <Link :href="`/customers/${customer.id}/edit`">
                                    <Button variant="outline" size="sm">View Customer</Button>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
