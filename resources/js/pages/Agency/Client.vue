<script setup lang="ts">
import { ref, computed, h } from 'vue'
import { router, useForm, Head } from '@inertiajs/vue3'

// Type declaration for global route helper
declare global {
  interface Window {
    route: (name: string, params?: Record<string, any>) => string;
  }
  const route: Window['route'];
}
import AppLayout from '@/layouts/AppLayout.vue'
import { useToast, POSITION } from 'vue-toastification'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { type BreadcrumbItem } from '@/types'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import DialogClose from '@/components/ui/dialog/DialogClose.vue'
import StatCard from '@/components/ui/card/StatCard.vue'
import GenericTable from '@/components/ui/GenericTable.vue'

// Types
interface Client {
  id: number;
  name: string;
  email: string;
  status: string;
  pendingPosts?: number;
  joined?: string;
  [key: string]: any;
}

// State
const search = ref('')
const showDeactivateDialog = ref(false)
const selectedClient = ref<Client | null>(null)
const isDeactivating = ref(false)

const filteredClients = computed(() => {
  if (!clientsList.value.length) return [];
  if (!search.value) return clientsList.value;
  
  const searchTerm = search.value.toLowerCase();
  return clientsList.value.filter(client =>
    (client.name?.toLowerCase() || '').includes(searchTerm) ||
    (client.email?.toLowerCase() || '').includes(searchTerm)
  );
})

function getInitials(name: string) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const toast = useToast()

// Debug: Log available routes
try {
  if (typeof window.route === 'function') {
    console.log('Available routes:', window.route('agency.clients.index'));
  } else {
    console.warn('Route helper is not available yet');
  }
} catch (error) {
  console.error('Error accessing route helper:', error);
}

// Open deactivation dialog
const openDeactivateDialog = (client: Client) => {
  selectedClient.value = client
  showDeactivateDialog.value = true
  
  // Debug: Log the route we're trying to use
  console.log('Trying to use route:', 'agency.clients.deactivate')
  console.log('Route URL:', route('agency.clients.deactivate', { client: client.id }))
}

// Handle client deactivation
const deactivateClient = async () => {
  if (!selectedClient.value) {
    console.error('No client selected for deactivation')
    toast.error('No client selected for deactivation')
    showDeactivateDialog.value = false
    return
  }
  
  isDeactivating.value = true
  
  try {
    console.log('Deactivating client with ID:', selectedClient.value.id)
    const response = await fetch(`/agency/clients/${selectedClient.value.id}/deactivate`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify({})
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    toast.success('Client deactivated successfully')
    // Refresh the client list
    router.reload({ only: ['clients'] })
  } catch (error) {
    console.error('Deactivation error:', error)
    toast.error('Failed to deactivate client. Please try again.')
  } finally {
    isDeactivating.value = false
    showDeactivateDialog.value = false
    selectedClient.value = null
  }
}

// Handle client activation
const activateClient = async (client: Client) => {
  try {
    await router.put(route('agency.clients.activate', { client: client.id }), {})
    toast.success('Client activated successfully')
    // Refresh the client list
    router.reload({ only: ['clients'] })
  } catch (error) {
    console.error('Activation error:', error)
    toast.error('Failed to activate client. Please try again.')
  }
}

interface StatItem {
  label: string;
  value: number;
  color: string;
  icon: string;
}

interface ClientItem {
  id: number;
  name: string;
  email: string;
  status: string;
  pendingPosts: number;
  joined: string;
  company_name?: string;
}

const props = withDefaults(defineProps<{ 
  stats?: StatItem[];
  clients?: ClientItem[];
}>(), {
  stats: () => [],
  clients: () => []
});

// Add reactive references for the props to ensure reactivity
const statsList = computed<StatItem[]>(() => props.stats || []);
const clientsList = computed<ClientItem[]>(() => props.clients || []);

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

const loading = ref<boolean>(false);
const form = useForm({
  name: '',
  email: '',
  company_name: '',
  message: ''
});

async function submitAddClient() {
  if (!validateAddClientForm()) {
    return;
  }
  
  loading.value = true;
  
  try {
    // Update form data
    form.name = addClientForm.value.name;
    form.email = addClientForm.value.email;
    form.company_name = addClientForm.value.company;
    form.message = addClientForm.value.message;
    
    // Submit the form using Inertia
    const options = {
      onSuccess: () => {
        closeAddClientModal();
        // Clear the form
        form.reset();
        // Show success message from the backend or default message
        const successMessage = form.recentlySuccessful && form.wasSuccessful
          ? form.recentlySuccessful
          : 'Client invited successfully!';
          
        toast.success(successMessage, {
          position: POSITION.TOP_RIGHT,
          timeout: 5000,
          closeOnClick: true,
          pauseOnFocusLoss: true,
          pauseOnHover: true,
          draggable: true,
          draggablePercent: 0.6,
          showCloseButtonOnHover: false,
          hideProgressBar: true,
          closeButton: 'button',
          icon: true,
          rtl: false
        });
      },
      onError: (errors: Record<string, string | string[]>) => {
        // Reset all errors
        addClientErrors.value = { name: '', email: '', company: '' };
        
        // Handle validation errors
        if (errors.name) addClientErrors.value.name = Array.isArray(errors.name) ? errors.name[0] : errors.name;
        if (errors.email) addClientErrors.value.email = Array.isArray(errors.email) ? errors.email[0] : errors.email;
        if (errors.company_name) addClientErrors.value.company = Array.isArray(errors.company_name) ? errors.company_name[0] : errors.company_name;
        
        // Show error toast for general errors
        const errorMessage = errors.message || 'Please fix the errors in the form.';
        toast.error(errorMessage, {
          position: POSITION.TOP_RIGHT,
          timeout: 5000,
          closeOnClick: true,
          pauseOnFocusLoss: true,
          pauseOnHover: true,
          draggable: true,
          draggablePercent: 0.6,
          showCloseButtonOnHover: false,
          hideProgressBar: true,
          closeButton: 'button',
          icon: true,
          rtl: false
        });
      },
      onFinish: () => {
        loading.value = false;
      }
    };
    
    await form.post(route('agency.clients.store'), options);
  } catch (error) {
    loading.value = false;
    toast.error('An unexpected error occurred. Please try again.', {
      position: POSITION.TOP_RIGHT,
      timeout: 5000,
      closeOnClick: true,
      pauseOnFocusLoss: true,
      pauseOnHover: true,
      draggable: true,
      draggablePercent: 0.6,
      showCloseButtonOnHover: false,
      hideProgressBar: true,
      closeButton: 'button',
      icon: true,
      rtl: false
    });
  }
}

const columns = [
  { label: 'Client', key: 'name', slot: 'client' },
  { label: 'Status', key: 'status', slot: 'status' },
  { label: 'Pending Posts', key: 'pendingPosts', slot: 'pendingPosts' },
  { label: 'Joined', key: 'joined', slot: 'joined' },
  { label: '', key: 'actions', slot: 'actions' },
];

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
          <StatCard
            v-for="stat in statsList"
            :key="stat.label"
            :icon="stat.icon"
            :label="stat.label"
            :value="stat.value"
            :color="stat.color"
          />
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
        <GenericTable :columns="columns" :rows="filteredClients">
          <template #client="{ row }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-semibold text-gray-600">
                {{ getInitials(row.name) }}
              </div>
              <div>
                <div class="font-semibold">{{ row.name }}</div>
                <div class="text-gray-500 text-sm inline-flex items-center gap-1">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
                  {{ row.email }}
                </div>
              </div>
            </div>
          </template>
          <template #status="{ row }">
            <span :class="row.status === 'Active' ? 'bg-emerald-100 text-emerald-700' : (row.status === 'Inactive' ? 'bg-gray-200 text-gray-600' : (row.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : (row.status === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-600')))" class="px-3 py-1 rounded-full text-xs font-semibold">
              {{ row.status }}
            </span>
          </template>
          <template #pendingPosts="{ row }">
            <span v-if="row.pendingPosts > 0" class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs font-semibold">{{ row.pendingPosts }}</span>
            <span v-else class="text-gray-500">0</span>
          </template>
          <template #joined="{ row }">
            <span class="text-gray-600">{{ row.joined }}</span>
          </template>
          <template #actions="{ row }">
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <button class="p-2 rounded-full hover:bg-gray-100">
                  <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <circle cx="10" cy="4" r="2"/>
                    <circle cx="10" cy="10" r="2"/>
                    <circle cx="10" cy="16" r="2"/>
                  </svg>
                </button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-48" align="end">
                <DropdownMenuItem 
                  v-if="row.status.toLowerCase() === 'active'"
                  @click="openDeactivateDialog(row as Client)"
                  class="text-red-600 hover:bg-red-50 focus:bg-red-50 cursor-pointer"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Deactivate
                </DropdownMenuItem>
                <DropdownMenuItem 
                  v-else-if="row.status.toLowerCase() === 'inactive'"
                  @click="activateClient(row as Client)"
                  class="text-green-600 hover:bg-green-50 focus:bg-green-50 cursor-pointer"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Activate
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </template>
        </GenericTable>
      </div>
    </div>
  </AppLayout>
  
  <!-- Deactivate Client Confirmation Dialog -->
  <Dialog v-model:open="showDeactivateDialog">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Deactivate Client</DialogTitle>
        <DialogDescription>
          Are you sure you want to deactivate {{ selectedClient?.name }}? They will no longer have access to their account.
        </DialogDescription>
      </DialogHeader>
      <div class="mt-4 flex justify-end gap-3">
        <Button variant="outline" @click="showDeactivateDialog = false">Cancel</Button>
        <Button variant="destructive" @click="deactivateClient" :disabled="isDeactivating">
          <svg v-if="isDeactivating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isDeactivating ? 'Deactivating...' : 'Deactivate' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
  
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
          <button 
            type="submit" 
            class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-75 disabled:cursor-not-allowed"
            :disabled="loading"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Sending...' : 'Send Invitation' }}
          </button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
