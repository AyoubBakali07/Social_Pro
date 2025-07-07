<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const stats = [
  {
    label: 'Total Agencies',
    value: 24,
    description: '+2 this month',
    icon: '🏢',
  },
  {
    label: 'Total Clients',
    value: 156,
    description: '+12 this week',
    icon: '👥',
  },
  {
    label: 'Monthly Revenue',
    value: '$12,450',
    description: 'This month',
    icon: '💰',
  },
  {
    label: 'Active Posts',
    value: 1324,
    description: 'Currently active',
    icon: '📝',
  },
]
</script>

<template>
  <Head title="Admin Dashboard" />
  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div>
        <h1 class="text-3xl font-bold mb-1">Admin Dashboard</h1>
        <p class="text-gray-500 mb-8">System overview and management</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 w-full">
          <div v-for="stat in stats" :key="stat.label" class="bg-white w-full rounded-xl p-6 flex flex-col items-start justify-center min-h-[120px] border border-gray-200">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-2xl">{{ stat.icon }}</span>
              <span class="font-semibold text-lg">{{ stat.label }}</span>
            </div>
            <span class="text-3xl font-bold">{{ stat.value }}</span>
            <span class="text-gray-400 text-sm mt-1">{{ stat.description }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
