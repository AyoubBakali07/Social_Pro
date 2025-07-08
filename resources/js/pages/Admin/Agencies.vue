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

type StatusType = 'active' | 'pending' | 'suspended' | 'inactive' | 'approved' | 'rejected' | 'draft' | 'published' | 'archived' | string;

const statusBadge = computed(() => {
  return (status: StatusType) => {
    const statusMap: Record<string, string> = {
      active: 'bg-green-50 text-green-700 ring-1 ring-green-600/20',
      pending: 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20',
      suspended: 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
      inactive: 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
      approved: 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
      rejected: 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
      draft: 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
      published: 'bg-green-50 text-green-700 ring-1 ring-green-600/20',
      archived: 'bg-purple-50 text-purple-700 ring-1 ring-purple-600/20',
      default: 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
    };
    return statusMap[status?.toLowerCase()] || statusMap.default;
  };
})
</script>

<template>
  <Head title="Agencies" />
  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold mb-1">Agencies</h1>
          <p class="text-gray-500">Manage registered agencies and their details</p>
        </div>
        <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50 self-start md:self-auto">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Agency
        </button>
      </div>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full">
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
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <div class="text-gray-500 text-sm">Active</div>
            <div class="text-2xl font-bold text-gray-900">18</div>
            <div class="text-green-500 text-xs mt-0.5">75% of total</div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
          <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-amber-100">
            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <div class="text-gray-500 text-sm">Pending</div>
            <div class="text-2xl font-bold text-gray-900">4</div>
            <div class="text-amber-500 text-xs mt-0.5">Awaiting approval</div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
          <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-red-100">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div>
            <div class="text-gray-500 text-sm">Suspended</div>
            <div class="text-2xl font-bold text-gray-900">2</div>
            <div class="text-red-500 text-xs mt-0.5">Requires attention</div>
          </div>
        </div>
      </div>
      <!-- Agencies Table -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">All Agencies</h2>
            <p class="text-gray-500 text-sm mt-1">Complete list of registered agencies and their details</p>
          </div>
          <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            <input
              v-model="search"
              type="text"
              placeholder="Search agencies by name or email..."
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agency</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clients</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="agency in filteredAgencies" :key="agency.email" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-blue-50">
                      <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m-1-7h4m-4 4h4m-4 4h4m4-13h2m-2 0h-5M9 7H7m2 4H7m2 4H7"/>
                      </svg>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ agency.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ agency.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ agency.clients }} clients
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="statusBadge(agency.status) + ' px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize'">
                    {{ agency.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ new Date(agency.created).toLocaleDateString() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button class="text-blue-600 hover:text-blue-900" title="Edit">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-900" title="Delete">
                      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 