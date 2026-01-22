<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    Edit,
    Trash2,
    Plus,
    Search,
    X,
    ChevronDown,
    ChevronUp,
    FileText,
    CheckCircle,
    Clock,
    Archive,
    RefreshCw,
    SlidersHorizontal,
} from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Post {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published' | 'archived';
    published_at: string | null;
    views_count: number;
    created_at: string;
    updated_at: string;
    reading_time: number;
    word_count: number;
    category?: {
        id: number;
        name: string;
        slug: string;
    };
    tags?: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    author: {
        id: number;
        name: string;
    };
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

interface Stats {
    total: number;
    published: number;
    draft: number;
    archived: number;
}

interface Props {
    posts: {
        data: Post[];
        links: any[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            per_page: number;
            to: number;
            total: number;
        };
    };
    categories: Category[];
    tags: Tag[];
    stats: Stats;
    filters: {
        search?: string;
        category?: string;
        status?: string;
        tag?: string;
        author?: string;
        sort?: string;
        direction?: string;
        per_page?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

// Filter state
const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedCategory = ref(props.filters.category || '');
const selectedTag = ref(props.filters.tag || '');
const sortBy = ref(props.filters.sort || 'created_at');
const sortDirection = ref(props.filters.direction || 'desc');
const perPage = ref(props.filters.per_page || '15');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// UI state
const showAdvancedFilters = ref(false);
const selectedPosts = ref<number[]>([]);
const selectAll = ref(false);
const showBulkActions = ref(false);

// Computed
const hasActiveFilters = computed(() => {
    return search.value || selectedStatus.value || selectedCategory.value ||
           selectedTag.value || dateFrom.value || dateTo.value ||
           sortBy.value !== 'created_at' || sortDirection.value !== 'desc';
});

// Watch selectAll checkbox
watch(selectAll, (value) => {
    if (value) {
        selectedPosts.value = props.posts.data.map(p => p.id);
    } else {
        selectedPosts.value = [];
    }
});

// Watch selectedPosts to update showBulkActions
watch(selectedPosts, (value) => {
    showBulkActions.value = value.length > 0;
}, { deep: true });

// Methods
const applyFilters = () => {
    const params: Record<string, string> = {};

    if (search.value) params.search = search.value;
    if (selectedStatus.value) params.status = selectedStatus.value;
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedTag.value) params.tag = selectedTag.value;
    if (sortBy.value !== 'created_at') params.sort = sortBy.value;
    if (sortDirection.value !== 'desc') params.direction = sortDirection.value;
    if (perPage.value !== '15') params.per_page = perPage.value;
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;

    router.get(route('admin.blog.posts.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    selectedStatus.value = '';
    selectedCategory.value = '';
    selectedTag.value = '';
    sortBy.value = 'created_at';
    sortDirection.value = 'desc';
    perPage.value = '15';
    dateFrom.value = '';
    dateTo.value = '';

    router.get(route('admin.blog.posts.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleSort = (column: string) => {
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = column;
        sortDirection.value = 'desc';
    }
    applyFilters();
};

const filterByStatus = (status: string) => {
    selectedStatus.value = status;
    applyFilters();
};

const deletePost = (id: number) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('admin.blog.posts.destroy', id));
    }
};

const bulkAction = (action: string) => {
    if (selectedPosts.value.length === 0) return;

    const actionLabels: Record<string, string> = {
        delete: 'delete',
        publish: 'publish',
        draft: 'move to draft',
        archive: 'archive',
    };

    if (confirm(`Are you sure you want to ${actionLabels[action]} ${selectedPosts.value.length} post(s)?`)) {
        router.post(route('admin.blog.posts.bulk-action'), {
            action: action,
            ids: selectedPosts.value,
        }, {
            onSuccess: () => {
                selectedPosts.value = [];
                selectAll.value = false;
            },
        });
    }
};

const togglePostSelection = (postId: number) => {
    const index = selectedPosts.value.indexOf(postId);
    if (index > -1) {
        selectedPosts.value.splice(index, 1);
    } else {
        selectedPosts.value.push(postId);
    }
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        published: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        archived: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return colors[status] || colors.draft;
};

const getStatusIcon = (status: string) => {
    const icons: Record<string, any> = {
        published: CheckCircle,
        draft: Clock,
        archived: Archive,
    };
    return icons[status] || Clock;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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
                        Manage and organize your blog content
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                    @click="clearFilters"
                    :class="[
                        'bg-white dark:bg-slate-800 rounded-lg border p-4 text-left transition-colors',
                        !selectedStatus ? 'border-primary ring-2 ring-primary/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">
                            <FileText class="h-5 w-5 text-slate-600 dark:text-slate-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.total }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Total Posts</p>
                        </div>
                    </div>
                </button>

                <button
                    @click="filterByStatus('published')"
                    :class="[
                        'bg-white dark:bg-slate-800 rounded-lg border p-4 text-left transition-colors',
                        selectedStatus === 'published' ? 'border-green-500 ring-2 ring-green-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.published }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Published</p>
                        </div>
                    </div>
                </button>

                <button
                    @click="filterByStatus('draft')"
                    :class="[
                        'bg-white dark:bg-slate-800 rounded-lg border p-4 text-left transition-colors',
                        selectedStatus === 'draft' ? 'border-yellow-500 ring-2 ring-yellow-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                            <Clock class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.draft }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Drafts</p>
                        </div>
                    </div>
                </button>

                <button
                    @click="filterByStatus('archived')"
                    :class="[
                        'bg-white dark:bg-slate-800 rounded-lg border p-4 text-left transition-colors',
                        selectedStatus === 'archived' ? 'border-gray-500 ring-2 ring-gray-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <Archive class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ stats.archived }}</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">Archived</p>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 space-y-4">
                <!-- Main Search Row -->
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Search Input -->
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by title, content, category, or tags..."
                                class="w-full pl-10 pr-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>

                    <!-- Quick Filters -->
                    <div class="flex gap-2">
                        <select
                            v-model="selectedCategory"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                            @change="applyFilters"
                        >
                            <option value="">All Categories</option>
                            <option v-for="category in categories" :key="category.id" :value="category.slug">
                                {{ category.name }}
                            </option>
                        </select>

                        <select
                            v-model="selectedStatus"
                            class="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                            @change="applyFilters"
                        >
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Archived</option>
                        </select>

                        <button
                            @click="applyFilters"
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                        >
                            <Search class="h-4 w-4" />
                        </button>

                        <button
                            @click="showAdvancedFilters = !showAdvancedFilters"
                            :class="[
                                'px-3 py-2 rounded-lg transition-colors flex items-center gap-2',
                                showAdvancedFilters
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                            ]"
                        >
                            <SlidersHorizontal class="h-4 w-4" />
                            <span class="hidden sm:inline text-sm">Advanced</span>
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div v-if="showAdvancedFilters" class="pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Tag Filter -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Tag
                            </label>
                            <select
                                v-model="selectedTag"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                                @change="applyFilters"
                            >
                                <option value="">All Tags</option>
                                <option v-for="tag in tags" :key="tag.id" :value="tag.slug">
                                    {{ tag.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Date From
                            </label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                                @change="applyFilters"
                            />
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Date To
                            </label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                                @change="applyFilters"
                            />
                        </div>

                        <!-- Per Page -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Per Page
                            </label>
                            <select
                                v-model="perPage"
                                class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm"
                                @change="applyFilters"
                            >
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Active Filters & Clear -->
                <div v-if="hasActiveFilters" class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex flex-wrap gap-2">
                        <span v-if="search" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            Search: {{ search }}
                            <button @click="search = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="selectedStatus" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            Status: {{ selectedStatus }}
                            <button @click="selectedStatus = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="selectedCategory" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            Category: {{ categories.find(c => c.slug === selectedCategory)?.name }}
                            <button @click="selectedCategory = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="selectedTag" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            Tag: {{ tags.find(t => t.slug === selectedTag)?.name }}
                            <button @click="selectedTag = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="dateFrom" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            From: {{ dateFrom }}
                            <button @click="dateFrom = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                        <span v-if="dateTo" class="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-sm">
                            To: {{ dateTo }}
                            <button @click="dateTo = ''; applyFilters()" class="hover:text-red-500">
                                <X class="h-3 w-3" />
                            </button>
                        </span>
                    </div>
                    <button
                        @click="clearFilters"
                        class="inline-flex items-center gap-1 px-3 py-1 text-sm text-slate-600 dark:text-slate-400 hover:text-red-500 transition-colors"
                    >
                        <RefreshCw class="h-4 w-4" />
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Bulk Actions Bar -->
            <div
                v-if="showBulkActions"
                class="bg-primary/10 border border-primary/20 rounded-lg p-4 flex items-center justify-between"
            >
                <span class="text-sm font-medium text-primary">
                    {{ selectedPosts.length }} post(s) selected
                </span>
                <div class="flex items-center gap-2">
                    <button
                        @click="bulkAction('publish')"
                        class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        Publish
                    </button>
                    <button
                        @click="bulkAction('draft')"
                        class="px-3 py-1.5 text-sm bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                    >
                        Draft
                    </button>
                    <button
                        @click="bulkAction('archive')"
                        class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    >
                        Archive
                    </button>
                    <button
                        @click="bulkAction('delete')"
                        class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                    >
                        Delete
                    </button>
                    <button
                        @click="selectedPosts = []; selectAll = false"
                        class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Posts Table -->
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input
                                        v-model="selectAll"
                                        type="checkbox"
                                        class="rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button
                                        @click="toggleSort('title')"
                                        class="flex items-center gap-1 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-slate-200"
                                    >
                                        Title
                                        <ChevronUp v-if="sortBy === 'title' && sortDirection === 'asc'" class="h-3 w-3" />
                                        <ChevronDown v-else-if="sortBy === 'title'" class="h-3 w-3" />
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button
                                        @click="toggleSort('views_count')"
                                        class="flex items-center gap-1 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-slate-200"
                                    >
                                        Views
                                        <ChevronUp v-if="sortBy === 'views_count' && sortDirection === 'asc'" class="h-3 w-3" />
                                        <ChevronDown v-else-if="sortBy === 'views_count'" class="h-3 w-3" />
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Author
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button
                                        @click="toggleSort('created_at')"
                                        class="flex items-center gap-1 text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider hover:text-slate-700 dark:hover:text-slate-200"
                                    >
                                        Date
                                        <ChevronUp v-if="sortBy === 'created_at' && sortDirection === 'asc'" class="h-3 w-3" />
                                        <ChevronDown v-else-if="sortBy === 'created_at'" class="h-3 w-3" />
                                    </button>
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr
                                v-for="post in posts.data"
                                :key="post.id"
                                :class="[
                                    'hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors',
                                    selectedPosts.includes(post.id) ? 'bg-primary/5' : ''
                                ]"
                            >
                                <td class="px-4 py-4">
                                    <input
                                        type="checkbox"
                                        :checked="selectedPosts.includes(post.id)"
                                        @change="togglePostSelection(post.id)"
                                        class="rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary"
                                    />
                                </td>
                                <td class="px-4 py-4">
                                    <div>
                                        <a
                                            :href="route('admin.blog.posts.edit', post.id)"
                                            class="text-sm font-medium text-slate-900 dark:text-white hover:text-primary transition-colors"
                                        >
                                            {{ post.title }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                                /{{ post.slug }}
                                            </span>
                                            <span v-if="post.reading_time" class="text-xs text-slate-400">
                                                {{ post.reading_time }} min read
                                            </span>
                                        </div>
                                        <!-- Tags -->
                                        <div v-if="post.tags && post.tags.length > 0" class="flex flex-wrap gap-1 mt-2">
                                            <span
                                                v-for="tag in post.tags.slice(0, 3)"
                                                :key="tag.id"
                                                class="px-1.5 py-0.5 text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded"
                                            >
                                                {{ tag.name }}
                                            </span>
                                            <span
                                                v-if="post.tags.length > 3"
                                                class="px-1.5 py-0.5 text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded"
                                            >
                                                +{{ post.tags.length - 3 }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        v-if="post.category"
                                        class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded"
                                    >
                                        {{ post.category.name }}
                                    </span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span
                                        :class="getStatusColor(post.status)"
                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full"
                                    >
                                        <component :is="getStatusIcon(post.status)" class="h-3 w-3" />
                                        {{ post.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-600 dark:text-slate-400">
                                    {{ post.views_count?.toLocaleString() || 0 }}
                                </td>
                                <td class="px-4 py-4 text-sm text-slate-900 dark:text-white">
                                    {{ post.author?.name || '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-slate-900 dark:text-white">
                                        {{ formatDate(post.created_at) }}
                                    </div>
                                    <div v-if="post.published_at" class="text-xs text-slate-500">
                                        Published: {{ formatDate(post.published_at) }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a
                                            :href="route('admin.blog.posts.edit', post.id)"
                                            class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                            title="Edit"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </a>
                                        <button
                                            @click="deletePost(post.id)"
                                            class="p-2 text-slate-600 hover:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="posts.data.length === 0" class="text-center py-12">
                    <FileText class="h-12 w-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">No posts found</h3>
                    <p class="text-slate-500 dark:text-slate-400 mb-4">
                        {{ hasActiveFilters ? 'Try adjusting your filters' : 'Get started by creating your first post' }}
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <button
                            v-if="hasActiveFilters"
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                        >
                            <RefreshCw class="h-4 w-4 mr-2" />
                            Clear Filters
                        </button>
                        <a
                            :href="route('admin.blog.posts.create')"
                            class="inline-flex items-center px-4 py-2 text-sm bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                        >
                            <Plus class="h-4 w-4 mr-2" />
                            Create Post
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="posts.meta && posts.meta.total > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    Showing <span class="font-medium">{{ posts.meta.from }}</span> to
                    <span class="font-medium">{{ posts.meta.to }}</span> of
                    <span class="font-medium">{{ posts.meta.total }}</span> posts
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="link in posts.links" :key="link.label">
                        <a
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm transition-colors',
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700'
                            ]"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-sm text-slate-400 dark:text-slate-600 cursor-not-allowed"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
