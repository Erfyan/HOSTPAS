<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-start justify-between">
    <div class="space-y-1">
      <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ title }}</p>
      <div class="text-2xl font-bold text-slate-800">{{ value }}</div>
      <p v-if="subvalue" class="text-xs text-slate-500">{{ subvalue }}</p>
    </div>
    <div v-if="iconBg" :class="['p-3.5 rounded-xl', iconBg]">
      <slot name="icon" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  subvalue: {
    type: String,
    default: '',
  },
  iconBg: {
    type: String,
    default: 'bg-indigo-100 text-indigo-600',
  },
});
</script>
