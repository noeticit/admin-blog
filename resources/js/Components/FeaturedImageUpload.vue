<script setup lang="ts">
import { ref, computed } from 'vue';
import { Upload, X, Image as ImageIcon, Link, Loader2 } from 'lucide-vue-next';

interface Props {
    modelValue: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputMode = ref<'url' | 'upload'>('url');
const isUploading = ref(false);
const uploadProgress = ref(0);
const error = ref<string | null>(null);
const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

// Check if there's a valid image
const hasImage = computed(() => !!props.modelValue);

// Validate image URL
const isValidImageUrl = (url: string): boolean => {
    try {
        const parsed = new URL(url);
        return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(parsed.pathname);
    } catch {
        return false;
    }
};

// Handle URL input
const handleUrlInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
    error.value = null;
};

// Handle file selection
const handleFileSelect = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        await uploadFile(file);
    }
};

// Handle drag and drop
const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const handleDrop = async (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;

    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        await uploadFile(file);
    } else {
        error.value = 'Please drop an image file';
    }
};

// Upload file
const uploadFile = async (file: File) => {
    if (!file.type.startsWith('image/')) {
        error.value = 'Please select an image file';
        return;
    }

    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        error.value = 'Image size must be less than 5MB';
        return;
    }

    isUploading.value = true;
    uploadProgress.value = 0;
    error.value = null;

    try {
        const formData = new FormData();
        formData.append('image', file);

        const response = await fetch(route('admin.blog.upload-image'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('Upload failed');
        }

        const data = await response.json();
        emit('update:modelValue', data.url);
        uploadProgress.value = 100;
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Upload failed';
    } finally {
        isUploading.value = false;
    }
};

// Remove image
const removeImage = () => {
    emit('update:modelValue', '');
    error.value = null;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Trigger file input
const triggerFileInput = () => {
    fileInputRef.value?.click();
};
</script>

<template>
    <div class="space-y-4">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Featured Image
        </label>

        <!-- Mode Toggle -->
        <div class="flex items-center gap-2 p-1 bg-slate-100 dark:bg-slate-700 rounded-lg w-fit">
            <button
                type="button"
                @click="inputMode = 'url'"
                :class="[
                    'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                    inputMode === 'url'
                        ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                ]"
            >
                <Link class="h-4 w-4" />
                URL
            </button>
            <button
                type="button"
                @click="inputMode = 'upload'"
                :class="[
                    'flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md transition-colors',
                    inputMode === 'upload'
                        ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm'
                        : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                ]"
            >
                <Upload class="h-4 w-4" />
                Upload
            </button>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="p-3 text-sm text-red-700 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg"
        >
            {{ error }}
        </div>

        <!-- Image Preview -->
        <div v-if="hasImage" class="relative group">
            <div class="relative aspect-video rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                <img
                    :src="modelValue"
                    alt="Featured image preview"
                    class="w-full h-full object-cover"
                    @error="error = 'Failed to load image'"
                />

                <!-- Overlay on hover -->
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button
                        type="button"
                        @click="removeImage"
                        :disabled="disabled"
                        class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                        title="Remove image"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Image URL display -->
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 truncate">
                {{ modelValue }}
            </p>
        </div>

        <!-- URL Input -->
        <div v-else-if="inputMode === 'url'">
            <input
                :value="modelValue"
                @input="handleUrlInput"
                type="url"
                :disabled="disabled"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white disabled:opacity-50"
                placeholder="https://example.com/image.jpg"
            />
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Enter the URL of an image
            </p>
        </div>

        <!-- Upload Area -->
        <div
            v-else
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
            :class="[
                'relative border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                isDragging
                    ? 'border-primary bg-primary/5'
                    : 'border-slate-300 dark:border-slate-600 hover:border-slate-400 dark:hover:border-slate-500',
                disabled ? 'opacity-50 cursor-not-allowed' : ''
            ]"
            @click="!disabled && triggerFileInput()"
        >
            <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                class="hidden"
                :disabled="disabled"
                @change="handleFileSelect"
            />

            <!-- Uploading state -->
            <div v-if="isUploading" class="space-y-3">
                <Loader2 class="h-10 w-10 mx-auto text-primary animate-spin" />
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Uploading... {{ uploadProgress }}%
                </p>
                <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-primary transition-all duration-300"
                        :style="{ width: `${uploadProgress}%` }"
                    />
                </div>
            </div>

            <!-- Default state -->
            <div v-else class="space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                    <ImageIcon class="h-6 w-6 text-slate-500 dark:text-slate-400" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                        Drop an image here, or click to browse
                    </p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        PNG, JPG, GIF, WebP up to 5MB
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
