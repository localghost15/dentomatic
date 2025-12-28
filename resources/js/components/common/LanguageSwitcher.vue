<template>
    <div class="dropdown language-switcher">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ri-translate-2"></i> {{ currentLanguage.toUpperCase() }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" @click.prevent="setLanguage('ru')">Русский</a></li>
            <li><a class="dropdown-item" href="#" @click.prevent="setLanguage('uz')">O'zbek</a></li>
        </ul>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useConfigStore } from '@/stores/useConfigStore';

const configStore = useConfigStore();
const currentLanguage = computed(() => configStore.language);

const setLanguage = (lang) => {
    configStore.setLanguage(lang);
};
</script>

<style scoped>
.language-switcher {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 1000;
}
</style>
