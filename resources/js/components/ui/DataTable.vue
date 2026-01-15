<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">{{ title }}</h2>
            <slot name="header-actions"></slot>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">Loading...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="p-12 text-center">
            <p class="text-red-600">{{ error }}</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!data || data.length === 0" class="p-12 text-center">
            <p class="text-gray-500">{{ emptyMessage }}</p>
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th v-for="column in columns" :key="column.key"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ column.label }}
                        </th>
                        <th v-if="actions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in data" :key="item[itemKey] || index" class="hover:bg-gray-50">
                        <td v-for="column in columns" :key="column.key" class="px-6 py-4 whitespace-nowrap">
                            <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]">
                                {{ item[column.key] }}
                            </slot>
                        </td>
                        <td v-if="actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <slot name="actions" :item="item"></slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing {{ pagination.from || 1 }} to {{ pagination.to || data.length }} of {{ pagination.total }} results
            </div>
            <div class="flex gap-2">
                <button @click="$emit('page-change', pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <button @click="$emit('page-change', pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DataTable',
    props: {
        title: {
            type: String,
            required: true
        },
        columns: {
            type: Array,
            required: true
        },
        data: {
            type: Array,
            default: () => []
        },
        loading: {
            type: Boolean,
            default: false
        },
        error: {
            type: String,
            default: null
        },
        emptyMessage: {
            type: String,
            default: 'No data available'
        },
        actions: {
            type: Boolean,
            default: true
        },
        itemKey: {
            type: String,
            default: 'id'
        },
        pagination: {
            type: Object,
            default: null
        }
    },
    emits: ['page-change']
}
</script>
