<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Sparkles, RefreshCw, Copy, Check } from 'lucide-vue-next';

interface Props {
    title: string;
    content: string;
    metaTitle: string;
    metaDescription: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:metaTitle', value: string): void;
    (e: 'update:metaDescription', value: string): void;
}>();

const isGenerating = ref(false);
const copied = ref<'title' | 'description' | null>(null);
const error = ref<string | null>(null);

// Character limits for SEO
const META_TITLE_MAX = 60;
const META_DESCRIPTION_MAX = 160;

// Computed properties for character counts
const metaTitleLength = computed(() => props.metaTitle?.length || 0);
const metaDescriptionLength = computed(() => props.metaDescription?.length || 0);

const metaTitleStatus = computed(() => {
    if (metaTitleLength.value === 0) return 'empty';
    if (metaTitleLength.value <= META_TITLE_MAX) return 'good';
    return 'warning';
});

const metaDescriptionStatus = computed(() => {
    if (metaDescriptionLength.value === 0) return 'empty';
    if (metaDescriptionLength.value <= META_DESCRIPTION_MAX) return 'good';
    return 'warning';
});

// Generate meta tags using AI
const generateMetaTags = async () => {
    if (!props.title && !props.content) {
        error.value = 'Please add a title or content first';
        return;
    }

    isGenerating.value = true;
    error.value = null;

    try {
        const response = await fetch('/admin/blog/posts/generate-meta', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                title: props.title,
                content: props.content,
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to generate meta tags');
        }

        const data = await response.json();

        if (data.meta_title) {
            emit('update:metaTitle', data.meta_title);
        }
        if (data.meta_description) {
            emit('update:metaDescription', data.meta_description);
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Failed to generate meta tags';
    } finally {
        isGenerating.value = false;
    }
};

// Copy to clipboard
const copyToClipboard = async (type: 'title' | 'description') => {
    const text = type === 'title' ? props.metaTitle : props.metaDescription;
    if (!text) return;

    try {
        await navigator.clipboard.writeText(text);
        copied.value = type;
        setTimeout(() => {
            copied.value = null;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

// Auto-generate from title if meta title is empty
const generateFromTitle = () => {
    if (!props.metaTitle && props.title) {
        const generated = props.title.length > META_TITLE_MAX
            ? props.title.substring(0, META_TITLE_MAX - 3) + '...'
            : props.title;
        emit('update:metaTitle', generated);
    }
};

// Watch title changes
watch(() => props.title, () => {
    if (!props.metaTitle) {
        generateFromTitle();
    }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header with AI Generate Button -->
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                SEO Settings
            </h3>
            <button
                type="button"
                @click="generateMetaTags"
                :disabled="isGenerating || disabled || (!title && !content)"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-gradient-to-r from-violet-500 to-purple-600 text-white hover:from-violet-600 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            >
                <Sparkles v-if="!isGenerating" class="h-4 w-4" />
                <RefreshCw v-else class="h-4 w-4 animate-spin" />
                {{ isGenerating ? 'Generating...' : 'AI Generate' }}
            </button>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="p-3 text-sm text-red-700 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg"
        >
            {{ error }}
        </div>

        <!-- Meta Title -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Meta Title
                </label>
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'text-xs font-medium',
                            metaTitleStatus === 'good' ? 'text-green-600 dark:text-green-400' :
                            metaTitleStatus === 'warning' ? 'text-amber-600 dark:text-amber-400' :
                            'text-slate-500 dark:text-slate-400'
                        ]"
                    >
                        {{ metaTitleLength }}/{{ META_TITLE_MAX }}
                    </span>
                    <button
                        type="button"
                        @click="copyToClipboard('title')"
                        :disabled="!metaTitle"
                        class="p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 disabled:opacity-50"
                        title="Copy to clipboard"
                    >
                        <Check v-if="copied === 'title'" class="h-4 w-4 text-green-500" />
                        <Copy v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>
            <input
                :value="metaTitle"
                @input="$emit('update:metaTitle', ($event.target as HTMLInputElement).value)"
                type="text"
                :disabled="disabled"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white disabled:opacity-50"
                placeholder="SEO optimized title (50-60 characters ideal)"
            />
            <p class="text-xs text-slate-500 dark:text-slate-400">
                The title that appears in search engine results. Keep it under 60 characters.
            </p>
        </div>

        <!-- Meta Description -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                    Meta Description
                </label>
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'text-xs font-medium',
                            metaDescriptionStatus === 'good' ? 'text-green-600 dark:text-green-400' :
                            metaDescriptionStatus === 'warning' ? 'text-amber-600 dark:text-amber-400' :
                            'text-slate-500 dark:text-slate-400'
                        ]"
                    >
                        {{ metaDescriptionLength }}/{{ META_DESCRIPTION_MAX }}
                    </span>
                    <button
                        type="button"
                        @click="copyToClipboard('description')"
                        :disabled="!metaDescription"
                        class="p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 disabled:opacity-50"
                        title="Copy to clipboard"
                    >
                        <Check v-if="copied === 'description'" class="h-4 w-4 text-green-500" />
                        <Copy v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>
            <textarea
                :value="metaDescription"
                @input="$emit('update:metaDescription', ($event.target as HTMLTextAreaElement).value)"
                rows="3"
                :disabled="disabled"
                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white disabled:opacity-50 resize-none"
                placeholder="Compelling description for search results (150-160 characters ideal)"
            />
            <p class="text-xs text-slate-500 dark:text-slate-400">
                The description shown in search results. Keep it under 160 characters.
            </p>
        </div>
    </div>
</template>
