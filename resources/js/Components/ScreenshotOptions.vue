<script setup>
defineProps({
    screenshotType: { type: String, default: 'viewport' },
    format: { type: String, default: 'png' },
});

const emit = defineEmits(['update:screenshotType', 'update:format']);

const formats = [
    { value: 'png', label: 'PNG' },
    { value: 'jpeg', label: 'JPEG' },
    { value: 'webp', label: 'WEBP' },
];
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Screenshot Type</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        :checked="screenshotType === 'viewport'"
                        @change="emit('update:screenshotType', 'viewport')"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm text-gray-700">Viewport</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="radio"
                        :checked="screenshotType === 'full'"
                        @change="emit('update:screenshotType', 'full')"
                        class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm text-gray-700">Full Page</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Image Format</label>
            <select
                :value="format"
                @change="emit('update:format', $event.target.value)"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
                <option v-for="f in formats" :key="f.value" :value="f.value">{{ f.label }}</option>
            </select>
        </div>
    </div>
</template>
