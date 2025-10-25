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

interface EmailTemplate {
    id: number;
    name: string;
    subject: string;
    body: string;
}

interface Props {
    templates: EmailTemplate[];
    placeholders: Record<string, string>;
}

defineProps<Props>();

const showDialog = ref(false);
const selectedTemplateId = ref<number | null>(null);
const isDeleting = ref(false);

const openDeleteDialog = (id: number) => {
    selectedTemplateId.value = id;
    showDialog.value = true;
};

const confirmDelete = () => {
    if (selectedTemplateId.value && !isDeleting.value) {
        isDeleting.value = true;

        router.delete(`/email-templates/${selectedTemplateId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDialog.value = false;
                selectedTemplateId.value = null;
                isDeleting.value = false;
            },
            onError: (errors) => {
                console.error('Failed to delete template:', errors);
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
            selectedTemplateId.value = null;
        }
    }
};
</script>

<template>
    <AppLayout title="Email Templates">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Email Templates</h1>
                <Link href="/email-templates/create">
                    <Button>Create Template</Button>
                </Link>
            </div>

            <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold mb-2">Available Placeholders:</h3>
                <div class="flex flex-wrap gap-2">
                    <Badge v-for="(desc, placeholder) in placeholders" :key="placeholder" variant="secondary">
                        {{ placeholder }}
                    </Badge>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Subject</TableHead>
                            <TableHead>Preview</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="template in templates" :key="template.id">
                            <TableCell class="font-medium">{{ template.name }}</TableCell>
                            <TableCell>{{ template.subject }}</TableCell>
                            <TableCell>
                                <span class="text-sm text-gray-600 line-clamp-2">
                                    {{ template.body.substring(0, 100) }}...
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <Link :href="`/email-templates/${template.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="openDeleteDialog(template.id)"
                                        :disabled="isDeleting && selectedTemplateId === template.id"
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
            title="Delete Email Template"
            description="Are you sure you want to delete this email template? This action cannot be undone and may affect campaigns using this template."
            confirmText="Delete Template"
            cancelText="Cancel"
            variant="destructive"
            @confirm="confirmDelete"
            @update:open="handleDialogUpdate"
        />
    </AppLayout>
</template>
