<template>
  <img :src="'/assets/img/logo.png'" alt="Dentomatic Logo" :style="{ width: width + 'px', height: 'auto' }" />
</template>

<script setup>
defineProps({
  width: {
    type: [Number, String],
    default: 40
  },
  height: { // Kept for API compatibility, though auto-height is used for images to maintain aspect ratio
    type: [Number, String],
    default: 'auto'
  }
});
</script>
