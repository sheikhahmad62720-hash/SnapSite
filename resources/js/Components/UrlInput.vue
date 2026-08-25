<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    error: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'enter']);

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const onKeydown = (e) => {
    if (e.key === 'Enter') {
        emit('enter');
    }
};
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Website URL</label>
        <input
            v-model="value"
            type="url"
            placeholder="https://example.com"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
            @keydown="onKeydown"
        />
        <p v-if="error" class="mt-1.5 text-sm text-red-600">{{ error }}</p>
    </div>
</template>
