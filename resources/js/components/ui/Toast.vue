<template>
  <div 
    class="bs-toast toast fade show m-2" 
    role="alert" 
    aria-live="assertive" 
    aria-atomic="true">
    <div class="toast-header">
      <i :class="[iconClass, colorClass, 'me-2']"></i>
      <div class="me-auto fw-medium">{{ toast.title }}</div>
      <small class="text-muted">just now</small>
      <button type="button" class="btn-close" @click="$emit('close', toast.id)" aria-label="Close"></button>
    </div>
    <div class="toast-body">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  toast: {
    type: Object,
    required: true
  }
});

defineEmits(['close']);

const iconClass = computed(() => {
    switch (props.toast.type) {
        case 'success': return 'ri-checkbox-circle-fill';
        case 'danger': 
        case 'error': return 'ri-error-warning-fill';
        case 'warning': return 'ri-alert-fill';
        case 'info': return 'ri-information-fill';
        default: return 'ri-notification-fill';
    }
});

const colorClass = computed(() => {
     switch (props.toast.type) {
        case 'success': return 'text-success';
        case 'danger': 
        case 'error': return 'text-danger';
        case 'warning': return 'text-warning';
        case 'info': return 'text-info';
        default: return 'text-primary';
    }
});
</script>
