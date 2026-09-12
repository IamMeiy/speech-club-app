import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global Custom Alert & Confirm Helpers
window.$confirm = window.$confirm || function(options, onConfirm) {
    const detail = typeof options === 'string' ? { message: options, onConfirm } : { ...options, onConfirm };
    window.dispatchEvent(new CustomEvent('modal-confirm', { detail }));
};

window.$alert = window.$alert || function(options, type = 'info') {
    const detail = typeof options === 'string' ? { message: options, type } : options;
    window.dispatchEvent(new CustomEvent('alert', { detail }));
};

// Route standard native window.alert to custom alert modal
window.alert = function(message) {
    if (typeof window.$alert === 'function') {
        window.$alert(message);
    }
};

// Intercept Livewire wire:confirm directive globally so any wire:confirm uses the custom alert modal
document.addEventListener('livewire:init', () => {
    if (window.Livewire && window.Livewire.directive) {
        window.Livewire.directive('confirm', ({ el, directive }) => {
            let message = directive.expression;
            message = (message || '').replaceAll('\\n', '\n');
            if (!message) message = 'Are you sure you want to proceed?';

            el.__livewire_confirm = (action, instead) => {
                if (typeof window.$confirm === 'function') {
                    window.$confirm({
                        title: 'Please Confirm',
                        message: message,
                        type: 'warning',
                        confirmText: 'Confirm',
                        cancelText: 'Cancel',
                        onConfirm: action
                    }).then(confirmed => {
                        if (!confirmed && typeof instead === 'function') {
                            instead();
                        }
                    });
                } else if (confirm(message)) {
                    action();
                } else if (typeof instead === 'function') {
                    instead();
                }
            };
        });
    }
});


