{{-- Custom Modal for Alerts & Confirmations --}}
<div
    x-data="{
        open: false,
        type: 'danger', // danger, warning, info, success
        title: 'Confirm Action',
        message: '',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        isAlert: false,
        resolvePromise: null,

        init() {
            window.$confirm = (options, onConfirm) => this.askConfirm(options, onConfirm);
            window.$alert = (options, type = 'info') => this.askAlert(options, type);
        },

        askConfirm(options, onConfirm) {
            return new Promise((resolve) => {
                let payload = {};
                if (typeof options === 'string') {
                    payload = { message: options };
                } else if (typeof options === 'object' && options !== null) {
                    payload = { ...options };
                }

                const callback = onConfirm || payload.onConfirm;

                this.title = payload.title || (payload.type === 'warning' ? 'Warning' : 'Are you sure?');
                this.message = payload.message || 'Are you sure you want to proceed?';
                this.type = payload.type || 'danger';
                this.confirmText = payload.confirmText || (this.type === 'danger' ? 'Delete' : 'Confirm');
                this.cancelText = payload.cancelText || 'Cancel';
                this.isAlert = false;

                this.resolvePromise = (confirmed) => {
                    if (confirmed && typeof callback === 'function') {
                        callback();
                    }
                    resolve(confirmed);
                };

                this.open = true;
                this.$nextTick(() => {
                    this.$refs.confirmBtn?.focus();
                });
            });
        },

        askAlert(options, type = 'info') {
            return new Promise((resolve) => {
                let payload = {};
                if (typeof options === 'string') {
                    payload = { message: options, type: type };
                } else if (typeof options === 'object' && options !== null) {
                    payload = { ...options };
                }

                this.title = payload.title || (payload.type === 'error' || payload.type === 'danger' ? 'Attention' : (payload.type === 'success' ? 'Success' : 'Notice'));
                this.message = payload.message || '';
                this.type = payload.type || type || 'info';
                this.confirmText = payload.confirmText || 'Got it';
                this.cancelText = '';
                this.isAlert = true;

                this.resolvePromise = (res) => resolve(res);

                this.open = true;
                this.$nextTick(() => {
                    this.$refs.confirmBtn?.focus();
                });
            });
        },

        confirm() {
            this.open = false;
            if (this.resolvePromise) {
                this.resolvePromise(true);
                this.resolvePromise = null;
            }
        },

        cancel() {
            this.open = false;
            if (this.resolvePromise) {
                this.resolvePromise(false);
                this.resolvePromise = null;
            }
        }
    }"
    x-on:keydown.escape.window="if (open) cancel()"
    x-on:alert.window="askAlert($event.detail)"
    x-on:modal-confirm.window="askConfirm($event.detail)"
    x-show="open"
    class="fixed inset-0 z-[100] overflow-y-auto"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="cancel()"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
    ></div>

    {{-- Dialog Centering Container --}}
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            @click.stop
            class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 text-left shadow-2xl transition-all w-full sm:max-w-md p-6 sm:p-7"
        >
            <div class="flex items-start gap-4">
                {{-- Dynamic Status Icon --}}
                <div class="flex-shrink-0">
                    {{-- Danger Icon --}}
                    <div x-show="type === 'danger' || type === 'error'" class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200/60 dark:border-rose-900/40 flex items-center justify-center text-rose-600 dark:text-rose-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    {{-- Warning Icon --}}
                    <div x-show="type === 'warning'" class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-900/40 flex items-center justify-center text-amber-600 dark:text-amber-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    {{-- Success Icon --}}
                    <div x-show="type === 'success'" class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/60 dark:border-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    {{-- Info / Default Icon --}}
                    <div x-show="type === 'info'" class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/50 border border-primary-200/60 dark:border-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-400 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Text Content --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight" x-text="title"></h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed break-words" x-text="message"></p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <button
                    x-show="!isAlert"
                    type="button"
                    @click="cancel()"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-750 transition-all active:scale-[0.98] shadow-sm"
                    x-text="cancelText"
                ></button>

                <button
                    x-ref="confirmBtn"
                    type="button"
                    @click="confirm()"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700 text-white shadow-md shadow-rose-600/25': type === 'danger' || type === 'error',
                        'bg-amber-600 hover:bg-amber-700 text-white shadow-md shadow-amber-600/25': type === 'warning',
                        'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/25': type === 'success',
                        'bg-primary-600 hover:bg-primary-700 text-white shadow-md shadow-primary-600/25': type === 'info'
                    }"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-2xl text-sm font-semibold transition-all active:scale-[0.98]"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>
</div>
