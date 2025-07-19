<script setup lang="ts">
import { ref, computed, reactive } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog'
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';
import StatCard from '@/components/ui/card/StatCard.vue';
import FeedbackModal from '@/components/ui/dialog/FeedbackModal.vue';

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

const calendarEvents = ref([
  {
    title: 'Sample Event',
    start: new Date().toISOString().slice(0, 10),
    allDay: true,
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
]);

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, rrulePlugin],
  initialView: 'dayGridMonth',
  editable: false,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek,dayGridDay',
  },
});
</script>

<template>
  <Head title="Client Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <div>
        <h1 class="text-2xl font-bold mb-1">Client Dashboard</h1>
        <p class="text-gray-500 mb-6">Review and approve your social media content</p>
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full mb-6">
          <StatCard
            icon="<svg class='w-6 h-6 text-blue-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'/></svg>"
            label="Pending Approval"
            :value="3"
            color="text-gray-500"
          />
          <StatCard
            icon="<svg class='w-6 h-6 text-green-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
            label="Approved"
            :value="12"
            color="text-green-600"
          />
          <StatCard
            icon="<svg class='w-6 h-6 text-yellow-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><path d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
            label="In Review"
            :value="5"
            color="text-yellow-600"
          />
          <StatCard
            icon="<svg class='w-6 h-6 text-red-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10'/><path d='M15 9l-6 6m0-6l6 6'/></svg>"
            label="Rejected"
            :value="2"
            color="text-red-600"
          />
        </div>
      </div>
      <!-- Pending Approvals -->
      <!-- (Remove this entire section) -->

      <!-- Content Awaiting Your Approval -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold">Content Awaiting Your Approval</h2>
          <div class="relative">
            <input 
              type="text" 
              v-model="search" 
              placeholder="Search content..." 
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>
        
        <div class="space-y-4">
          <div v-for="(item, index) in 3" :key="index" class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold text-gray-900 truncate">Check out our latest product launch! 🚀</h3>
                  <span class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap">Post</span>
                </div>
                <div class="text-sm text-gray-500 mb-3">Scheduled for Dec 15, 2024 at 10:00 AM</div>
                <p class="text-gray-700 mb-4">Check out our latest product launch! 🚀 #newproduct #innovation</p>
                
                <div class="flex flex-wrap items-center gap-2">
                  <button class="bg-green-50 hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 border border-green-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve
                  </button>
                  <button class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 border border-red-100" @click="feedbackType = 'reject'; showFeedback = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                  </button>
                  <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50" @click="feedbackType = 'comment'; showFeedback = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Comment
                  </button>
                  <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50" @click="showPreview = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- End Content Awaiting Your Approval section -->

      <!-- Upcoming Contents Calendar -->
      <div class="mt-3">
        <h2 class="text-2xl font-semibold mb-4">Upcoming Contents</h2>
        <div class="w-full  bg-white border border-gray-200 rounded-xl p-4 shadow mx-auto">
          <FullCalendar :options="calendarOptions" :events="calendarEvents" />
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
      <FeedbackModal
        v-model:open="showFeedback"
        v-model:feedbackText="feedbackText"
        :feedbackType="feedbackType"
        @submit="showFeedback = false"
        @cancel="showFeedback = false"
      />
    </div>
  </AppLayout>
</template>
