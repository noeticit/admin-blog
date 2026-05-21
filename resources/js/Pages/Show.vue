<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Edit as EditIcon, Eye, Calendar, Clock } from 'lucide-vue-next';

interface Author {
    id: number;
    name?: string;
    email?: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    featured_image: string | null;
    status: 'draft' | 'published' | 'archived';
    published_at: string | null;
    views_count: number;
    reading_time: number | null;
    word_count: number | null;
    meta_title: string | null;
    meta_description: string | null;
    category: Category | null;
    author: Author | null;
    tags: Tag[];
    created_at: string;
    updated_at: string;
}

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const statusColor = (status: Post['status']) => {
    switch (status) {
        case 'published':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300';
        case 'archived':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        default:
            return 'bg-slate-100 text-slate-800';
    }
};

const formatDate = (value: string | null): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head :title="post.title" />

    <div class="space-y-6 p-4 sm:p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Link
                    href="/admin/blog/posts"
                    class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    title="Back to posts"
                >
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ post.title }}</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ post.category?.name ?? 'Uncategorized' }}
                        <span v-if="post.author?.name"> · by {{ post.author.name }}</span>
                    </p>
                </div>
            </div>
            <Link
                :href="`/admin/blog/posts/${post.id}/edit`"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
                <EditIcon class="h-4 w-4 mr-2" />
                Edit
            </Link>
        </div>

        <!-- Meta strip -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</p>
                <span
                    :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2 capitalize',
                        statusColor(post.status),
                    ]"
                >
                    {{ post.status }}
                </span>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Views</p>
                <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                    <Eye class="h-4 w-4 text-slate-400" />
                    {{ post.views_count }}
                </p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Reading time</p>
                <p class="text-lg font-semibold text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                    <Clock class="h-4 w-4 text-slate-400" />
                    {{ post.reading_time ?? '—' }} min
                </p>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Published</p>
                <p class="text-sm font-medium text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                    <Calendar class="h-4 w-4 text-slate-400" />
                    {{ formatDate(post.published_at) }}
                </p>
            </div>
        </div>

        <!-- Featured image -->
        <div
            v-if="post.featured_image"
            class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden"
        >
            <img :src="post.featured_image" :alt="post.title" class="w-full max-h-96 object-cover" />
        </div>

        <!-- Excerpt -->
        <div
            v-if="post.excerpt"
            class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6"
        >
            <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">Excerpt</p>
            <p class="text-slate-700 dark:text-slate-300 italic">{{ post.excerpt }}</p>
        </div>

        <!-- Content -->
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
            <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">Content</p>
            <article
                class="prose prose-slate dark:prose-invert max-w-none"
                v-html="post.content"
            ></article>
        </div>

        <!-- Tags -->
        <div
            v-if="post.tags && post.tags.length"
            class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6"
        >
            <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-3">Tags</p>
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="tag in post.tags"
                    :key="tag.id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300"
                >
                    #{{ tag.name }}
                </span>
            </div>
        </div>

        <!-- SEO -->
        <div
            v-if="post.meta_title || post.meta_description"
            class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6 space-y-3"
        >
            <p class="text-sm uppercase tracking-wide text-slate-500 dark:text-slate-400">SEO</p>
            <div v-if="post.meta_title">
                <p class="text-xs text-slate-500 dark:text-slate-400">Meta title</p>
                <p class="text-slate-900 dark:text-white">{{ post.meta_title }}</p>
            </div>
            <div v-if="post.meta_description">
                <p class="text-xs text-slate-500 dark:text-slate-400">Meta description</p>
                <p class="text-slate-700 dark:text-slate-300">{{ post.meta_description }}</p>
            </div>
        </div>
    </div>
</template>
