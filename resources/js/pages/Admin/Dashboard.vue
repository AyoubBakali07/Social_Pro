<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import StatCard from '@/components/ui/card/StatCard.vue'

const props = defineProps<{ stats: Array<{ label: string; value: number|string; color: string; icon: string; description?: string }> }>();
</script>

<template>
  <Head title="Admin Dashboard" />
  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div>
        <h1 class="text-2xl font-bold mb-1">Admin Dashboard</h1>
        <p class="text-gray-500 mb-6">System overview and management</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
          <StatCard
            v-for="stat in props.stats"
            :key="stat.label"
            :icon="stat.icon"
            :label="stat.label"
            :value="stat.value"
            :color="stat.color"
            :description="stat.description"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
