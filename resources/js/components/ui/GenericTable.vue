<script setup lang="ts">
import { defineProps, useSlots, ref, computed, watch } from 'vue';

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

const pageSize = 5;
const currentPage = ref(1);
const totalPages = computed(() => Math.ceil(props.rows.length / pageSize));
const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return props.rows.slice(start, start + pageSize);
});

watch(
  () => props.rows.length,
  () => {
    // Reset to first page if data changes
    currentPage.value = 1;
  }
);

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}
function prevPage() {
  if (currentPage.value > 1) currentPage.value--;
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++;
}
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
        <tr v-for="(row, rowIdx) in paginatedRows" :key="rowIdx" class="hover:bg-gray-50">
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
    <div v-if="props.rows.length > pageSize" class="flex items-center justify-between mt-4 px-2">
      <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 rounded border text-sm mr-2" :class="currentPage === 1 ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">Prev</button>
      <div class="flex gap-1">
        <button
          v-for="page in totalPages"
          :key="page"
          @click="goToPage(page)"
          class="px-3 py-1 rounded border text-sm"
          :class="page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
        >
          {{ page }}
        </button>
      </div>
      <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border text-sm ml-2" :class="currentPage === totalPages ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">Next</button>
    </div>
  </div>
</template> 