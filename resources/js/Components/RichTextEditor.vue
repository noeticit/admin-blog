<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import {
    Bold,
    Italic,
    Underline,
    Strikethrough,
    List,
    ListOrdered,
    Quote,
    Code,
    Link,
    Image,
    Heading1,
    Heading2,
    Heading3,
    AlignLeft,
    AlignCenter,
    AlignRight,
    Undo,
    Redo,
    Maximize2,
    Minimize2,
} from 'lucide-vue-next';

interface Props {
    modelValue: string;
    placeholder?: string;
    disabled?: boolean;
    minHeight?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Start writing...',
    disabled: false,
    minHeight: '400px',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editorRef = ref<HTMLDivElement | null>(null);
const isFullscreen = ref(false);
const showLinkModal = ref(false);
const linkUrl = ref('');
const linkText = ref('');

// Toolbar button configuration
const toolbarButtons = [
    { group: 'history', items: [
        { icon: Undo, command: 'undo', title: 'Undo' },
        { icon: Redo, command: 'redo', title: 'Redo' },
    ]},
    { group: 'headings', items: [
        { icon: Heading1, command: 'formatBlock', value: 'h1', title: 'Heading 1' },
        { icon: Heading2, command: 'formatBlock', value: 'h2', title: 'Heading 2' },
        { icon: Heading3, command: 'formatBlock', value: 'h3', title: 'Heading 3' },
    ]},
    { group: 'formatting', items: [
        { icon: Bold, command: 'bold', title: 'Bold (Ctrl+B)' },
        { icon: Italic, command: 'italic', title: 'Italic (Ctrl+I)' },
        { icon: Underline, command: 'underline', title: 'Underline (Ctrl+U)' },
        { icon: Strikethrough, command: 'strikeThrough', title: 'Strikethrough' },
    ]},
    { group: 'lists', items: [
        { icon: List, command: 'insertUnorderedList', title: 'Bullet List' },
        { icon: ListOrdered, command: 'insertOrderedList', title: 'Numbered List' },
    ]},
    { group: 'blocks', items: [
        { icon: Quote, command: 'formatBlock', value: 'blockquote', title: 'Quote' },
        { icon: Code, command: 'formatBlock', value: 'pre', title: 'Code Block' },
    ]},
    { group: 'alignment', items: [
        { icon: AlignLeft, command: 'justifyLeft', title: 'Align Left' },
        { icon: AlignCenter, command: 'justifyCenter', title: 'Align Center' },
        { icon: AlignRight, command: 'justifyRight', title: 'Align Right' },
    ]},
    { group: 'insert', items: [
        { icon: Link, command: 'createLink', title: 'Insert Link', modal: true },
        { icon: Image, command: 'insertImage', title: 'Insert Image', modal: true },
    ]},
];

// Execute command
const execCommand = (command: string, value?: string) => {
    if (command === 'createLink') {
        showLinkModal.value = true;
        const selection = window.getSelection();
        if (selection && selection.toString()) {
            linkText.value = selection.toString();
        }
        return;
    }

    if (command === 'insertImage') {
        const url = prompt('Enter image URL:');
        if (url) {
            document.execCommand('insertImage', false, url);
            updateContent();
        }
        return;
    }

    document.execCommand(command, false, value);
    editorRef.value?.focus();
    updateContent();
};

// Insert link
const insertLink = () => {
    if (linkUrl.value) {
        document.execCommand('createLink', false, linkUrl.value);
        updateContent();
    }
    showLinkModal.value = false;
    linkUrl.value = '';
    linkText.value = '';
};

// Update content
const updateContent = () => {
    if (editorRef.value) {
        emit('update:modelValue', editorRef.value.innerHTML);
    }
};

// Handle paste - clean up pasted content
const handlePaste = (e: ClipboardEvent) => {
    e.preventDefault();
    const text = e.clipboardData?.getData('text/plain') || '';
    document.execCommand('insertText', false, text);
    updateContent();
};

// Handle keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
    if (e.ctrlKey || e.metaKey) {
        switch (e.key.toLowerCase()) {
            case 'b':
                e.preventDefault();
                execCommand('bold');
                break;
            case 'i':
                e.preventDefault();
                execCommand('italic');
                break;
            case 'u':
                e.preventDefault();
                execCommand('underline');
                break;
        }
    }
};

// Toggle fullscreen
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

// Initialize editor content
onMounted(() => {
    if (editorRef.value && props.modelValue) {
        editorRef.value.innerHTML = props.modelValue;
    }
});

// Watch for external content changes
watch(() => props.modelValue, (newValue) => {
    if (editorRef.value && editorRef.value.innerHTML !== newValue) {
        editorRef.value.innerHTML = newValue;
    }
});
</script>

<template>
    <div
        :class="[
            'border border-slate-300 dark:border-slate-600 rounded-lg overflow-hidden bg-white dark:bg-slate-800',
            isFullscreen ? 'fixed inset-4 z-50 flex flex-col' : '',
        ]"
    >
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 p-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
            <template v-for="(group, groupIndex) in toolbarButtons" :key="group.group">
                <!-- Separator -->
                <div
                    v-if="groupIndex > 0"
                    class="w-px h-6 bg-slate-300 dark:bg-slate-600 mx-1"
                />

                <!-- Buttons -->
                <button
                    v-for="button in group.items"
                    :key="button.command + (button.value || '')"
                    type="button"
                    @click="execCommand(button.command, button.value)"
                    :disabled="disabled"
                    :title="button.title"
                    class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <component :is="button.icon" class="h-4 w-4" />
                </button>
            </template>

            <!-- Fullscreen toggle -->
            <div class="flex-1" />
            <button
                type="button"
                @click="toggleFullscreen"
                :title="isFullscreen ? 'Exit Fullscreen' : 'Fullscreen'"
                class="p-1.5 rounded hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
            >
                <Minimize2 v-if="isFullscreen" class="h-4 w-4" />
                <Maximize2 v-else class="h-4 w-4" />
            </button>
        </div>

        <!-- Editor -->
        <div
            ref="editorRef"
            contenteditable="true"
            :class="[
                'prose prose-slate dark:prose-invert max-w-none p-4 focus:outline-none overflow-y-auto',
                isFullscreen ? 'flex-1' : '',
            ]"
            :style="{ minHeight: isFullscreen ? 'auto' : minHeight }"
            :data-placeholder="placeholder"
            @input="updateContent"
            @paste="handlePaste"
            @keydown="handleKeydown"
        />

        <!-- Link Modal -->
        <div
            v-if="showLinkModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showLinkModal = false"
        >
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-4">
                    Insert Link
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            URL
                        </label>
                        <input
                            v-model="linkUrl"
                            type="url"
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                            placeholder="https://example.com"
                        />
                    </div>

                    <div v-if="linkText">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Text
                        </label>
                        <input
                            v-model="linkText"
                            type="text"
                            disabled
                            class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-white"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        type="button"
                        @click="showLinkModal = false"
                        class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="insertLink"
                        :disabled="!linkUrl"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors disabled:opacity-50"
                    >
                        Insert
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
[contenteditable]:empty:before {
    content: attr(data-placeholder);
    color: #9ca3af;
    pointer-events: none;
}

[contenteditable] {
    min-height: v-bind(minHeight);
}
</style>
