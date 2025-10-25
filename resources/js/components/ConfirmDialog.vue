<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'

interface Props {
    open: boolean
    title?: string
    description?: string
    confirmText?: string
    cancelText?: string
    variant?: 'default' | 'destructive'
    loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    description: 'This action cannot be undone.',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    variant: 'default',
    loading: false,
})

const emit = defineEmits<{
    confirm: []
    'update:open': [value: boolean]
}>()

const internalOpen = ref(props.open)

watch(
    () => props.open,
    (value) => {
        internalOpen.value = value
    }
)

const closeDialog = () => {
    if (props.loading) return
    internalOpen.value = false
    emit('update:open', false) // ✅ This handles both cancel and X button
}

const handleConfirm = () => {
    if (props.loading) return
    emit('confirm')
}

const handleCancel = () => {
    closeDialog() // ✅ Just close the dialog, parent handles via @update:open
}

const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && internalOpen.value && !props.loading) {
        handleCancel()
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape)
})
</script>

<template>
    <Teleport to="body">
        <transition name="fade">
            <div
                v-if="internalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="title"
            >
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="handleCancel"
                ></div>

                <div
                    class="relative bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md z-10 animate-fade-in"
                >
                    <h2 class="text-xl font-semibold mb-2 text-gray-900" :id="title">
                        {{ title }}
                    </h2>
                    <p class="text-gray-600 mb-6">
                        {{ description }}
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="handleCancel"
                            :disabled="loading"
                            class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            @click="handleConfirm"
                            :disabled="loading"
                            :class="[
                                'px-4 py-2 rounded-md text-white font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2',
                                variant === 'destructive'
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-blue-600 hover:bg-blue-700'
                            ]"
                        >
                            <svg
                                v-if="loading"
                                class="animate-spin h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <span>{{ loading ? 'Processing...' : confirmText }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>

@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}
</style>
