<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { useToast, POSITION } from 'vue-toastification'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import Button from '@/components/ui/button/Button.vue'
import StatCard from '@/components/ui/card/StatCard.vue'
import GenericTable from '@/components/ui/GenericTable.vue'

const props = defineProps<{ stats: Array<{ label: string; value: number|string; color: string; icon: string; description?: string }>, agencies: Array<any> }>();

const search = ref('')
const toast = useToast()

type AgencyItem = {
  id: number
  name: string
  email: string
  status: string
  clients: number
  created: string
}

const showDeactivateDialog = ref(false)
const showActivateDialog = ref(false)
const selectedAgency = ref<AgencyItem | null>(null)
const isDeactivating = ref(false)
const isActivating = ref(false)

const filteredAgencies = computed(() => {
  if (!search.value) return props.agencies
  return props.agencies.filter(a =>
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

const columns = [
  { label: 'Agency', key: 'name', slot: 'agency' },
  { label: 'Email', key: 'email' },
  { label: 'Clients', key: 'clients', slot: 'clients' },
  { label: 'Status', key: 'status', slot: 'status' },
  { label: 'Created', key: 'created', slot: 'created' },
  { label: 'Actions', key: 'actions', slot: 'actions' },
];

function openDeactivateDialog(row: AgencyItem) {
  if (!row?.id) return
  selectedAgency.value = row
  showDeactivateDialog.value = true
}

function openActivateDialog(row: AgencyItem) {
  if (!row?.id) return
  selectedAgency.value = row
  showActivateDialog.value = true
}

async function deactivateAgency() {
  if (!selectedAgency.value?.id) return
  isDeactivating.value = true
  const url = `/admin/agencies/${selectedAgency.value.id}/deactivate`
  router.put(url, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Agency deactivated successfully', { position: POSITION.TOP_RIGHT })
      showDeactivateDialog.value = false
      selectedAgency.value = null
    },
    onFinish: () => {
      isDeactivating.value = false
    }
  })
}

async function activateAgency() {
  if (!selectedAgency.value?.id) return
  isActivating.value = true
  const url = `/admin/agencies/${selectedAgency.value.id}/activate`
  router.put(url, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Agency activated successfully', { position: POSITION.TOP_RIGHT })
      showActivateDialog.value = false
      selectedAgency.value = null
    },
    onFinish: () => {
      isActivating.value = false
    }
  })
}
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
        <GenericTable :columns="columns" :rows="filteredAgencies">
          <template #agency="{ row }">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-blue-50">
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m-1-7h4m-4 4h4m-4 4h4m4-13h2m-2 0h-5M9 7H7m2 4H7m2 4H7"/>
                </svg>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">{{ row.name }}</div>
              </div>
            </div>
          </template>
          <template #clients="{ row }">
            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
              {{ row.clients }} clients
            </span>
          </template>
          <template #status="{ row }">
            <span :class="statusBadge(row.status) + ' px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize'">
              {{ row.status }}
            </span>
          </template>
          <template #created="{ row }">
            <span class="text-sm text-gray-500">{{ new Date(row.created).toLocaleDateString() }}</span>
          </template>
          <template #actions="{ row }">
            <div class="flex justify-end space-x-2">
              <button class="text-blue-600 hover:text-blue-900" title="Edit">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button v-if="row.status !== 'Inactive'" class="text-red-600 hover:text-red-900" title="Deactivate" @click="openDeactivateDialog(row)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
              <button v-else class="text-green-600 hover:text-green-800" title="Activate" @click="openActivateDialog(row)">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </button>
            </div>
          </template>
        </GenericTable>
      </div>
    </div>
  </AppLayout>

  <!-- Deactivate Agency Confirmation Dialog -->
  <Dialog v-model:open="showDeactivateDialog">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Deactivate Agency</DialogTitle>
        <DialogDescription>
          Are you sure you want to deactivate {{ selectedAgency?.name }}? They will lose access.
        </DialogDescription>
      </DialogHeader>
      <div class="mt-4 flex justify-end gap-3">
        <Button variant="outline" @click="showDeactivateDialog = false">Cancel</Button>
        <Button variant="destructive" @click="deactivateAgency" :disabled="isDeactivating">
          <svg v-if="isDeactivating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isDeactivating ? 'Deactivating...' : 'Deactivate' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>

  <!-- Activate Agency Confirmation Dialog -->
  <Dialog v-model:open="showActivateDialog">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Activate Agency</DialogTitle>
        <DialogDescription>
          Are you sure you want to activate {{ selectedAgency?.name }}?
        </DialogDescription>
      </DialogHeader>
      <div class="mt-4 flex justify-end gap-3">
        <Button variant="outline" @click="showActivateDialog = false">Cancel</Button>
        <Button variant="default" @click="activateAgency" :disabled="isActivating">
          <svg v-if="isActivating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isActivating ? 'Activating...' : 'Activate' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
 