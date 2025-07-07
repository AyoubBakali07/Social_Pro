<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog'

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
    title: 'Dashboard',
    href: '/client/dashboard',
  },
]

const stats = [
  { label: 'Pending Approvals', value: 5, color: 'text-black' },
  { label: 'Approved This Month', value: 18, color: 'text-green-600' },
  { label: 'Upcoming Posts', value: 12, color: 'text-blue-600' },
]

const pendingApprovals = [
  {
    title: 'Summer Campaign Launch',
    description: 'Exciting summer collection is here! 🌟 Check out our latest designs.',
    scheduled: '1/20/2024, 10:00:00 AM',
    platform: 'Instagram',
  },
  {
    title: 'Product Showcase',
    description: 'Introducing our newest product line. What do you think?',
    scheduled: '1/22/2024, 2:00:00 PM',
    platform: 'Facebook',
  },
]

const showPreview = ref(false)
const showFeedback = ref(false)
const feedbackType = ref<'comment' | 'reject'>('comment')
const feedbackText = ref('')
</script>

<template>
  <Head title="Client Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <div>
        <h1 class="text-2xl font-bold mb-1">Client Dashboard</h1>
        <p class="text-gray-500 mb-6">Review and approve your social media content</p>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full mb-6">
          <div v-for="stat in stats" :key="stat.label" class="bg-white w-full rounded-xl p-4 flex flex-col items-start justify-center min-h-[100px] border border-gray-200">
            <span class="text-sm text-gray-500 mb-2">{{ stat.label }}</span>
            <span :class="['text-3xl font-bold', stat.color]">{{ stat.value }}</span>
          </div>
        </div>
      </div>
      <!-- Pending Approvals -->
      <!-- (Remove this entire section) -->

      <!-- Content Awaiting Your Approval -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 mt-6">
        <h2 class="text-xl font-semibold mb-4">Content Awaiting Your Approval</h2>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
          <div class="flex items-center gap-3 mb-2">
            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
            </div>
            <span class="font-semibold text-lg truncate">Check out our latest...</span>
            <span class="ml-2 bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-semibold">Post</span>
          </div>
          <div class="text-gray-500 text-sm mb-1">Scheduled for 12/15/2024</div>
          <div class="mb-4 text-gray-700">Check out our latest product launch!🚀 #newproduct #innovation</div>
          <div class="flex flex-wrap items-center gap-2">
            <button class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg font-semibold flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              Approve
            </button>
            <button class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg font-semibold flex items-center gap-2" @click="feedbackType = 'reject'; showFeedback = true">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
              Reject
            </button>
            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg font-semibold flex items-center gap-2" @click="feedbackType = 'comment'; showFeedback = true">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2h2m2-4h4a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
              Add Comment
            </button>
            <button class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-5 py-2 rounded-lg font-semibold flex items-center gap-2 border border-blue-200" @click="showPreview = true">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m0 0l3-3m-3 3l3 3"/></svg>
              Preview
            </button>
          </div>
        </div>
      </div>

      <!-- Post Preview Modal -->
      <Dialog v-model:open="showPreview">
        <DialogContent class="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Post Preview</DialogTitle>
            <DialogClose />
          </DialogHeader>
          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2 mb-2">
              <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-semibold">instagram, facebook</span>
              <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-semibold">Post</span>
            </div>
            <img
              src="https://wallpapers.com/images/high/4k-tech-adejl7ukwd91go3b.webp"
              alt="Post preview"
              class="rounded-xl w-full object-cover mb-2"
              style="max-height: 300px;"
            />
            <div class="mb-2">Check out our latest product launch!🚀 #newproduct #innovation</div>
            <div class="text-gray-700 text-sm">
              <span class="font-semibold">Scheduled:</span> 12/15/2024, 10:00:00 AM<br>
              <span class="font-semibold">Platforms:</span> instagram, facebook
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Feedback Modal for Add Comment and Reject -->
      <Dialog v-model:open="showFeedback">
        <DialogContent class="max-w-lg">
          <DialogHeader>
            <DialogTitle>
              {{ feedbackType === 'comment' ? 'Add Comment' : 'Reject Post' }}
            </DialogTitle>
            <DialogClose />
          </DialogHeader>
          <div class="flex flex-col gap-4">
            <textarea
              v-model="feedbackText"
              rows="4"
              class="w-full border-2 border-teal-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-teal-400"
              placeholder="Enter your feedback..."
            />
            <div class="flex gap-2 justify-end">
              <button
                class="bg-teal-400 hover:bg-teal-500 text-white px-5 py-2 rounded-lg font-semibold"
                @click="showFeedback = false"
              >
                Submit Feedback
              </button>
              <button
                class="bg-white border border-gray-300 text-gray-700 px-5 py-2 rounded-lg font-semibold"
                @click="showFeedback = false"
              >
                Cancel
              </button>
            </div>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>
