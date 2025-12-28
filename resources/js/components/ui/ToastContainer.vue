<template>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
    <Toast 
        v-for="toast in toastStore.toasts" 
        :key="toast.id" 
        :toast="toast" 
        @close="toastStore.remove" 
    />
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/useToastStore';
import Toast from './Toast.vue';

const toastStore = useToastStore();
</script>
