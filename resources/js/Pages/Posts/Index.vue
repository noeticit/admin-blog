<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Edit, Eye, Trash2, Plus, Search, Filter } from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Post {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published' | 'archived';
    published_at: string | null;
    views_count: number;
    created_at: string;
    category?: {
        id: number;
        name: string;
    };
    author: {
        id: number;
        name: string;
    };
}

interface Props {
    posts: {
        data: Post[];
        links: any;
        meta: any;
    };
    categories: any[];
    filters: {
        search?: string;
        category?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPosts = ref<number[]>([]);

const applyFilters = () => {
    router.get(route('admin.blog.posts.index'), {
        search: search.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deletePost = (id: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('admin.blog.posts.destroy', id));
    }
};

const getStatusColor = (status: string) => {
    const colors = {
        published: 'bg-green-100 text-green-800',
        draft: 'bg-yellow-100 text-yellow-800',
        archived: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || colors.draft;
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Blog Posts</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Manage your blog posts
                </p>
            </div>
            <a
                :href="route('admin.blog.posts.create')"
                class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
                <Plus class="h-4 w-4 mr-2" />
                New Post
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search posts..."
                            class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>
                <select
                    v-model="selectedStatus"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    @change="applyFilters"
                >
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                >
                    <Filter class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Title
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Category
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Author
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <tr v-for="post in posts.data" :key="post.id" class="hover:bg-slate-50 dark:hover:bg-slate-900">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                {{ post.title }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ post.slug }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                            {{ post.category?.name || '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span :class="getStatusColor(post.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ post.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                            {{ post.author.name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ new Date(post.created_at).toLocaleDateString() }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    :href="route('admin.blog.posts.edit', post.id)"
                                    class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                    title="Edit"
                                >
                                    <Edit class="h-4 w-4" />
                                </a>
                                <button
                                    @click="deletePost(post.id)"
                                    class="p-2 text-slate-600 hover:text-destructive hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Empty State -->
            <div v-if="posts.data.length === 0" class="text-center py-12">
                <p class="text-slate-500 dark:text-slate-400">No posts found</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="posts.links && posts.meta" class="flex items-center justify-between">
            <div class="text-sm text-slate-600 dark:text-slate-400">
                Showing {{ posts.meta.from || 0 }} to {{ posts.meta.to || 0 }} of {{ posts.meta.total || 0 }} posts
            </div>
            <div class="flex gap-2">
                <a
                    v-for="link in posts.links"
                    :key="link.label"
                    :href="link.url"
                    v-html="link.label"
                    :class="[
                        'px-3 py-1 rounded-lg text-sm transition-colors',
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'
                    ]"
                />
            </div>
        </div>
        </div>
    </AdminLayout>
</template>
