<template>
  <span
    :class="[
      'text-xs font-bold px-3 py-1 rounded-full inline-flex items-center gap-1.5 transition-colors',
      valid ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
    ]"
  >
    <span :class="['w-1.5 h-1.5 rounded-full', valid ? 'bg-emerald-600' : 'bg-rose-600']"></span>
    <span>Total Bobot: {{ formattedTotal }} ({{ valid ? 'Valid 100%' : 'Belum 100%' }})</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  valid: {
    type: Boolean,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
});

const formattedTotal = computed(() => {
  return (props.total * 100).toFixed(1) + '%';
});
</script>
