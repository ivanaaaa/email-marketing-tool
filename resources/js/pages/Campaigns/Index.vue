<!-- resources/js/Pages/Campaigns/Index.vue -->
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

interface StatusOption {
    value: string;
    label: string;
    color: string;
}

interface Campaign {
    id: number;
    name: string;
    status: string;
    scheduled_at?: string;
    total_recipients: number;
    sent_count: number;
    failed_count: number;
    email_template: {
        name: string;
    };
    groups: Array<{ name: string }>;
}

interface Props {
    campaigns: Campaign[];
    statusOptions: StatusOption[];
}

const props = defineProps<Props>();

const showDialog = ref(false);
const selectedCampaignId = ref<number | null>(null);
const isDeleting = ref(false);

const getStatusColor = (status: string): string => {
    const option = props.statusOptions.find(opt => opt.value === status);
    return option?.color || 'bg-gray-500';
};

const getStatusLabel = (status: string): string => {
    const option = props.statusOptions.find(opt => opt.value === status);
    return option?.label || status;
};

// Helper functions that match backend enum logic
const canEdit = (campaign: Campaign) => {
    return ['draft', 'scheduled'].includes(campaign.status);
};

const canDelete = (campaign: Campaign) => {
    return campaign.status === 'draft';
};

const openDeleteDialog = (id: number) => {
    selectedCampaignId.value = id;
    showDialog.value = true;
};

const confirmDelete = () => {
    if (selectedCampaignId.value && !isDeleting.value) {
        isDeleting.value = true;

        router.delete(`/campaigns/${selectedCampaignId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDialog.value = false;
                selectedCampaignId.value = null;
                isDeleting.value = false;
            },
            onError: (errors) => {
                console.error('Failed to delete campaign:', errors);
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
            selectedCampaignId.value = null;
        }
    }
};
</script>

<template>
    <AppLayout title="Campaigns">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Campaigns</h1>
                <Link href="/campaigns/create">
                    <Button>Create Campaign</Button>
                </Link>
            </div>

            <div class="bg-white rounded-lg shadow">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Template</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Recipients</TableHead>
                            <TableHead>Progress</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="campaign in campaigns" :key="campaign.id">
                            <TableCell class="font-medium">{{ campaign.name }}</TableCell>
                            <TableCell>{{ campaign.email_template.name }}</TableCell>
                            <TableCell>
                                <Badge :class="getStatusColor(campaign.status)">
                                    {{ getStatusLabel(campaign.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell>{{ campaign.total_recipients }}</TableCell>
                            <TableCell>
                                <div class="space-y-1">
                                    <div class="text-sm">
                                        Sent: {{ campaign.sent_count }} / Failed: {{ campaign.failed_count }}
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <Link :href="`/campaigns/${campaign.id}`">
                                        <Button variant="outline" size="sm">View</Button>
                                    </Link>
                                    <Link v-if="canEdit(campaign)" :href="`/campaigns/${campaign.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        v-if="canDelete(campaign)"
                                        variant="destructive"
                                        size="sm"
                                        @click="openDeleteDialog(campaign.id)"
                                        :disabled="isDeleting && selectedCampaignId === campaign.id"
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

        <ConfirmDialog
            :open="showDialog"
            :loading="isDeleting"
            title="Delete Campaign"
            description="Are you sure you want to delete this campaign? This action cannot be undone."
            confirmText="Delete Campaign"
            cancelText="Cancel"
            variant="destructive"
            @confirm="confirmDelete"
            @update:open="handleDialogUpdate"
        />
    </AppLayout>
</template>
