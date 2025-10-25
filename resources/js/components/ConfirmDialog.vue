<script setup lang="ts">
import { ref, watch } from 'vue'

interface Props {
    open: boolean
    title?: string
    description?: string
    confirmText?: string
    cancelText?: string
    variant?: 'default' | 'destructive'
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Are you sure?',
    description: 'This action cannot be undone.',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    variant: 'default',
})

const emit = defineEmits<{
    confirm: []
    cancel: []
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
    internalOpen.value = false
    emit('update:open', false)
}

const handleConfirm = () => {
    emit('confirm')
    closeDialog()
}

const handleCancel = () => {
    emit('cancel')
    closeDialog()
}
</script>

<template>
    <Teleport to="body">
        <transition name="fade">
            <div
                v-if="internalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center"
            >
                <!-- Overlay -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="handleCancel"
                ></div>

                <!-- Dialog -->
                <div
                    class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl p-6 w-full max-w-md z-10 animate-fade-in"
                >
                    <h2 class="text-xl font-semibold mb-2">
                        {{ title }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        {{ description }}
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="handleCancel"
                            class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            {{ cancelText }}
                        </button>
                        <button
                            @click="handleConfirm"
                            :class="[
                'px-4 py-2 rounded-md text-white font-semibold',
                variant === 'destructive'
                  ? 'bg-red-600 hover:bg-red-700'
                  : 'bg-blue-600 hover:bg-blue-700'
              ]"
                        >
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

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
