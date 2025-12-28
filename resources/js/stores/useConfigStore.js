import { defineStore } from 'pinia';

export const useConfigStore = defineStore('config', {
    state: () => ({
        language: 'ru',
        isSidebarOpen: true,
    }),
    actions: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        setLanguage(lang) {
            this.language = lang;
        }
    }
});
