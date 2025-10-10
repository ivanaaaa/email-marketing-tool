<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';

interface EmailTemplate {
    id: number;
    name: string;
    subject: string;
    body: string;
}

interface Props {
    template: EmailTemplate;
    placeholders: Record<string, string>;
}

const props = defineProps<Props>();

const form = ref({
    name: props.template.name,
    subject: props.template.subject,
    body: props.template.body,
});

const submit = () => {
    router.put(`/email-templates/${props.template.id}`, form.value);
};
</script>

<template>
    <AppLayout title="Edit Email Template">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Edit Email Template</h1>

            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold mb-2">Available Placeholders (click to insert):</h3>
                <div class="flex flex-wrap gap-2">
                    <Badge
                        v-for="(desc, placeholder) in placeholders"
                        :key="placeholder"
                        variant="secondary"
                        class="cursor-pointer hover:bg-gray-300"
                        @click="form.body += ' ' + placeholder"
                    >
                        {{ placeholder }} - {{ desc }}
                    </Badge>
                </div>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Template Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                    />
                </div>

                <div class="space-y-2">
                    <Label for="subject">Email Subject *</Label>
                    <Input
                        id="subject"
                        v-model="form.subject"
                        required
                    />
                </div>

                <div class="space-y-2">
                    <Label for="body">Email Body *</Label>
                    <Textarea
                        id="body"
                        v-model="form.body"
                        required
                        rows="10"
                    />
                </div>

                <div class="flex gap-4">
                    <Button type="submit">Update Template</Button>
                    <Button type="button" variant="outline" @click="router.visit('/email-templates')">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
