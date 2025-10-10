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

interface Group {
    id: number;
    name: string;
}

interface Customer {
    id: number;
    email: string;
    first_name: string;
    last_name: string;
    sex?: string;
    birth_date?: string;
    groups: Group[];
}

interface Props {
    customer: Customer;
    groups: Group[];
}

const props = defineProps<Props>();

const form = ref({
    email: props.customer.email,
    first_name: props.customer.first_name,
    last_name: props.customer.last_name,
    sex: props.customer.sex || '',
    birth_date: props.customer.birth_date || '',
    group_ids: props.customer.groups.map(g => g.id),
});

const submit = () => {
    router.put(`/customers/${props.customer.id}`, form.value);
};
</script>

<template>
    <AppLayout title="Edit Customer">
        <div class="p-6">
            <h1 class="text-3xl font-bold mb-6">Edit Customer</h1>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <div class="space-y-2">
                    <Label for="email">Email *</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="first_name">First Name *</Label>
                        <Input
                            id="first_name"
                            v-model="form.first_name"
                            required
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="last_name">Last Name *</Label>
                        <Input
                            id="last_name"
                            v-model="form.last_name"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="sex">Sex (Optional)</Label>
                        <Select v-model="form.sex">
                            <SelectTrigger>
                                <SelectValue placeholder="Select sex" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="male">Male</SelectItem>
                                <SelectItem value="female">Female</SelectItem>
                                <SelectItem value="other">Other</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-2">
                        <Label for="birth_date">Birth Date (Optional)</Label>
                        <Input
                            id="birth_date"
                            v-model="form.birth_date"
                            type="date"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Groups (Optional)</Label>
                    <div class="space-y-2">
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
                            <span>{{ group.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-4">
                    <Button type="submit">Update Customer</Button>
                    <Button type="button" variant="outline" @click="router.visit('/customers')">
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
