<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

interface Campaign {
    id: number;
    name: string;
    status: string;
    scheduled_at?: string;
    sent_at?: string;
    email_template: {
        id: number;
        name: string;
    };
    groups: Array<{ id: number; name: string }>;
}

interface Statistics {
    total_recipients: number;
    sent_count: number;
    failed_count: number;
    pending_count: number;
    progress_percentage: number;
    status: string;
}

interface Props {
    campaign: Campaign;
    statistics: Statistics;
}

const props = defineProps<Props>();

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

const sendNow = () => {
    if (confirm('Are you sure you want to send this campaign now?')) {
        router.post(`/campaigns/${props.campaign.id}/send-now`, {});
    }
};
</script>

<template>
    <AppLayout :title="`Campaign: ${campaign.name}`">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold">{{ campaign.name }}</h1>
                    <Badge :class="getStatusColor(campaign.status)" class="mt-2">
                        {{ campaign.status }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Link v-if="campaign.status === 'draft'" :href="`/campaigns/${campaign.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button
                        v-if="campaign.status === 'draft'"
                        @click="sendNow"
                    >
                        Send Now
                    </Button>
                    <Link href="/campaigns">
                        <Button variant="outline">Back to Campaigns</Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Campaign Details</h3>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm text-gray-600">Template</dt>
                            <dd class="font-medium">{{ campaign.email_template.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600">Target Groups</dt>
                            <dd>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <Badge v-for="group in campaign.groups" :key="group.id" variant="secondary">
                                        {{ group.name }}
                                    </Badge>
                                </div>
                            </dd>
                        </div>
                        <div v-if="campaign.scheduled_at">
                            <dt class="text-sm text-gray-600">Scheduled At</dt>
                            <dd class="font-medium">{{ new Date(campaign.scheduled_at).toLocaleString() }}</dd>
                        </div>
                        <div v-if="campaign.sent_at">
                            <dt class="text-sm text-gray-600">Sent At</dt>
                            <dd class="font-medium">{{ new Date(campaign.sent_at).toLocaleString() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="font-semibold mb-4">Statistics</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm text-gray-600">Total Recipients</dt>
                            <dd class="text-2xl font-bold">{{ statistics.total_recipients }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 mb-2">Progress</dt>
                            <Progress :value="statistics.progress_percentage" class="mb-2" />
                            <dd class="text-sm">{{ statistics.progress_percentage }}% complete</dd>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <dt class="text-sm text-gray-600">Sent</dt>
                                <dd class="text-lg font-semibold text-green-600">{{ statistics.sent_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Failed</dt>
                                <dd class="text-lg font-semibold text-red-600">{{ statistics.failed_count }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-600">Pending</dt>
                                <dd class="text-lg font-semibold text-gray-600">{{ statistics.pending_count }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
