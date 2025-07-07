<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const stats = [
  { label: 'Total Agencies', value: 24, description: '+2 this month', color: 'text-gray-900', sub: '' },
  { label: 'Active', value: 18, description: '75% of total', color: 'text-green-600', sub: '' },
  { label: 'Pending', value: 4, description: 'Awaiting approval', color: 'text-amber-500', sub: '' },
  { label: 'Suspended', value: 2, description: 'Requires attention', color: 'text-red-500', sub: '' },
]

const agencies = ref([
  {
    name: 'Digital Marketing Pro',
    email: 'contact@digitalmarketingpro.com',
    clients: 8,
    status: 'active',
    created: '2024-01-15',
  },
  {
    name: 'Creative Solutions Ltd',
    email: 'hello@creativesolutions.com',
    clients: 5,
    status: 'pending',
    created: '2024-02-01',
  },
  {
    name: 'Social Media Experts',
    email: 'info@smexperts.com',
    clients: 12,
    status: 'active',
    created: '2024-01-08',
  },
  {
    name: 'Brand Builders Inc',
    email: 'team@brandbuilders.com',
    clients: 3,
    status: 'suspended',
    created: '2024-02-20',
  },
])

const search = ref('')
const filteredAgencies = computed(() => {
  if (!search.value) return agencies.value
  return agencies.value.filter(a =>
    a.name.toLowerCase().includes(search.value.toLowerCase()) ||
    a.email.toLowerCase().includes(search.value.toLowerCase())
  )
})

function statusBadge(status: string) {
  if (status === 'active') return 'bg-teal-100 text-teal-700'
  if (status === 'pending') return 'bg-gray-100 text-gray-500'
  if (status === 'suspended') return 'bg-red-100 text-red-600'
  return 'bg-gray-100 text-gray-500'
}
</script>

<template>
  <Head title="Agencies" />
  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold mb-1">Agencies</h1>
          <p class="text-gray-500">Manage registered agencies</p>
        </div>
        <button class="bg-teal-500 hover:bg-teal-600 text-white font-semibold px-6 py-2 rounded-lg flex items-center gap-2 self-start md:self-auto">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Add Agency
        </button>
      </div>
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full mb-2">
        <div v-for="stat in stats" :key="stat.label" class="bg-white w-full rounded-xl p-4 flex flex-col items-start justify-center min-h-[100px] border border-gray-200">
          <span class="text-sm text-gray-500 mb-2">{{ stat.label }}</span>
          <span :class="['text-3xl font-bold', stat.color]">{{ stat.value }}</span>
          <span class="text-gray-400 text-sm mt-1">{{ stat.description }}</span>
        </div>
      </div>
      <!-- Agencies Table -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
          <div>
            <h2 class="text-lg font-semibold">All Agencies</h2>
            <p class="text-gray-500 text-sm">Complete list of registered agencies</p>
          </div>
          <div class="relative w-full md:w-72 mt-2 md:mt-0">
            <input
              v-model="search"
              type="text"
              placeholder="Search agencies..."
              class="w-full border border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
            />
            <svg class="absolute right-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-left">
            <thead>
              <tr class="text-gray-500 text-sm">
                <th class="py-2 px-4 font-medium">Agency</th>
                <th class="py-2 px-4 font-medium">Email</th>
                <th class="py-2 px-4 font-medium">Clients</th>
                <th class="py-2 px-4 font-medium">Status</th>
                <th class="py-2 px-4 font-medium">Created</th>
                <th class="py-2 px-4 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="agency in filteredAgencies" :key="agency.email" class="border-t last:border-b">
                <td class="py-3 px-4 flex items-center gap-2 font-medium">
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M16 3v4M8 3v4"/></svg>
                  {{ agency.name }}
                </td>
                <td class="py-3 px-4">{{ agency.email }}</td>
                <td class="py-3 px-4">{{ agency.clients }}</td>
                <td class="py-3 px-4">
                  <span :class="statusBadge(agency.status) + ' px-3 py-1 rounded-full text-sm font-semibold'">
                    {{ agency.status }}
                  </span>
                </td>
                <td class="py-3 px-4">{{ agency.created }}</td>
                <td class="py-3 px-4 flex gap-2">
                  <button class="p-2 rounded hover:bg-gray-100" title="Edit">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 11l6 6M3 21h6l11.293-11.293a1 1 0 0 0 0-1.414l-4.586-4.586a1 1 0 0 0-1.414 0L3 15v6z"/></svg>
                  </button>
                  <button class="p-2 rounded hover:bg-gray-100" title="Delete">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 7V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v2m5 4v6m4-6v6"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 