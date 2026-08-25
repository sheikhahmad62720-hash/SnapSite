<script setup>
import { computed } from 'vue';

const props = defineProps({
    selected: { type: String, default: 'desktop' },
    width: { type: Number, default: 1366 },
    height: { type: Number, default: 768 },
});

const emit = defineEmits(['update:selected', 'update:width', 'update:height']);

const devices = [
    { id: 'desktop', label: 'Desktop', icon: '🖥', width: 1366, height: 768 },
    { id: 'laptop', label: 'Laptop', icon: '💻', width: 1280, height: 800 },
    { id: 'tablet', label: 'Tablet', icon: '📱', width: 768, height: 1024 },
    { id: 'mobile', label: 'Mobile', icon: '📲', width: 375, height: 812 },
    { id: 'custom', label: 'Custom', icon: '⚙', width: 1366, height: 768 },
];

const selectDevice = (device) => {
    emit('update:selected', device.id);
    if (device.id !== 'custom') {
        emit('update:width', device.width);
        emit('update:height', device.height);
    }
};

const localWidth = computed({
    get: () => props.width,
    set: (val) => {
        const num = parseInt(val) || 1366;
        emit('update:width', Math.max(320, Math.min(3840, num)));
    },
});

const localHeight = computed({
    get: () => props.height,
    set: (val) => {
        const num = parseInt(val) || 768;
        emit('update:height', Math.max(320, Math.min(21600, num)));
    },
});
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Device</label>
        <div class="flex flex-wrap gap-2 mb-4">
            <button
                v-for="device in devices"
                :key="device.id"
                type="button"
                @click="selectDevice(device)"
                class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-medium transition"
                :class="
                    selected === device.id
                        ? 'bg-blue-600 text-white shadow-md'
                        : 'bg-white text-gray-700 border border-gray-300 hover:border-blue-400 hover:text-blue-600'
                "
            >
                <span>{{ device.icon }}</span>
                <span>{{ device.label }}</span>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Width</label>
                <input
                    v-model="localWidth"
                    type="number"
                    :disabled="selected !== 'custom'"
                    min="320"
                    max="3840"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-gray-100 disabled:text-gray-500"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Height</label>
                <input
                    v-model="localHeight"
                    type="number"
                    :disabled="selected !== 'custom'"
                    min="320"
                    max="21600"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-gray-100 disabled:text-gray-500"
                />
            </div>
        </div>
    </div>
</template>
