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
}

defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-500',
        scheduled: 'bg-blue-500',
        processing: 'bg-yellow-500',
        completed: 'bg-green-500',
        failed: 'bg-red-500',
    };
    return colors[status] || 'bg-gray-500';
};

const deleteCampaign = (id: number) => {
    if (confirm('Are you sure you want to delete this campaign?')) {
        router.delete(`/campaigns/${id}`);
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
                                    {{ campaign.status }}
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
                                    <Link v-if="campaign.status === 'draft' || campaign.status === 'scheduled'" :href="`/campaigns/${campaign.id}/edit`">
                                        <Button variant="outline" size="sm">Edit</Button>
                                    </Link>
                                    <Button
                                        v-if="campaign.status === 'draft' || campaign.status === 'scheduled'"
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteCampaign(campaign.id)"
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
