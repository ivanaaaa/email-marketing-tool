<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, Mail, Send, TrendingUp, FolderOpen, Calendar } from 'lucide-vue-next';

interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    created_at: string;
}

interface Campaign {
    id: number;
    name: string;
    status: string;
    sent_count: number;
    total_recipients: number;
    created_at: string;
    email_template: {
        id: number;
        name: string;
    };
}

interface Props {
    stats: {
        totalCustomers: number;
        totalGroups: number;
        totalTemplates: number;
        totalCampaigns: number;
        newCustomersThisMonth: number;
    };
    recentCustomers: Customer[];
    recentCampaigns: Campaign[];
    campaignStats: {
        draft: number;
        scheduled: number;
        processing: number;
        completed: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'draft': return 'bg-gray-100 text-gray-800';
        case 'scheduled': return 'bg-blue-100 text-blue-800';
        case 'processing': return 'bg-yellow-100 text-yellow-800';
        case 'completed': return 'bg-green-100 text-green-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Overview of your email marketing system
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Customers</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalCustomers }}</div>
                        <p class="text-xs text-muted-foreground">
                            +{{ stats.newCustomersThisMonth }} this month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Groups</CardTitle>
                        <FolderOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalGroups }}</div>
                        <p class="text-xs text-muted-foreground">
                            Customer segments
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Email Templates</CardTitle>
                        <Mail class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalTemplates }}</div>
                        <p class="text-xs text-muted-foreground">
                            Ready to use
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Campaigns</CardTitle>
                        <Send class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalCampaigns }}</div>
                        <p class="text-xs text-muted-foreground">
                            Total campaigns
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Campaign Status Overview -->
                <Card class="col-span-full md:col-span-1">
                    <CardHeader>
                        <CardTitle>Campaign Status</CardTitle>
                        <CardDescription>
                            Current status of your campaigns
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Draft</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">{{ campaignStats.draft }}</span>
                                <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Scheduled</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">{{ campaignStats.scheduled }}</span>
                                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Processing</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">{{ campaignStats.processing }}</span>
                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Completed</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">{{ campaignStats.completed }}</span>
                                <div class="w-2 h-2 rounded-full bg-green-400"></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Customers -->
                <Card class="col-span-full md:col-span-1">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Customers</CardTitle>
                            <CardDescription>
                                Latest customer additions
                            </CardDescription>
                        </div>
                        <Link href="/customers">
                            <Button variant="outline" size="sm">View All</Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-if="recentCustomers.length === 0" class="text-sm text-muted-foreground py-4 text-center">
                                No customers yet
                            </div>
                            <div v-for="customer in recentCustomers" :key="customer.id" class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium">
                                        {{ customer.first_name }} {{ customer.last_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ customer.email }}
                                    </p>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    {{ formatDate(customer.created_at) }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Campaigns -->
                <Card class="col-span-full md:col-span-1">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle>Recent Campaigns</CardTitle>
                            <CardDescription>
                                Latest campaign activity
                            </CardDescription>
                        </div>
                        <Link href="/campaigns">
                            <Button variant="outline" size="sm">View All</Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-if="recentCampaigns.length === 0" class="text-sm text-muted-foreground py-4 text-center">
                                No campaigns yet
                            </div>
                            <div v-for="campaign in recentCampaigns" :key="campaign.id" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium">{{ campaign.name }}</p>
                                    <span :class="['text-xs px-2 py-1 rounded-full', getStatusColor(campaign.status)]">
                                        {{ campaign.status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span>{{ campaign.email_template?.name || 'No template' }}</span>
                                    <span v-if="campaign.status === 'completed'">
                                        {{ campaign.sent_count }}/{{ campaign.total_recipients }} sent
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card class="col-span-full">
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Get started with common tasks
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-3 md:grid-cols-4">
                            <Link href="/customers/create">
                                <Button class="w-full justify-start" variant="outline">
                                    <Users class="mr-2 h-4 w-4" />
                                    Add Customer
                                </Button>
                            </Link>
                            <Link href="/groups/create">
                                <Button class="w-full justify-start" variant="outline">
                                    <FolderOpen class="mr-2 h-4 w-4" />
                                    Create Group
                                </Button>
                            </Link>
                            <Link href="/email-templates/create">
                                <Button class="w-full justify-start" variant="outline">
                                    <Mail class="mr-2 h-4 w-4" />
                                    New Template
                                </Button>
                            </Link>
                            <Link href="/campaigns/create">
                                <Button class="w-full justify-start" variant="outline">
                                    <Send class="mr-2 h-4 w-4" />
                                    New Campaign
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
