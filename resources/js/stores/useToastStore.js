import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);

    // Add a new toast
    const add = (message, type = 'success', title = '') => {
        const id = Date.now() + Math.random();

        // Default titles if not provided
        if (!title) {
            if (type === 'success') title = 'Success';
            else if (type === 'error' || type === 'danger') title = 'Error';
            else if (type === 'warning') title = 'Warning';
            else title = 'Notification';
        }

        const toast = { id, message, type, title };
        toasts.value.push(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            remove(id);
        }, 5000);
    };

    // Remove a toast by ID
    const remove = (id) => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    };

    return {
        toasts,
        add,
        remove
    };
});
