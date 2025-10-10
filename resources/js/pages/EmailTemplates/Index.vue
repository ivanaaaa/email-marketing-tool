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

const deleteTemplate = (id: number) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(`/email-templates/${id}`);
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
                                        @click="deleteTemplate(template.id)"
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
