<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface EmailTemplate {
    id: number;
    name: string;
}

interface Group {
    id: number;
    name: string;
    customers_count: number;
}

interface Campaign {
    id: number;
    name: string;
    email_template_id: number;
    scheduled_at?: string;
    groups: Array<{ id: number }>;
}

interface Props {
    campaign: Campaign;
    templates: EmailTemplate[];
    groups: Group[];
}

const props = defineProps<Props>();

const form = ref({
    name: props.campaign.name,
    email_template_id: props.campaign.email_template_id.toString(),
    group_ids: props.campaign.groups.map(g => g.id),
    scheduled_at: props.campaign.scheduled_at || '',
});

const submit = () => {
    const formData = {
        ...form.value,
        scheduled_at: form.value.scheduled_at
            ? new Date(form.value.scheduled_at).toLocaleString('en-US', {
                timeZone: 'Europe/Paris',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            }).replace(/(\d+)\/(\d+)\/(\d+), (\d+:\d+:\d+)/, '$3-$1-$2 $4')
            : ''
    };

    router.put(`/campaigns/${props.campaign.id}`, formData);
};
</script>

<template>
    <AppLayout title="Edit Campaign">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Edit Campaign</h1>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Campaign Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                    />
                </div>

                <div class="space-y-2">
                    <Label for="template">Email Template *</Label>
                    <Select v-model="form.email_template_id" required>
                        <SelectTrigger>
                            <SelectValue placeholder="Select a template" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="template in templates"
                                :key="template.id"
                                :value="template.id.toString()"
                            >
                                {{ template.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label>Target Groups * (at least one)</Label>
                    <div class="border rounded-lg p-4 space-y-2">
                        <label
                            v-for="group in groups"
                            :key="group.id"
                            class="flex items-center space-x-2"
                        >
                            <input
                                type="checkbox"
                                :value="group.id"
                                v-model="form.group_ids"
                                class="rounded"
                            />
                            <span>{{ group.name }} ({{ group.customers_count }} customers)</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="scheduled_at">Schedule For (Optional)</Label>
                    <Input
                        id="scheduled_at"
                        v-model="form.scheduled_at"
                        type="datetime-local"
                    />
                </div>

                <div class="flex gap-4">
                    <Button type="submit">Update Campaign</Button>
                    <Button type="button" variant="outline" @click="router.visit('/campaigns')">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
