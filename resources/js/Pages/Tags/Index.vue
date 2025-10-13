<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Edit, Trash2, Plus } from 'lucide-vue-next';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Tag {
    id: number;
    name: string;
    slug: string;
    posts_count: number;
}

interface Props {
    tags: Tag[];
}

const props = defineProps<Props>();

const deleteTag = (id: number) => {
    if (confirm('Are you sure you want to delete this tag?')) {
        router.delete(route('admin.blog.tags.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tags</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Manage blog tags
                    </p>
                </div>
                <a
                    :href="route('admin.blog.tags.create')"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                    <Plus class="h-4 w-4 mr-2" />
                    New Tag
                </a>
            </div>

            <!-- Tags List -->
            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Slug
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Posts
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-for="tag in tags" :key="tag.id" class="hover:bg-slate-50 dark:hover:bg-slate-900">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900 dark:text-white">
                                    {{ tag.name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ tag.slug }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-900 dark:text-white">
                                {{ tag.posts_count }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        :href="route('admin.blog.tags.edit', tag.id)"
                                        class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </a>
                                    <button
                                        @click="deleteTag(tag.id)"
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
                <div v-if="tags.length === 0" class="text-center py-12">
                    <p class="text-slate-500 dark:text-slate-400">No tags found</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
