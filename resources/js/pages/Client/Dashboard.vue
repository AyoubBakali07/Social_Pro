<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast, POSITION } from 'vue-toastification';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';
import FullCalendar from '@fullcalendar/vue3';
import type { EventInput, EventClickArg, EventContentArg } from '@fullcalendar/core';
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
const t = useToast()

async function approvePost(postId: number) {
  await router.post(`/client/posts/${postId}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      t.success('Post approved', { position: POSITION.TOP_CENTER })
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
        t.success('Comment added', { position: POSITION.TOP_CENTER })
        router.reload({ only: ['stats', 'pendingPosts'] })
      }
    })
  } else {
    await router.post(`/client/posts/${actingPostId.value}/reject`, { feedback: feedbackText.value }, {
      preserveScroll: true,
      onSuccess: () => {
        t.success('Post rejected with feedback', { position: POSITION.TOP_CENTER })
        router.reload({ only: ['stats', 'pendingPosts'] })
      }
    })
  }
  showFeedback.value = false
}

// Toastify server flash messages (success/error)
const page = usePage();
watch(() => (page.props as any).flash?.success, (message) => {
  if (message) t.success(message, { position: POSITION.TOP_CENTER })
}, { immediate: true });
watch(() => (page.props as any).flash?.error, (message) => {
  if (message) t.error(message, { position: POSITION.TOP_CENTER })
}, { immediate: true });

const platformIconSvgs = {
  Facebook: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-blue-600" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3.28l.72-4H14V7a1 1 0 0 1 1-1h3z"/></svg>`,
  Instagram: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-pink-600" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>`,
  Twitter: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-sky-500" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.4 1.64a9.09 9.09 0 0 1-2.88 1.1A4.48 4.48 0 0 0 16.5 0c-2.5 0-4.5 2.01-4.5 4.5 0 .35.04.7.11 1.03A12.94 12.94 0 0 1 3 1.13a4.48 4.48 0 0 0-.61 2.27c0 1.56.8 2.94 2.02 3.75A4.48 4.48 0 0 1 2 6.13v.06c0 2.18 1.55 4 3.8 4.42a4.52 4.52 0 0 1-2.04.08c.57 1.78 2.23 3.08 4.2 3.12A9.05 9.05 0 0 1 1 19.54a12.8 12.8 0 0 0 6.95 2.04c8.36 0 12.94-6.93 12.94-12.94 0-.2 0-.39-.01-.58A9.22 9.22 0 0 0 23 3z"/></svg>`,
  TikTok: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-black" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>`,
  LinkedIn: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-blue-800" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="16" y1="8" x2="8" y2="16"/><line x1="12" y1="12" x2="12" y2="16"/></svg>`,
};

const platformPillColors: Record<string, string> = {
  Facebook: 'bg-blue-100 text-blue-600',
  Instagram: 'bg-pink-100 text-pink-600',
  Twitter: 'bg-gray-100 text-gray-800',
  TikTok: 'bg-cyan-500 text-white',
  LinkedIn: 'bg-blue-50 text-blue-800',
};

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

function normalizeMedia(media?: Post['media']): string[] {
  if (!media) return [];
  if (Array.isArray(media)) {
    return media.filter(Boolean) as string[];
  }
  return media ? [media] : [];
}

function formatCalendarEvents(posts: any[]): EventInput[] {
  if (!Array.isArray(posts)) return [];

  return posts
    .filter((post) => !!post)
    .map((post) => {
      const color = getPlatformColor(post.platform || '');
      const media = normalizeMedia(post.media);
      const startDate = post.scheduleDate || post.created_at;

      return {
        id: String(post.id),
        title: post.title || (post.content ? String(post.content).slice(0, 40) : 'Scheduled Post'),
        start: startDate,
        allDay: true,
        backgroundColor: color,
        borderColor: color,
        extendedProps: {
          ...post,
          media,
          scheduleDate: startDate,
          postType: post.postType || post.post_type || 'Post',
        },
      };
    });
}

function renderEventContent(arg: EventContentArg) {
  if (!arg?.event) return null;
  const eventPlatform = arg.event.extendedProps?.platform || '';
  const platformIconSvg = platformIconSvgs[eventPlatform as keyof typeof platformIconSvgs] || '';
  const pillColor = platformPillColors[eventPlatform] || 'bg-gray-100 text-gray-800';
  const label = arg.event.extendedProps?.postType || arg.event.title || 'Post';
  const mediaList: string[] = arg.event.extendedProps?.media || [];

  let mediaHtml = '';
  if (mediaList.length > 0) {
    const mediaUrl = mediaList[0];
    const isImage = /\.(jpe?g|png|gif|webp)$/i.test(mediaUrl);
    const isVideo = /\.(mp4|mov|webm)$/i.test(mediaUrl);
    if (isImage) {
      mediaHtml = `<img src="${mediaUrl}" class="w-full h-24 object-cover rounded-lg border mb-1" alt="Post preview" />`;
    } else if (isVideo) {
      mediaHtml = `<video src="${mediaUrl}" class="w-full h-24 object-cover rounded-lg border mb-1" muted></video>`;
    }
  }

  const contentHtml = mediaHtml
    ? ''
    : `<p class="text-xs text-gray-700 leading-tight truncate">${arg.event.extendedProps?.content || ''}</p>`;

  return {
    html: `
      <div class="fc-event-card rounded-xl border border-gray-200 p-2 bg-white shadow-sm flex flex-col gap-1 cursor-pointer">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold ${pillColor}">
          ${platformIconSvg}
          <span>${label}</span>
        </span>
        ${mediaHtml || contentHtml}
      </div>
    `
  };
}

function handleCalendarEventClick(info: EventClickArg) {
  const event = info.event;
  const extended = event.extendedProps || {};
  const media = normalizeMedia(extended.media);

  selectedPost.value = {
    id: Number(event.id),
    title: extended.title || event.title || 'Post',
    content: extended.content || '',
    platform: extended.platform || 'Platform',
    postType: extended.postType || 'Post',
    status: extended.status || 'scheduled',
    client_id: extended.client_id || 0,
    created_at: extended.created_at || (event.start ? event.start.toISOString() : new Date().toISOString()),
    scheduleDate: extended.scheduleDate || (event.start ? event.start.toISOString() : undefined),
    media,
  };

  showPreview.value = true;
  info.jsEvent?.preventDefault();
}

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, interactionPlugin, rrulePlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek,dayGridDay'
  },
  events: formatCalendarEvents(props.calendarPosts || []),
  eventContent: renderEventContent,
  eventClick: handleCalendarEventClick,
}));
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
          <FullCalendar :options="calendarOptions" />
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

<style>
.fc {
  width: 100%;
}

.fc .fc-button,
.fc .fc-button-primary {
  background-color: #fff !important;
  color: #2563eb !important;
  border: 1px solid #1d4ed8 !important;
  border-radius: 0.5rem !important;
  font-weight: 500;
  font-size: 1rem;
  min-width: 44px;
  min-height: 38px;
  padding: 0.5rem 1.25rem;
  box-shadow: none;
  transition: background 0.18s, color 0.18s, border 0.18s, box-shadow 0.18s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  outline: none;
}

.fc .fc-button:hover,
.fc .fc-button-primary:hover {
  background-color: #eff6ff !important;
  color: #1d4ed8 !important;
  border-color: #1d4ed8 !important;
}

.fc .fc-button:focus,
.fc .fc-button-primary:focus {
  outline: none !important;
  box-shadow: 0 0 0 3px #bfdbfe !important;
}

.fc .fc-button-active,
.fc .fc-button-primary.fc-button-active {
  background-color: #2563eb !important;
  color: #fff !important;
  border-color: #2563eb !important;
}

.fc .fc-button-group {
  gap: 0.5rem;
}
</style>
