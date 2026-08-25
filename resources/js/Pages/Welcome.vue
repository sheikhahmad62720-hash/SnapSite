<script setup>
import { ref, reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import UrlInput from '@/Components/UrlInput.vue';
import DeviceSelector from '@/Components/DeviceSelector.vue';
import ScreenshotOptions from '@/Components/ScreenshotOptions.vue';
import ScreenshotPreview from '@/Components/ScreenshotPreview.vue';

const form = reactive({
    url: '',
    device: 'desktop',
    width: 1366,
    height: 768,
    screenshot_type: 'viewport',
    format: 'png',
});

const urlError = ref('');
const generating = ref(false);
const screenshot = ref(null);
const error = ref('');

const validateUrl = () => {
    urlError.value = '';
    const url = form.url.trim();

    if (!url) {
        urlError.value = 'Please enter a website URL.';
        return false;
    }

    try {
        const parsed = new URL(url);
        if (!['http:', 'https:'].includes(parsed.protocol)) {
            urlError.value = 'Only HTTP and HTTPS URLs are allowed.';
            return false;
        }
    } catch {
        urlError.value = 'Please enter a valid website URL.';
        return false;
    }

    return true;
};

const generate = async () => {
    if (!validateUrl()) return;

    generating.value = true;
    error.value = '';
    screenshot.value = null;

    try {
        const response = await fetch('/screenshots/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                url: form.url.trim(),
                width: form.width,
                height: form.height,
                screenshot_type: form.screenshot_type,
                format: form.format,
            }),
        });

        const data = await response.json();

        if (data.success) {
            screenshot.value = data.data;
        } else {
            error.value = data.message || 'Screenshot generation failed. Please try again.';
        }
    } catch (err) {
        error.value = 'Unable to connect to the server. Please try again.';
    } finally {
        generating.value = false;
    }
};
</script>

<template>
    <Head title="SnapSite - Website Screenshot Generator" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50">
        <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <header class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 tracking-tight">
                    Snap<span class="text-blue-600">Site</span>
                </h1>
                <p class="mt-2 text-xl text-gray-500">Website Screenshot Generator</p>
                <p class="mt-1 text-gray-400">Capture any public website in seconds.</p>
            </header>

            <div class="space-y-8">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="space-y-6">
                        <UrlInput
                            v-model="form.url"
                            :error="urlError"
                            @enter="generate"
                        />

                        <DeviceSelector
                            v-model:selected="form.device"
                            v-model:width="form.width"
                            v-model:height="form.height"
                        />

                        <ScreenshotOptions
                            v-model:screenshotType="form.screenshot_type"
                            v-model:format="form.format"
                        />

                        <button
                            @click="generate"
                            :disabled="generating"
                            class="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span v-if="generating" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Generating Screenshot...
                            </span>
                            <span v-else>Generate Screenshot</span>
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <ScreenshotPreview
                        :screenshot="screenshot"
                        :generating="generating"
                        :error="error"
                    />
                </div>
            </div>

            <footer class="mt-12 text-center text-sm text-gray-400">
                Built with Laravel, Vue &amp; Playwright
            </footer>
        </div>
    </div>
</template>
