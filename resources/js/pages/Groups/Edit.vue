<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

interface Customer {
    id: number;
    email: string;
    first_name: string;
    last_name: string;
}

interface Group {
    id: number;
    name: string;
    description?: string;
    customers: Customer[];
}

interface Props {
    group: Group;
    customers: {
        data: Customer[];
    };
}

const props = defineProps<Props>();

const form = ref({
    name: props.group.name,
    description: props.group.description || '',
    customer_ids: props.group.customers.map(c => c.id),
});

const submit = () => {
    router.put(`/groups/${props.group.id}`, form.value);
};
</script>

<template>
    <AppLayout title="Edit Group">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Edit Group</h1>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Group Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                    />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description (Optional)</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                    />
                </div>

                <div class="space-y-2">
                    <Label>Customers (Optional)</Label>
                    <div class="border rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                        <label
                            v-for="customer in customers.data"
                            :key="customer.id"
                            class="flex items-center space-x-2"
                        >
                            <input
                                type="checkbox"
                                :value="customer.id"
                                v-model="form.customer_ids"
                                class="rounded"
                            />
                            <span>{{ customer.first_name }} {{ customer.last_name }} ({{ customer.email }})</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-4">
                    <Button type="submit">Update Group</Button>
                    <Button type="button" variant="outline" @click="router.visit('/groups')">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
