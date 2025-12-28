import { defineStore } from 'pinia';

export const useConfigStore = defineStore('config', {
    state: () => ({
        language: localStorage.getItem('language') || 'ru',
        isSidebarOpen: true,
    }),
    actions: {
        toggleSidebar() {
            this.isSidebarOpen = !this.isSidebarOpen;
        },
        setLanguage(lang) {
            this.language = lang;
            localStorage.setItem('language', lang);
        }
    }
});
