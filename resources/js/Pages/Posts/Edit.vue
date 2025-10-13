<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Save, ArrowLeft, Trash2 } from 'lucide-vue-next';

interface Post {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt: string;
    meta_title: string;
    meta_description: string;
    featured_image: string;
    category_id: number | null;
    tags: number[];
    status: string;
    published_at: string | null;
}

interface Category {
    id: number;
    name: string;
}

interface Tag {
    id: number;
    name: string;
}

interface Props {
    post: Post;
    categories: Category[];
    tags: Tag[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    content: props.post.content,
    excerpt: props.post.excerpt || '',
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
    featured_image: props.post.featured_image || '',
    category_id: props.post.category_id,
    tags: props.post.tags || [],
    status: props.post.status,
    published_at: props.post.published_at,
});

const generateSlug = () => {
    form.slug = form.title
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

const submit = () => {
    form.put(route('admin.blog.posts.update', props.post.id));
};

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('admin.blog.posts.destroy', props.post.id));
    }
};
</script>

<template>
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
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Post</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Update blog post
                    </p>
                </div>
            </div>
            <button
                @click="deletePost"
                class="inline-flex items-center px-4 py-2 bg-destructive text-destructive-foreground rounded-lg hover:bg-destructive/90 transition-colors"
            >
                <Trash2 class="h-4 w-4 mr-2" />
                Delete
            </button>
        </div>

        <!-- Form (same as Create.vue) -->
        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Title *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            @blur="generateSlug"
                            required
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-destructive">{{ form.errors.title }}</p>
                    </div>

                    <!-- Slug -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Slug *
                        </label>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            required
                        />
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-destructive">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Content -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Content *
                        </label>
                        <textarea
                            v-model="form.content"
                            rows="15"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            required
                        />
                        <p v-if="form.errors.content" class="mt-1 text-sm text-destructive">{{ form.errors.content }}</p>
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="3"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- SEO Meta -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white">SEO Settings</h3>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Meta Title
                            </label>
                            <input
                                v-model="form.meta_title"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Meta Description
                            </label>
                            <textarea
                                v-model="form.meta_description"
                                rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                        <h3 class="text-lg font-medium text-slate-900 dark:text-white">Publish</h3>

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

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Publish Date
                            </label>
                            <input
                                v-model="form.published_at"
                                type="datetime-local"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                        >
                            <Save class="h-4 w-4 mr-2" />
                            {{ form.processing ? 'Updating...' : 'Update Post' }}
                        </button>
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
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Tags
                        </label>
                        <div class="space-y-2">
                            <label v-for="tag in tags" :key="tag.id" class="flex items-center gap-2">
                                <input
                                    v-model="form.tags"
                                    type="checkbox"
                                    :value="tag.id"
                                    class="rounded border-slate-300 dark:border-slate-600"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ tag.name }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-6">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Featured Image URL
                        </label>
                        <input
                            v-model="form.featured_image"
                            type="text"
                            class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                        />
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
