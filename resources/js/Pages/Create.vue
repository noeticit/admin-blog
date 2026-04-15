<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Save, ArrowLeft, Eye, Calendar } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import MetaTagsGenerator from '@/components/admin-blog/MetaTagsGenerator.vue';
import SEOPreview from '@/components/admin-blog/SEOPreview.vue';
import RichTextEditor from '@/components/admin-blog/RichTextEditor.vue';
import FeaturedImageUpload from '@/components/admin-blog/FeaturedImageUpload.vue';

interface Category {
    id: number;
    name: string;
    slug?: string;
}

interface Tag {
    id: number;
    name: string;
    slug?: string;
}

interface Props {
    categories: Category[];
    tags: Tag[];
}

const props = defineProps<Props>();

const activeTab = ref<'content' | 'seo'>('content');
const showScheduleOptions = ref(false);

const form = useForm({
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    meta_title: '',
    meta_description: '',
    featured_image: '',
    category_id: null as number | null,
    tags: [] as number[],
    status: 'draft',
    published_at: null as string | null,
});

// Slugify helper
const slugify = (text: string): string => {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

// Auto-generate slug from title
watch(() => form.title, (newTitle, oldTitle) => {
    // Only auto-generate if slug is empty or matches the old title's slug
    if (!form.slug || form.slug === slugify(oldTitle || '')) {
        form.slug = slugify(newTitle);
    }
});

// Handle schedule toggle
const toggleSchedule = () => {
    showScheduleOptions.value = !showScheduleOptions.value;
    if (!showScheduleOptions.value) {
        form.published_at = null;
    }
};

// Set to publish now
const publishNow = () => {
    form.status = 'published';
    form.published_at = new Date().toISOString().slice(0, 16);
    submit();
};

// Save as draft
const saveDraft = () => {
    form.status = 'draft';
    submit();
};

// Submit form
const submit = () => {
    form.post(route('admin.blog.posts.store'));
};

// Get base URL for SEO preview
const baseUrl = typeof window !== 'undefined' ? window.location.origin : 'https://example.com';
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a
                        :href="route('admin.blog.posts.index')"
                        class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Create New Post
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Write and publish a new blog post
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="saveDraft"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors disabled:opacity-50"
                    >
                        Save Draft
                    </button>
                    <button
                        type="button"
                        @click="publishNow"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Publishing...' : 'Publish' }}
                    </button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-slate-200 dark:border-slate-700">
                <nav class="flex gap-4">
                    <button
                        type="button"
                        @click="activeTab = 'content'"
                        :class="[
                            'py-3 px-1 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'content'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        Content
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'seo'"
                        :class="[
                            'py-3 px-1 text-sm font-medium border-b-2 transition-colors',
                            activeTab === 'seo'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        SEO & Meta
                    </button>
                </nav>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content Area -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Content Tab -->
                        <template v-if="activeTab === 'content'">
                            <!-- Title -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Title *
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-4 py-3 text-lg border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary"
                                    placeholder="Enter an engaging title..."
                                    required
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-destructive">
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- Slug -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    URL Slug
                                </label>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        /blog/
                                    </span>
                                    <input
                                        v-model="form.slug"
                                        type="text"
                                        class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                        placeholder="post-slug"
                                    />
                                </div>
                                <p v-if="form.errors.slug" class="mt-1 text-sm text-destructive">
                                    {{ form.errors.slug }}
                                </p>
                            </div>

                            <!-- Content Editor -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Content *
                                </label>
                                <RichTextEditor
                                    v-model="form.content"
                                    placeholder="Write your post content here..."
                                    min-height="400px"
                                />
                                <p v-if="form.errors.content" class="mt-1 text-sm text-destructive">
                                    {{ form.errors.content }}
                                </p>
                            </div>

                            <!-- Excerpt -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Excerpt
                                </label>
                                <textarea
                                    v-model="form.excerpt"
                                    rows="3"
                                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white resize-none"
                                    placeholder="A brief summary of your post (shown in listings)..."
                                />
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    If left empty, an excerpt will be auto-generated from the content.
                                </p>
                            </div>
                        </template>

                        <!-- SEO Tab -->
                        <template v-if="activeTab === 'seo'">
                            <!-- Meta Tags Generator -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <MetaTagsGenerator
                                    :title="form.title"
                                    :content="form.content"
                                    v-model:meta-title="form.meta_title"
                                    v-model:meta-description="form.meta_description"
                                />
                            </div>

                            <!-- SEO Preview -->
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                                <SEOPreview
                                    :title="form.meta_title || form.title"
                                    :description="form.meta_description || form.excerpt"
                                    :slug="form.slug"
                                    :base-url="baseUrl"
                                />
                            </div>
                        </template>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Publish Settings -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                            <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                                Publish
                            </h3>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Status
                                </label>
                                <select
                                    v-model="form.status"
                                    class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <!-- Schedule Options -->
                            <div>
                                <button
                                    type="button"
                                    @click="toggleSchedule"
                                    class="flex items-center gap-2 text-sm text-primary hover:underline"
                                >
                                    <Calendar class="h-4 w-4" />
                                    {{ showScheduleOptions ? 'Hide schedule options' : 'Schedule for later' }}
                                </button>

                                <div v-if="showScheduleOptions" class="mt-3">
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Publish Date & Time
                                    </label>
                                    <input
                                        v-model="form.published_at"
                                        type="datetime-local"
                                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Save Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                            >
                                <Save class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Saving...' : 'Save Post' }}
                            </button>
                        </div>

                        <!-- Featured Image -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                            <FeaturedImageUpload v-model="form.featured_image" />
                        </div>

                        <!-- Category -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Category
                            </label>
                            <select
                                v-model="form.category_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            >
                                <option :value="null">No Category</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Tags -->
                        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tags
                            </label>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <label
                                    v-for="tag in tags"
                                    :key="tag.id"
                                    class="flex items-center gap-2 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 p-1 rounded"
                                >
                                    <input
                                        v-model="form.tags"
                                        type="checkbox"
                                        :value="tag.id"
                                        class="rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ tag.name }}
                                    </span>
                                </label>
                            </div>
                            <p v-if="tags.length === 0" class="text-sm text-slate-500 dark:text-slate-400">
                                No tags available.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
