<script setup lang="ts">
import { ref } from 'vue';
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
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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

const showDialog = ref(false);
const selectedGroupId = ref<number | null>(null);
const isDeleting = ref(false);

const openDeleteDialog = (id: number) => {
    selectedGroupId.value = id;
    showDialog.value = true;
};

const confirmDelete = () => {
    if (selectedGroupId.value && !isDeleting.value) {
        isDeleting.value = true;

        router.delete(`/groups/${selectedGroupId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDialog.value = false;
                selectedGroupId.value = null;
                isDeleting.value = false;
            },
            onError: (errors) => {
                console.error('Failed to delete group:', errors);
                isDeleting.value = false;
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
            selectedGroupId.value = null;
        }
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
                                        @click="openDeleteDialog(group.id)"
                                        :disabled="isDeleting && selectedGroupId === group.id"
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

        <!-- Confirm Delete Dialog -->
        <ConfirmDialog
            :open="showDialog"
            :loading="isDeleting"
            title="Delete Group"
            description="Are you sure you want to delete this group? This will remove the group but will not delete the customers in it."
            confirmText="Delete Group"
            cancelText="Cancel"
            variant="destructive"
            @confirm="confirmDelete"
            @update:open="handleDialogUpdate"
        />
    </AppLayout>
</template>
