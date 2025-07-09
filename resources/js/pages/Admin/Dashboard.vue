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
        <h1 class="text-2xl font-bold mb-1">Admin Dashboard</h1>
        <p class="text-gray-500 mb-6">System overview and management</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-blue-100">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Total Agencies</div>
              <div class="text-2xl font-bold text-gray-900">24</div>
              <div class="text-green-500 text-xs mt-0.5">+2 this month</div>
            </div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-green-100">
              <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Total Clients</div>
              <div class="text-2xl font-bold text-gray-900">156</div>
              <div class="text-green-500 text-xs mt-0.5">+12 this week</div>
            </div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-purple-100">
              <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Monthly Revenue</div>
              <div class="text-2xl font-bold text-gray-900">$12,450</div>
              <div class="text-gray-400 text-xs mt-0.5">Current month</div>
            </div>
          </div>
          
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-amber-100">
              <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Active Posts</div>
              <div class="text-2xl font-bold text-gray-900">1,324</div>
              <div class="text-gray-400 text-xs mt-0.5">Currently active</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
