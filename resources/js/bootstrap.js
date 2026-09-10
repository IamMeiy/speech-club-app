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

