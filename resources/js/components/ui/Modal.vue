<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto" @click.self="close">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="close"></div>

                    <!-- Modal panel -->
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                         :class="sizeClass">
                        <!-- Header -->
                        <div class="bg-white px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ title }}
                                </h3>
                                <button @click="close" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="bg-white px-6 py-4">
                            <slot></slot>
                        </div>

                        <!-- Footer -->
                        <div v-if="$slots.footer" class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                            <slot name="footer"></slot>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
export default {
    name: 'Modal',
    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        size: {
            type: String,
            default: 'md',
            validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
        }
    },
    emits: ['update:modelValue'],
    computed: {
        sizeClass() {
            const sizes = {
                sm: 'sm:max-w-sm',
                md: 'sm:max-w-lg',
                lg: 'sm:max-w-2xl',
                xl: 'sm:max-w-4xl'
            };
            return sizes[this.size];
        }
    },
    methods: {
        close() {
            this.$emit('update:modelValue', false);
        }
    }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
</style>
