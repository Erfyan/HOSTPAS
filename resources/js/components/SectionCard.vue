<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div v-if="title || $slots.header" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100">
      <div>
        <h2 v-if="title" class="text-base font-bold text-slate-800 flex items-center gap-2">
          <slot name="title-icon" />
          {{ title }}
        </h2>
        <p v-if="description" class="text-xs text-slate-500 mt-0.5">{{ description }}</p>
      </div>
      <div v-if="$slots.action" class="flex items-center gap-2">
        <slot name="action" />
      </div>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
});
</script>
