<template>
  <span
    :class="[
      'text-xs px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1',
      step === 4 ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-700'
    ]"
  >
    Langkah {{ step }}
  </span>
</template>

<script setup>
defineProps({
  step: {
    type: Number,
    required: true,
  },
});
</script>
