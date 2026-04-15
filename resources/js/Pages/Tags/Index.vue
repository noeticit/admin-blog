<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Edit, Trash2, Plus, X } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface Tag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    posts_count: number;
}

interface Props {
    tags: Tag[];
}

const props = defineProps<Props>();

const showModal = ref(false);
const editingTag = ref<Tag | null>(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
});

const openCreateModal = () => {
    editingTag.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (tag: Tag) => {
    editingTag.value = tag;
    form.name = tag.name;
    form.slug = tag.slug;
    form.description = tag.description || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    editingTag.value = null;
};

const generateSlug = () => {
    form.slug = form.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

const submit = () => {
    if (editingTag.value) {
        form.put(route('admin.blog.tags.update', editingTag.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.blog.tags.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTag = (id: number) => {
    if (confirm('Are you sure you want to delete this tag?')) {
        router.delete(route('admin.blog.tags.destroy', id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Categories</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Manage blog tags
                    </p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                    <Plus class="h-4 w-4 mr-2" />
                    New Tag
                </button>
            </div>

            <!-- Categories List -->
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
                                <div v-if="tag.description" class="text-sm text-slate-500">
                                    {{ tag.description }}
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
                                    <button
                                        @click="openEditModal(tag)"
                                        class="p-2 text-slate-600 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </button>
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

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
            <div class="flex min-h-full items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeModal"></div>

                <!-- Modal Content -->
                <div class="relative bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md">
                    <form @submit.prevent="submit" class="p-6 space-y-4">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                                {{ editingTag ? 'Edit Tag' : 'Create Tag' }}
                            </h2>
                            <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600">
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Name *
                            </label>
                            <input
                                v-model="form.name"
                                @input="generateSlug"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Slug -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Slug
                            </label>
                            <input
                                v-model="form.slug"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            />
                            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>

                        <!--  -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50"
                            >
                                {{ form.processing ? 'Saving...' : (editingTag ? 'Update' : 'Create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
