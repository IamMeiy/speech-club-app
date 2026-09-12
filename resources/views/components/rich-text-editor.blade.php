@props([
    'placeholder' => 'Additional agenda, meeting notes, action items, or announcements…',
])

@php
    $wireModel = $attributes->wire('model');
    $wireModelValue = $wireModel->value() ?: 'notes';
@endphp

<div
    x-data="{
        content: @entangle($attributes->wire('model')),
        isBold: false,
        isItalic: false,
        isUnderline: false,
        isStrike: false,
        isUl: false,
        isOl: false,
        isQuote: false,
        currentBlock: 'p',
        wordCount: 0,
        charCount: 0,
        init() {
            // Set initial content from Livewire if available
            if (this.content && this.$refs.editor) {
                this.$refs.editor.innerHTML = this.content;
            }
            this.updateStats();

            // Watch for changes coming from Livewire (e.g. edit form hydration)
            this.$watch('content', value => {
                if (this.$refs.editor && (value || '') !== this.$refs.editor.innerHTML) {
                    this.$refs.editor.innerHTML = value || '';
                    this.updateStats();
                }
            });
        },
        updateContent() {
            if (!this.$refs.editor) return;
            let html = this.$refs.editor.innerHTML;
            let text = this.$refs.editor.innerText.trim();

            // Prevent stray empty tags like <p><br></p> or <div><br></div> from filling DB
            if (!text && !html.includes('<img') && !html.includes('<hr')) {
                this.content = '';
            } else {
                this.content = html;
            }
            this.updateStats();
            this.checkActiveStates();
        },
        updateStats() {
            if (!this.$refs.editor) return;
            let text = (this.$refs.editor.innerText || '').trim();
            this.charCount = text.length;
            this.wordCount = text ? text.split(/\s+/).filter(Boolean).length : 0;
        },
        exec(command, value = null) {
            this.$refs.editor.focus();
            document.execCommand(command, false, value);
            this.updateContent();
            this.checkActiveStates();
        },
        setHeading(tag) {
            this.$refs.editor.focus();
            // Toggle back to paragraph if already active
            if (this.currentBlock === tag.toLowerCase()) {
                document.execCommand('formatBlock', false, '<p>');
            } else {
                document.execCommand('formatBlock', false, '<' + tag + '>');
            }
            this.updateContent();
            this.checkActiveStates();
        },
        addLink() {
            this.$refs.editor.focus();
            let selectedText = window.getSelection().toString();
            let url = prompt('Enter website link URL (e.g. https://toastmasters.org):', 'https://');
            if (url && url !== 'https://' && url.trim() !== '') {
                let trimmed = url.trim();
                if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://') && !trimmed.startsWith('mailto:')) {
                    trimmed = 'https://' + trimmed;
                }
                document.execCommand('createLink', false, trimmed);
                this.updateContent();
            }
        },
        checkActiveStates() {
            try {
                this.isBold = document.queryCommandState('bold');
                this.isItalic = document.queryCommandState('italic');
                this.isUnderline = document.queryCommandState('underline');
                this.isStrike = document.queryCommandState('strikeThrough');
                this.isUl = document.queryCommandState('insertUnorderedList');
                this.isOl = document.queryCommandState('insertOrderedList');
                
                let block = document.queryCommandValue('formatBlock') || 'p';
                this.currentBlock = block.toLowerCase().replace(/[^a-z0-9]/g, '');
            } catch (e) {}
        }
    }"
    wire:ignore
    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-850 shadow-xs focus-within:ring-2 focus-within:ring-primary-500/80 focus-within:border-primary-500 transition-all overflow-hidden']) }}
>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50/90 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-700/80 select-none">
        
        {{-- Block format buttons (Paragraph, H2, H3) --}}
        <div class="flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-0.5 shadow-xs">
            <button
                type="button"
                @click.prevent="setHeading('p')"
                :class="currentBlock === 'p' || currentBlock === 'div' || !currentBlock ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                class="px-2 py-1 text-xs rounded-lg transition-colors"
                title="Normal Paragraph"
            >
                Normal
            </button>
            <button
                type="button"
                @click.prevent="setHeading('h2')"
                :class="currentBlock === 'h2' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                class="px-2 py-1 text-xs rounded-lg transition-colors font-bold"
                title="Heading 2"
            >
                H2
            </button>
            <button
                type="button"
                @click.prevent="setHeading('h3')"
                :class="currentBlock === 'h3' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white'"
                class="px-2 py-1 text-xs rounded-lg transition-colors font-semibold"
                title="Heading 3"
            >
                H3
            </button>
        </div>

        {{-- Divider --}}
        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>

        {{-- Inline text styles: Bold, Italic, Underline, Strikethrough --}}
        <button
            type="button"
            @click.prevent="exec('bold')"
            :class="isBold ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors font-bold w-8 h-8 flex items-center justify-center text-xs"
            title="Bold (Ctrl+B)"
        >
            <span class="font-extrabold text-sm">B</span>
        </button>

        <button
            type="button"
            @click.prevent="exec('italic')"
            :class="isItalic ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors italic w-8 h-8 flex items-center justify-center text-xs"
            title="Italic (Ctrl+I)"
        >
            <span class="font-serif italic font-semibold text-sm">I</span>
        </button>

        <button
            type="button"
            @click.prevent="exec('underline')"
            :class="isUnderline ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors underline w-8 h-8 flex items-center justify-center text-xs"
            title="Underline (Ctrl+U)"
        >
            <span class="underline font-semibold text-sm">U</span>
        </button>

        <button
            type="button"
            @click.prevent="exec('strikeThrough')"
            :class="isStrike ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors line-through w-8 h-8 flex items-center justify-center text-xs"
            title="Strikethrough"
        >
            <span class="line-through text-xs font-semibold">S</span>
        </button>

        {{-- Divider --}}
        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>

        {{-- Bullet List --}}
        <button
            type="button"
            @click.prevent="exec('insertUnorderedList')"
            :class="isUl ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors w-8 h-8 flex items-center justify-center"
            title="Bullet List"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/>
            </svg>
        </button>

        {{-- Numbered List --}}
        <button
            type="button"
            @click.prevent="exec('insertOrderedList')"
            :class="isOl ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors w-8 h-8 flex items-center justify-center"
            title="Numbered List"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h1v4H3m0 2h1a1 1 0 011 1v1a1 1 0 01-1 1H3m0 2h2"/>
            </svg>
        </button>

        {{-- Quote --}}
        <button
            type="button"
            @click.prevent="setHeading('blockquote')"
            :class="currentBlock === 'blockquote' ? 'bg-primary-100 text-primary-700 dark:bg-primary-950/80 dark:text-primary-300 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white'"
            class="p-1.5 rounded-xl transition-colors w-8 h-8 flex items-center justify-center"
            title="Blockquote"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/>
            </svg>
        </button>

        {{-- Divider --}}
        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>

        {{-- Link --}}
        <button
            type="button"
            @click.prevent="addLink()"
            class="p-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center"
            title="Insert Link"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
        </button>

        {{-- Horizontal Rule --}}
        <button
            type="button"
            @click.prevent="exec('insertHorizontalRule')"
            class="p-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center"
            title="Insert Horizontal Divider"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
            </svg>
        </button>

        {{-- Clear Formatting --}}
        <button
            type="button"
            @click.prevent="exec('removeFormat')"
            class="p-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center"
            title="Clear Formatting"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Divider --}}
        <div class="h-4 w-px bg-slate-300 dark:bg-slate-700 mx-1"></div>

        {{-- Undo / Redo --}}
        <button
            type="button"
            @click.prevent="exec('undo')"
            class="p-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center"
            title="Undo (Ctrl+Z)"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m-15-7l4-4m-4 4l4 4"/>
            </svg>
        </button>
        <button
            type="button"
            @click.prevent="exec('redo')"
            class="p-1.5 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white transition-colors w-8 h-8 flex items-center justify-center"
            title="Redo (Ctrl+Y)"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2m15-7l-4-4m4 4l-4 4"/>
            </svg>
        </button>
    </div>

    {{-- Editor Contenteditable Area --}}
    <div class="relative bg-slate-50/50 dark:bg-slate-900/40">
        <div
            x-ref="editor"
            contenteditable="true"
            data-placeholder="{{ $placeholder }}"
            @input="updateContent()"
            @keyup="checkActiveStates()"
            @mouseup="checkActiveStates()"
            @blur="updateContent()"
            class="rich-text-content px-4 py-3.5 min-h-[130px] max-h-[360px] overflow-y-auto text-sm text-slate-900 dark:text-white focus:outline-none leading-relaxed empty:before:content-[attr(data-placeholder)] empty:before:text-slate-400 dark:empty:before:text-slate-500 empty:before:pointer-events-none empty:before:cursor-text"
        ></div>
    </div>

    {{-- Footer Bar with Word & Char Stats --}}
    <div class="flex items-center justify-between px-4 py-1.5 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 dark:text-slate-500 select-none">
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>Rich Text</span>
        </span>
        <div class="flex items-center gap-3">
            <span>Words: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="wordCount">0</strong></span>
            <span>Chars: <strong class="font-semibold text-slate-600 dark:text-slate-300" x-text="charCount">0</strong></span>
        </div>
    </div>
</div>
