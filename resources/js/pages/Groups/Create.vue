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

interface Props {
    customers: {
        data: Customer[];
    };
}

const props = defineProps<Props>();

const form = ref({
    name: '',
    description: '',
    customer_ids: [] as number[],
});

const submit = () => {
    router.post('/groups', form.value);
};
</script>

<template>
    <AppLayout title="Create Group">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Create Group</h1>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="name">Group Name *</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        placeholder="VIP Customers"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description (Optional)</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Description of this group"
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
                    <Button type="submit">Create Group</Button>
                    <Button type="button" variant="outline" @click="router.visit('/groups')">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
