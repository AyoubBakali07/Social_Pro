<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
  icon: string;
  label: string;
  value: string | number;
  color?: string;
}>();
const iconBg = computed(() => {
  // Pick a bg color based on label or color prop
  if (props.color?.includes('blue')) return 'bg-blue-100';
  if (props.color?.includes('green')) return 'bg-green-100';
  if (props.color?.includes('yellow')) return 'bg-yellow-100';
  if (props.color?.includes('red')) return 'bg-red-100';
  return 'bg-gray-100';
});
</script>
<template>
  <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg" :class="iconBg">
      <span v-html="icon" />
    </div>
    <div>
      <div class="text-gray-500 text-sm">{{ label }}</div>
      <div class="text-2xl font-bold text-gray-900" :class="color">{{ value }}</div>
    </div>
  </div>
</template> 