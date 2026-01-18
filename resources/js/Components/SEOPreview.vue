<script setup lang="ts">
import { computed } from 'vue';
import { Globe, Smartphone, Monitor } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    title: string;
    description: string;
    slug: string;
    baseUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    baseUrl: 'https://example.com',
});

const previewMode = ref<'desktop' | 'mobile'>('desktop');

// Character limits for display
const TITLE_DESKTOP_MAX = 60;
const TITLE_MOBILE_MAX = 55;
const DESC_DESKTOP_MAX = 160;
const DESC_MOBILE_MAX = 120;

// Truncate text for display
const truncate = (text: string, max: number): string => {
    if (!text) return '';
    if (text.length <= max) return text;
    return text.substring(0, max - 3) + '...';
};

// Computed display values
const displayTitle = computed(() => {
    const max = previewMode.value === 'desktop' ? TITLE_DESKTOP_MAX : TITLE_MOBILE_MAX;
    return truncate(props.title || 'Page Title', max);
});

const displayDescription = computed(() => {
    const max = previewMode.value === 'desktop' ? DESC_DESKTOP_MAX : DESC_MOBILE_MAX;
    return truncate(props.description || 'Add a meta description to improve your search appearance.', max);
});

const displayUrl = computed(() => {
    const slug = props.slug || 'page-slug';
    return `${props.baseUrl}/blog/${slug}`;
});

// Format URL for display (shorter version)
const formattedUrl = computed(() => {
    try {
        const url = new URL(displayUrl.value);
        const path = url.pathname.length > 30
            ? url.pathname.substring(0, 30) + '...'
            : url.pathname;
        return `${url.hostname}${path}`;
    } catch {
        return displayUrl.value;
    }
});

// SEO Score calculation
const seoScore = computed(() => {
    let score = 0;
    const issues: string[] = [];

    // Title checks
    if (props.title) {
        score += 20;
        if (props.title.length >= 30 && props.title.length <= 60) {
            score += 15;
        } else {
            issues.push(props.title.length < 30 ? 'Title too short' : 'Title too long');
        }
    } else {
        issues.push('Missing meta title');
    }

    // Description checks
    if (props.description) {
        score += 20;
        if (props.description.length >= 120 && props.description.length <= 160) {
            score += 15;
        } else {
            issues.push(props.description.length < 120 ? 'Description too short' : 'Description too long');
        }
    } else {
        issues.push('Missing meta description');
    }

    // Slug check
    if (props.slug) {
        score += 15;
        if (props.slug.length <= 50 && !props.slug.includes('_')) {
            score += 15;
        } else {
            issues.push('URL could be optimized');
        }
    } else {
        issues.push('Missing URL slug');
    }

    return { score, issues };
});

const scoreColor = computed(() => {
    const score = seoScore.value.score;
    if (score >= 80) return 'text-green-600 dark:text-green-400';
    if (score >= 50) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
});

const scoreBgColor = computed(() => {
    const score = seoScore.value.score;
    if (score >= 80) return 'bg-green-500';
    if (score >= 50) return 'bg-amber-500';
    return 'bg-red-500';
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                Search Preview
            </h3>
            <div class="flex items-center gap-1 p-1 bg-slate-100 dark:bg-slate-700 rounded-lg">
                <button
                    type="button"
                    @click="previewMode = 'desktop'"
                    :class="[
                        'p-1.5 rounded-md transition-colors',
                        previewMode === 'desktop'
                            ? 'bg-white dark:bg-slate-600 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                    ]"
                    title="Desktop preview"
                >
                    <Monitor class="h-4 w-4" />
                </button>
                <button
                    type="button"
                    @click="previewMode = 'mobile'"
                    :class="[
                        'p-1.5 rounded-md transition-colors',
                        previewMode === 'mobile'
                            ? 'bg-white dark:bg-slate-600 shadow-sm'
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                    ]"
                    title="Mobile preview"
                >
                    <Smartphone class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Google-style Preview -->
        <div
            :class="[
                'p-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg',
                previewMode === 'mobile' ? 'max-w-sm' : ''
            ]"
        >
            <!-- URL -->
            <div class="flex items-center gap-2 text-sm">
                <Globe class="h-4 w-4 text-slate-400" />
                <span class="text-green-700 dark:text-green-400">
                    {{ formattedUrl }}
                </span>
            </div>

            <!-- Title -->
            <h4 class="mt-1 text-xl text-blue-700 dark:text-blue-400 hover:underline cursor-pointer leading-tight">
                {{ displayTitle }}
            </h4>

            <!-- Description -->
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                {{ displayDescription }}
            </p>
        </div>

        <!-- SEO Score -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    SEO Score
                </span>
                <span :class="['text-lg font-bold', scoreColor]">
                    {{ seoScore.score }}%
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div
                    :class="['h-full transition-all duration-500', scoreBgColor]"
                    :style="{ width: `${seoScore.score}%` }"
                />
            </div>

            <!-- Issues -->
            <div v-if="seoScore.issues.length > 0" class="mt-3 space-y-1">
                <p class="text-xs font-medium text-slate-600 dark:text-slate-400">
                    Suggestions:
                </p>
                <ul class="text-xs text-slate-500 dark:text-slate-400 space-y-0.5">
                    <li v-for="issue in seoScore.issues" :key="issue" class="flex items-center gap-1">
                        <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                        {{ issue }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
