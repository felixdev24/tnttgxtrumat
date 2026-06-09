<script lang="ts">
    import DashboardLayout from '../../../layouts/DashboardLayout.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import { Editor } from '@tiptap/core';
    import StarterKit from '@tiptap/starter-kit';
    import Image from '@tiptap/extension-image';
    import TiptapLink from '@tiptap/extension-link';
    import TextAlign from '@tiptap/extension-text-align';
    import Placeholder from '@tiptap/extension-placeholder';

    let {
        categories = [],
    }: {
        categories?: string[];
    } = $props();

    const form = useForm({
        title: '',
        content: '',
        status: 'draft',
        category: categories[0] ?? '',
        tags: [],
        cover_image: null as File | null | string,
        scheduled_at: null
    });

    let element: HTMLElement;
    let editor: Editor;
    let fileInput: HTMLInputElement;
    let coverPreview: string | null = $state(null);
    let isDraggingOver = $state(false);

    function handleCoverFile(file: File) {
        if (!file.type.startsWith('image/')) {
            alert('Vui lòng chọn file hình ảnh.');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            alert('Ảnh bìa không được vượt quá 5MB.');
            return;
        }
        form.cover_image = file;
        coverPreview = URL.createObjectURL(file);
    }

    function onFileSelected(e: Event) {
        const input = e.target as HTMLInputElement;
        if (input.files && input.files[0]) {
            handleCoverFile(input.files[0]);
        }
    }

    function onDrop(e: DragEvent) {
        e.preventDefault();
        isDraggingOver = false;
        if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
            handleCoverFile(e.dataTransfer.files[0]);
        }
    }

    function onDragOver(e: DragEvent) {
        e.preventDefault();
        isDraggingOver = true;
    }

    function onDragLeave(e: DragEvent) {
        e.preventDefault();
        isDraggingOver = false;
    }

    function removeCover() {
        form.cover_image = null;
        if (coverPreview) {
            URL.revokeObjectURL(coverPreview);
            coverPreview = null;
        }
        if (fileInput) {
            fileInput.value = '';
        }
    }

    onMount(() => {
        editor = new Editor({
            element: element,
            extensions: [
                StarterKit,
                Image,
                TiptapLink,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                Placeholder.configure({
                    placeholder: 'Bắt đầu viết nội dung tại đây...',
                }),
            ],
            content: form.content,
            onTransaction: () => {
                // force re-render so buttons can update state
                editor = editor;
            },
            onUpdate: ({ editor }) => {
                form.content = editor.getHTML();
            },
            editorProps: {
                attributes: {
                    class: 'w-full min-h-[500px] h-full bg-transparent border-none focus:ring-0 font-body-lg text-body-lg resize-none placeholder:text-outline-variant outline-none prose max-w-none prose-sm dark:prose-invert',
                },
            },
        });
    });

    onDestroy(() => {
        if (editor) {
            editor.destroy();
        }
        if (coverPreview) {
            URL.revokeObjectURL(coverPreview);
        }
    });

    function toggleBold() { editor.chain().focus().toggleBold().run(); }
    function toggleItalic() { editor.chain().focus().toggleItalic().run(); }
    function toggleStrike() { editor.chain().focus().toggleStrike().run(); }
    function setParagraph() { editor.chain().focus().setParagraph().run(); }
    function toggleHeading(level: 1 | 2 | 3) { editor.chain().focus().toggleHeading({ level }).run(); }
    function toggleBulletList() { editor.chain().focus().toggleBulletList().run(); }
    function toggleOrderedList() { editor.chain().focus().toggleOrderedList().run(); }
    function toggleBlockquote() { editor.chain().focus().toggleBlockquote().run(); }

    function setAlign(alignment: 'left' | 'center' | 'right') {
        editor.chain().focus().setTextAlign(alignment).run();
    }

    function addImage() {
        const url = window.prompt('URL hình ảnh:');
        if (url) {
            editor.chain().focus().setImage({ src: url }).run();
        }
    }

    function submitPost(status: 'draft' | 'published' = 'draft') {
        form.status = status;
        form.post('/dashboard/posts');
    }
</script>

<svelte:head>
    <title>Tạo bài viết mới - Dashboard</title>
</svelte:head>

<DashboardLayout>
    <div class="px-margin-mobile md:px-margin-desktop pb-section-gap pt-stack-lg">
        <div class="max-w-container-max mx-auto">
            <!-- Page Header Actions -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-stack-lg gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-on-surface-variant text-sm mb-1">
                        <Link href="/dashboard/posts">Truyền Thông</Link>
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                        <span class="text-primary font-medium">Tạo bài viết mới</span>
                    </nav>
                    <h1 class="font-headline-lg text-headline-lg text-primary">Viết Bài Mới</h1>
                </div>
                <div class="flex items-center gap-stack-md">
                    <button onclick={() => submitPost('draft')} disabled={form.processing} class="px-6 py-3 rounded-full border-2 border-primary text-primary font-label-bold hover:bg-primary/5 transition-all active:scale-95">
                        Lưu bản nháp
                    </button>
                    <button onclick={() => submitPost('published')} disabled={form.processing} class="px-8 py-3 rounded-full bg-primary text-on-primary font-label-bold duolingo-button border-b-4 border-primary-container hover:brightness-110 transition-all active:scale-95 disabled:opacity-50">
                        Đăng bài
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
                <!-- Editor Canvas -->
                <div class="lg:col-span-8 flex flex-col gap-gutter">
                    <!-- Cover Image Upload -->
                    <input bind:this={fileInput} type="file" accept="image/*" class="hidden" onchange={onFileSelected} id="cover-image-input" />

                    {#if coverPreview}
                        <!-- Preview State -->
                        <div class="glass-card rounded-xl overflow-hidden relative group">
                            <img src={coverPreview} alt="Ảnh bìa" class="w-full h-[300px] object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4">
                                <button onclick={() => fileInput.click()} class="px-4 py-2 bg-white/90 text-on-surface rounded-full font-label-bold hover:bg-white transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">swap_horiz</span> Đổi ảnh
                                </button>
                                <button onclick={removeCover} class="px-4 py-2 bg-error/90 text-on-error rounded-full font-label-bold hover:bg-error transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">delete</span> Xóa
                                </button>
                            </div>
                            {#if form.errors.cover_image}<span class="absolute bottom-2 left-2 text-error text-sm bg-error-container px-2 py-1 rounded">{form.errors.cover_image}</span>{/if}
                        </div>
                    {:else}
                        <!-- Drop Zone State -->
                        <!-- svelte-ignore a11y_no_static_element_interactions -->
                        <div
                            class="glass-card rounded-xl p-stack-md flex flex-col items-center justify-center min-h-[300px] border-2 border-dashed transition-all cursor-pointer group {isDraggingOver ? 'border-primary bg-primary/5 scale-[1.01]' : 'border-outline-variant hover:border-primary/50'}"
                            onclick={() => fileInput.click()}
                            ondrop={onDrop}
                            ondragover={onDragOver}
                            ondragleave={onDragLeave}
                            role="button"
                            tabindex="0"
                            onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); }}
                        >
                            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform {isDraggingOver ? 'scale-110 bg-primary/20' : ''}">
                                <span class="material-symbols-outlined text-primary text-4xl">{isDraggingOver ? 'file_download' : 'image'}</span>
                            </div>
                            <h3 class="font-title-md text-primary">{isDraggingOver ? 'Thả ảnh tại đây' : 'Thêm ảnh bìa'}</h3>
                            <p class="text-on-surface-variant mt-2 text-center">Kéo và thả ảnh tại đây hoặc nhấp để chọn tệp<br/><span class="text-xs">(Kích thước đề nghị: 1200x630px)</span></p>
                            {#if form.errors.cover_image}<span class="text-error text-sm mt-2">{form.errors.cover_image}</span>{/if}
                        </div>
                    {/if}

                    <!-- Title Input -->
                    <div class="glass-card rounded-xl p-stack-lg">
                        <input bind:value={form.title} class="w-full bg-transparent border-none focus:ring-0 font-headline-lg text-headline-lg placeholder:text-outline-variant outline-none" placeholder="Tiêu đề bài viết..." type="text"/>
                        {#if form.errors.title}<span class="text-error text-sm">{form.errors.title}</span>{/if}
                    </div>

                    <!-- Rich Text Editor -->
                    <div class="glass-card rounded-xl flex flex-col min-h-[600px] overflow-hidden">
                        <!-- Toolbar -->
                        {#if editor}
                            <div class="flex flex-wrap items-center gap-1 p-3 bg-surface-container-low border-b border-outline-variant/20">
                                <button onclick={toggleBold} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('bold') ? 'bg-surface-variant' : ''}" title="Bold"><span class="material-symbols-outlined">format_bold</span></button>
                                <button onclick={toggleItalic} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('italic') ? 'bg-surface-variant' : ''}" title="Italic"><span class="material-symbols-outlined">format_italic</span></button>
                                <button onclick={toggleStrike} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('strike') ? 'bg-surface-variant' : ''}" title="Strikethrough"><span class="material-symbols-outlined">format_strikethrough</span></button>
                                
                                <div class="w-[1px] h-6 bg-outline-variant/30 mx-1"></div>
                                
                                <button onclick={() => toggleHeading(1)} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('heading', { level: 1 }) ? 'bg-surface-variant' : ''}" title="H1"><span class="material-symbols-outlined">format_h1</span></button>
                                <button onclick={() => toggleHeading(2)} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('heading', { level: 2 }) ? 'bg-surface-variant' : ''}" title="H2"><span class="material-symbols-outlined">format_h2</span></button>
                                
                                <div class="w-[1px] h-6 bg-outline-variant/30 mx-1"></div>
                                
                                <button onclick={toggleBulletList} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('bulletList') ? 'bg-surface-variant' : ''}" title="List"><span class="material-symbols-outlined">format_list_bulleted</span></button>
                                <button onclick={toggleOrderedList} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('orderedList') ? 'bg-surface-variant' : ''}" title="Numbered List"><span class="material-symbols-outlined">format_list_numbered</span></button>
                                <button onclick={toggleBlockquote} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive('blockquote') ? 'bg-surface-variant' : ''}" title="Quote"><span class="material-symbols-outlined">format_quote</span></button>
                                
                                <div class="w-[1px] h-6 bg-outline-variant/30 mx-1"></div>
                                
                                <button onclick={addImage} class="p-2 hover:bg-surface-variant rounded-lg transition-all" title="Image"><span class="material-symbols-outlined">add_photo_alternate</span></button>
                                
                                <div class="w-[1px] h-6 bg-outline-variant/30 mx-1"></div>
                                
                                <button onclick={() => setAlign('left')} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive({ textAlign: 'left' }) ? 'bg-surface-variant' : ''}" title="Align Left"><span class="material-symbols-outlined">format_align_left</span></button>
                                <button onclick={() => setAlign('center')} class="p-2 hover:bg-surface-variant rounded-lg transition-all {editor.isActive({ textAlign: 'center' }) ? 'bg-surface-variant' : ''}" title="Align Center"><span class="material-symbols-outlined">format_align_center</span></button>
                            </div>
                        {/if}

                        <!-- Content Area -->
                        <div class="p-stack-lg flex-1">
                            <div bind:this={element} class="w-full h-full min-h-[500px]"></div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Metadata -->
                <div class="lg:col-span-4 flex flex-col gap-gutter">
                    <!-- Categories & Tags -->
                    <div class="glass-card rounded-xl p-stack-lg flex flex-col gap-stack-md">
                        <h3 class="font-title-md text-primary flex items-center gap-2">
                            <span class="material-symbols-outlined">category</span> Danh mục & Thẻ
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block font-label-bold text-on-surface-variant mb-2">Danh mục chính</label>
                                <select bind:value={form.category} class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary/20 appearance-none outline-none">
                                    {#each categories as category (category)}
                                        <option value={category}>{category}</option>
                                    {:else}
                                        <option value="">Chưa có danh mục</option>
                                    {/each}
                                </select>
                            </div>
                            <div>
                                <label class="block font-label-bold text-on-surface-variant mb-2">Hẹn giờ đăng</label>
                                <div class="relative">
                                    <input bind:value={form.scheduled_at} class="w-full bg-surface-container-low border-none rounded-xl px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary/20 outline-none" type="datetime-local"/>
                                    <span class="material-symbols-outlined absolute right-4 top-3 text-outline pointer-events-none">calendar_today</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</DashboardLayout>

<style>
    /* Basic Prosemirror styling to make it look like an editor */
    :global(.ProseMirror) {
        outline: none;
    }
    :global(.ProseMirror p.is-editor-empty:first-child::before) {
        content: attr(data-placeholder);
        float: left;
        color: #adb5bd;
        pointer-events: none;
        height: 0;
    }
</style>
