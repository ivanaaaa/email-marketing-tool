<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
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

interface Group {
    id: number;
    name: string;
    description?: string;
    customers_count: number;
}

interface Props {
    groups: Group[];
}

defineProps<Props>();

const deleteGroup = (id: number) => {
    if (confirm('Are you sure you want to delete this group?')) {
        router.delete(`/groups/${id}`);
    }
};
</script>

<template>
    <AppLayout title="Groups">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Groups</h1>
                <Link href="/groups/create">
                    <Button>Create Group</Button>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>Customers</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="group in groups" :key="group.id">
                            <TableCell class="font-medium">{{ group.name }}</TableCell>
                            <TableCell>{{ group.description || '-' }}</TableCell>
                            <TableCell>
                                <Badge variant="secondary">
                                    {{ group.customers_count }} customers
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <Link :href="`/groups/${group.id}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                    <Link :href="`/groups/${group.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteGroup(group.id)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
