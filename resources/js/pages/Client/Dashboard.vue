<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

const search = ref('')
const clients = ref([
  {
    name: 'Sarah Johnson',
    email: 'sarah@example.com',
    status: 'Active',
    pendingPosts: 3,
    joined: '1/15/2024',
  },
  {
    name: 'Mike Chen',
    email: 'mike@techcorp.com',
    status: 'Active',
    pendingPosts: 1,
    joined: '2/1/2024',
  },
  {
    name: 'Emma Davis',
    email: 'emma@startup.co',
    status: 'Inactive',
    pendingPosts: 0,
    joined: '1/20/2024',
  },
])

const filteredClients = computed(() => {
  if (!search.value) return clients.value
  return clients.value.filter(client =>
    client.name.toLowerCase().includes(search.value.toLowerCase()) ||
    client.email.toLowerCase().includes(search.value.toLowerCase())
  )
})

function getInitials(name: string) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Clients',
    href: '/clients',
  },
]
</script>

<template>
  <Head title="Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <h1>THIS IS CLIENT DASHBOARD   </h1>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="mb-6 flex flex-col items-center">
        <div class="mb-6 w-full flex flex-col items-start">
          <h2 class="text-2xl font-semibold mb-2">Clients</h2>
          <p class="text-sm text-muted-foreground">Manage your client accounts</p>
        </div>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full mb-6">
  <div class="bg-white w-full rounded-xl shadow p-4 flex flex-col items-start justify-center min-h-[100px] border border-gray-200">
    <span class="text-sm text-gray-500 mb-2">Total Clients</span>
    <span class="text-3xl font-bold text-black">3</span>
  </div>
  <div class="bg-white w-full rounded-xl shadow p-4 flex flex-col items-start justify-center min-h-[100px] border border-gray-200">
    <span class="text-sm text-gray-500 mb-2">Active Clients</span>
    <span class="text-3xl font-bold text-green-600">2</span>
  </div>
  <div class="bg-white w-full rounded-xl shadow p-4 flex flex-col items-start justify-center min-h-[100px] border border-gray-200">
    <span class="text-sm text-gray-500 mb-2">Pending Posts</span>
    <span class="text-3xl font-bold text-orange-500">4</span>
  </div>
</div>
      </div>
      <div class="relative min-h-[60vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 bg-white">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-semibold">Client Directory</h2>
          <button class="bg-teal-500 hover:bg-teal-600 text-white font-semibold px-6 py-2 rounded-lg flex items-center gap-2">
            <span class="text-xl">+</span> Add Client
          </button>
        </div>
        <div class="mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Search clients..."
            class="w-full border border-gray-200 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500"
          />
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-left">
            <thead>
              <tr class="text-gray-500 text-sm">
                <th class="py-2 px-4 font-medium">Client</th>
                <th class="py-2 px-4 font-medium">Status</th>
                <th class="py-2 px-4 font-medium">Pending Posts</th>
                <th class="py-2 px-4 font-medium">Joined</th>
                <th class="py-2 px-4"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="client in filteredClients" :key="client.email" class="border-t last:border-b">
                <td class="py-3 px-4 flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-semibold text-gray-600">
                    {{ getInitials(client.name) }}
                  </div>
                  <div>
                    <div class="font-semibold">{{ client.name }}</div>
                    <div class="text-gray-500 text-sm flex items-center gap-1">
                      <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 01-8 0m8 0a4 4 0 00-8 0m8 0V8a4 4 0 00-8 0v4m8 0v4a4 4 0 01-8 0v-4"/></svg>
                      {{ client.email }}
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <span :class="client.status === 'Active' ? 'bg-teal-100 text-teal-700' : 'bg-gray-200 text-gray-600'" class="px-3 py-1 rounded-full text-sm font-semibold">
                    {{ client.status }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span v-if="client.pendingPosts > 0" class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full text-sm font-semibold">{{ client.pendingPosts }}</span>
                  <span v-else class="text-gray-500">0</span>
                </td>
                <td class="py-3 px-4 text-gray-600">{{ client.joined }}</td>
                <td class="py-3 px-4 text-right">
                  <button class="p-2 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="4" r="2"/><circle cx="10" cy="10" r="2"/><circle cx="10" cy="16" r="2"/></svg>
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
