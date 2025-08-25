<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toast } from '@/toast';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';
import StatCard from '@/components/ui/card/StatCard.vue';
import FeedbackModal from '@/components/ui/dialog/FeedbackModal.vue';

interface Post {
  id: number;
  title: string;
  content: string;
  platform: string;
  postType: string;
  status: string;
  client_id: number;
  created_at: string;
  scheduleDate?: string;
  media?: string | string[] | null;
  client?: {
    id: number;
    name: string;
    email: string;
  };
}

interface StatItem {
  label: string;
  value: number | string;
  color: string;
  icon: string;
}

interface DashboardProps {
  pendingPosts: Post[];
  stats: StatItem[];
  calendarPosts?: any[];
}

const search = ref('');
const props = defineProps<DashboardProps>();

const filteredPosts = computed(() => {
  if (!search.value) return props.pendingPosts;
  return props.pendingPosts.filter(
    (post) =>
      (post.title?.toLowerCase().includes(search.value.toLowerCase()) ||
      post.content?.toLowerCase().includes(search.value.toLowerCase()))
  );
});

function getInitials(name: string) {
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/client/dashboard',
  },
];

function formatDateTime(iso: string) {
  try {
    return new Date(iso).toLocaleString();
  } catch {
    return iso;
  }
}

const showPreview = ref(false)
const selectedPost = ref<Post | null>(null)
const showFeedback = ref(false)
const feedbackType = ref<'comment' | 'reject'>('comment')
const feedbackText = ref('')
const actingPostId = ref<number | null>(null)

async function approvePost(postId: number) {
  await router.post(`/client/posts/${postId}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Post approved')
      router.reload({ only: ['stats', 'pendingPosts'] })
    }
  })
}

function openComment(postId: number) {
  actingPostId.value = postId
  feedbackType.value = 'comment'
  feedbackText.value = ''
  showFeedback.value = true
}

function openReject(postId: number) {
  actingPostId.value = postId
  feedbackType.value = 'reject'
  feedbackText.value = ''
  showFeedback.value = true
}

async function submitFeedback() {
  if (!actingPostId.value) return
  if (feedbackType.value === 'comment') {
    await router.post(`/client/posts/${actingPostId.value}/comment`, { comment: feedbackText.value }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Comment added')
        router.reload({ only: ['stats', 'pendingPosts'] })
      }
    })
  } else {
    await router.post(`/client/posts/${actingPostId.value}/reject`, { feedback: feedbackText.value }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Post rejected with feedback')
        router.reload({ only: ['stats', 'pendingPosts'] })
      }
    })
  }
  showFeedback.value = false
}

// Toastify server flash messages (success/error)
const page = usePage();
watch(() => (page.props as any).flash?.success, (message) => {
  if (message) toast.success(message)
}, { immediate: true });
watch(() => (page.props as any).flash?.error, (message) => {
  if (message) toast.error(message)
}, { immediate: true });

const calendarEvents = ref<any[]>([]);

const calendarOptions = ref({
  plugins: [dayGridPlugin, interactionPlugin, rrulePlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  events: calendarEvents,
  dateClick: (info: any) => {
    // Handle date click
  },
  eventClick: (info: any) => {
    // Handle event click
  }
});

function getPlatformColor(platform: string) {
  const map: Record<string, string> = {
    Facebook: '#3b82f6',
    Instagram: '#ec4899',
    Twitter: '#111827',
    TikTok: '#06b6d4',
    LinkedIn: '#1e40af',
  };
  return map[platform] || '#64748b';
}

function formatCalendarEvents(posts: any[]) {
  if (!Array.isArray(posts)) return [];
  return posts.map((post: any) => {
    const color = getPlatformColor(post.platform || '');
    return {
      id: String(post.id),
      title: post.content ? String(post.content).slice(0, 30) + (String(post.content).length > 30 ? '…' : '') : post.title || 'Post',
      start: post.scheduleDate || new Date().toISOString().slice(0,10),
      allDay: true,
      backgroundColor: color,
      borderColor: color,
      extendedProps: { ...post }
    };
  });
}

// initialize calendar events from props and update when props change
calendarEvents.value = formatCalendarEvents(props.calendarPosts || []);
watch(() => props.calendarPosts, (posts) => {
  calendarEvents.value = formatCalendarEvents(posts || [])
}, { deep: true });
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
            v-for="stat in props.stats"
            :key="stat.label"
            :icon="stat.icon"
            :label="stat.label"
            :value="stat.value"
            :color="stat.color"
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
          <div v-for="post in filteredPosts" :key="post.id" class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <h3 class="font-semibold text-gray-900 truncate">{{ post.title }}</h3>
                  <span class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap">{{ post.postType || 'Post' }}</span>
                </div>
                <div class="text-sm text-gray-500 mb-3">Created {{ formatDateTime(post.created_at) }}</div>
                <p class="text-gray-700 mb-4">{{ post.content }}</p>
                
                <div class="flex flex-wrap items-center gap-2">
                  <button class="bg-green-50 hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 border border-green-100" @click="approvePost(post.id)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Approve
                  </button>
                  <button class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium flex items-center gap-2 border border-red-100" @click="openReject(post.id)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                  </button>
                  <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50" @click="openComment(post.id)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Comment
                  </button>
                  <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50" @click="selectedPost = post; showPreview = true">
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
            <div class="flex items-center gap-2 mb-2" v-if="selectedPost">
              <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-semibold">{{ selectedPost.platform }}</span>
              <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs font-semibold">{{ selectedPost.postType }}</span>
            </div>
            <img
              v-if="selectedPost && selectedPost.media"
              :src="typeof selectedPost.media === 'string' ? selectedPost.media : (Array.isArray(selectedPost.media) && selectedPost.media.length ? selectedPost.media[0] : '')"
              alt="Post preview"
              class="rounded-xl w-full object-cover mb-2"
              style="max-height: 300px;"
            />
            <div class="mb-2" v-if="selectedPost">{{ selectedPost.content }}</div>
            <div class="text-gray-700 text-sm" v-if="selectedPost">
              <span class="font-semibold">Scheduled:</span> {{ formatDateTime((selectedPost as any).scheduleDate ?? selectedPost.created_at) }}<br>
              <span class="font-semibold">Platforms:</span> {{ selectedPost.platform }}
            </div>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Feedback Modal for Add Comment and Reject -->
      <FeedbackModal
        v-model:open="showFeedback"
        v-model:feedbackText="feedbackText"
        :feedbackType="feedbackType"
        @submit="submitFeedback"
        @cancel="showFeedback = false"
      />
    </div>
  </AppLayout>
</template>
