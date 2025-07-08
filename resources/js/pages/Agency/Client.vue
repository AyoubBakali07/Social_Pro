<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import DialogClose from '@/components/ui/dialog/DialogClose.vue'

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

const showAddClientModal = ref(false)
const addClientForm = ref({
  name: '',
  email: '',
  company: '',
  message: ''
})
const addClientErrors = ref({
  name: '',
  email: '',
  company: ''
})

function openAddClientModal() {
  showAddClientModal.value = true
}

function closeAddClientModal() {
  showAddClientModal.value = false
  addClientForm.value = { name: '', email: '', company: '', message: '' }
  addClientErrors.value = { name: '', email: '', company: '' }
}

function validateAddClientForm() {
  let valid = true
  addClientErrors.value = { name: '', email: '', company: '' }
  if (!addClientForm.value.name) {
    addClientErrors.value.name = 'Client name is required.'
    valid = false
  }
  if (!addClientForm.value.email) {
    addClientErrors.value.email = 'Email address is required.'
    valid = false
  }
  if (!addClientForm.value.company) {
    addClientErrors.value.company = 'Company name is required.'
    valid = false
  }
  return valid
}

function submitAddClient() {
  if (validateAddClientForm()) {
    // No backend logic yet, just close modal
    closeAddClientModal()
  }
}
</script>

<template>
  <Head title="Clients" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="mb-6 flex flex-col items-center">
        <div class="mb-6 w-full flex flex-col items-start">
          <h2 class="text-2xl font-semibold mb-2">Clients</h2>
          <p class="text-sm text-gray-700">Manage your client accounts</p>
        </div>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 w-full mb-8">
          <!-- Total Clients -->
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-blue-100">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87M16 3.13a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"/></svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Total Clients</div>
              <div class="text-2xl font-bold text-gray-900">3</div>
            </div>
          </div>
          <!-- Active Clients -->
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-green-100">
              <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Active</div>
              <div class="text-2xl font-bold text-gray-900">2</div>
            </div>
          </div>
          <!-- Pending -->
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-yellow-100">
              <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Pending</div>
              <div class="text-2xl font-bold text-gray-900">4</div>
            </div>
          </div>
          <!-- Inactive -->
          <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
            <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-red-100">
              <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            </div>
            <div>
              <div class="text-gray-500 text-sm">Inactive</div>
              <div class="text-2xl font-bold text-gray-900">1</div>
            </div>
          </div>
        </div>
    </div>
    <div class="relative min-h-[60vh] flex-1 rounded-lg border border-gray-200 p-4 bg-white">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-2xl font-semibold">Client Directory</h2>
          <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50" @click="openAddClientModal">
            <span class="text-xl">+</span> Add Client
          </button>
        </div>
        <div class="mb-4">
          <input
            v-model="search"
            type="text"
            placeholder="Search clients..."
            class="w-full border border-gray-200 rounded-md px-4 py-2 bg-white text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
                    <div class="text-gray-500 text-sm inline-flex items-center gap-1">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
                      {{ client.email }}
                    </div>
                  </div>
                </td>
                <td class="py-3 px-4">
                  <span :class="client.status === 'Active' ? 'bg-emerald-100 text-emerald-700' : (client.status === 'Inactive' ? 'bg-gray-200 text-gray-600' : (client.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : (client.status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-600')))" class="px-3 py-1 rounded-full text-xs font-semibold">
                    {{ client.status }}
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span v-if="client.pendingPosts > 0" class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs font-semibold">{{ client.pendingPosts }}</span>
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
  <Dialog v-model:open="showAddClientModal">
    <DialogContent class="max-w-lg w-full">
      <DialogHeader>
        <DialogTitle>Invite New Client</DialogTitle>
        <DialogDescription>Fill in the details to invite a new client to your agency.</DialogDescription>
        <DialogClose />
      </DialogHeader>
      <form @submit.prevent="submitAddClient" class="flex flex-col gap-4 mt-2">
        <div>
          <label class="block text-sm font-medium mb-1">Client Name <span class="text-red-500">*</span></label>
          <input v-model="addClientForm.name" type="text" placeholder="Enter client's full name" class="w-full rounded border p-2 bg-white text-black" :class="addClientErrors.name ? 'border-red-400' : 'border-gray-200'" required />
          <div v-if="addClientErrors.name" class="text-xs text-red-500 mt-1">{{ addClientErrors.name }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Email Address <span class="text-red-500">*</span></label>
          <input v-model="addClientForm.email" type="email" placeholder="client@company.com" class="w-full rounded border p-2 bg-white text-black" :class="addClientErrors.email ? 'border-red-400' : 'border-gray-200'" required />
          <div v-if="addClientErrors.email" class="text-xs text-red-500 mt-1">{{ addClientErrors.email }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Company Name <span class="text-red-500">*</span></label>
          <input v-model="addClientForm.company" type="text" placeholder="Company or Organization" class="w-full rounded border p-2 bg-white text-black" :class="addClientErrors.company ? 'border-red-400' : 'border-gray-200'" required />
          <div v-if="addClientErrors.company" class="text-xs text-red-500 mt-1">{{ addClientErrors.company }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Personal Message (Optional)</label>
          <textarea v-model="addClientForm.message" class="w-full rounded border p-2 bg-white text-black" rows="3" placeholder="Add a personal message to your invitation..."></textarea>
        </div>
        <DialogFooter class="mt-4 flex justify-end gap-3">
          <button type="button" class="inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200" @click="closeAddClientModal">Cancel</button>
          <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">Send Invitation</button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
