<script setup lang="ts">
import { defineProps, useSlots } from 'vue';

interface Column {
  label: string;
  key: string;
  // Optionally, a slot name or render function for custom cell rendering
  slot?: string;
  render?: (row: any, column: Column) => any;
}

const props = defineProps<{
  columns: Column[];
  rows: any[];
}>();
const slots = useSlots();
</script>
<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th v-for="col in columns" :key="col.key" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="(row, rowIdx) in rows" :key="rowIdx" class="hover:bg-gray-50">
          <td v-for="col in columns" :key="col.key" class="px-6 py-4 whitespace-nowrap">
            <template v-if="col.slot && slots[col.slot]">
              <slot :name="col.slot" :row="row" :column="col" :value="row[col.key]" />
            </template>
            <template v-else-if="col.render">
              {{ col.render(row, col) }}
            </template>
            <template v-else>
              {{ row[col.key] }}
            </template>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template> 