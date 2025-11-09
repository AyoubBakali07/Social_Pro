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
      router.reload({ only: ['stats', 'pendingPosts', 'calendarPosts'] })
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
        router.reload({ only: ['stats', 'pendingPosts', 'calendarPosts'] })
      }
    })
  } else {
    await router.post(`/client/posts/${actingPostId.value}/reject`, { feedback: feedbackText.value }, {
      preserveScroll: true,
      onSuccess: () => {
        t.success('Post rejected with feedback', { position: POSITION.TOP_CENTER })
        router.reload({ only: ['stats', 'pendingPosts', 'calendarPosts'] })
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
  Facebook: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-blue-600"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.988h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.462h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>`,
  Instagram: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-pink-600"><path d="M7 2C4.243 2 2 4.243 2 7v10c0 2.757 2.243 5 5 5h10c2.757 0 5-2.243 5-5V7c0-2.757-2.243-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10zm-5 3a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6zm5-3a1 1 0 100 2 1 1 0 000-2z"/></svg>`,
  Twitter: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-sky-500"><path d="M22.162 5.656a5.658 5.658 0 01-1.59.435 2.777 2.777 0 001.238-1.535 5.556 5.556 0 01-1.764.672 2.777 2.777 0 00-4.729 2.53 7.89 7.89 0 01-5.732-2.908 2.777 2.777 0 00.86 3.705 2.757 2.757 0 01-1.258-.347v.035a2.776 2.776 0 002.227 2.724 2.777 2.777 0 01-1.252.048 2.777 2.777 0 002.592 1.927A5.572 5.572 0 013 16.29a7.863 7.863 0 004.26 1.247c5.134 0 7.94-4.253 7.94-7.94 0-.121-.003-.242-.009-.362a5.664 5.664 0 001.39-1.448z"/></svg>`,
  TikTok: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-black"><path d="M21 8.5a6.5 6.5 0 01-4.5-1.84v7.1a6.22 6.22 0 11-5.4-6.16v3.39a2.9 2.9 0 102 2.74V2h3.1a3.4 3.4 0 003.3 3.12z"/></svg>`,
  LinkedIn: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-blue-700"><path d="M20.451 20.451h-3.554v-5.569c0-1.327-.027-3.036-1.849-3.036-1.851 0-2.134 1.445-2.134 2.939v5.666H9.358V9h3.414v1.561h.049c.476-.9 1.637-1.849 3.369-1.849 3.601 0 4.267 2.372 4.267 5.455v6.284zM5.337 7.433a2.062 2.062 0 110-4.124 2.062 2.062 0 010 4.124zm1.777 13.018H3.56V9h3.554v11.451zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.225.792 24 1.771 24h20.451c.98 0 1.772-.775 1.772-1.729V1.729C24 .774 23.207 0 22.225 0z"/></svg>`
};

const platformPillColors: Record<string, string> = {
  Facebook: 'bg-blue-100 text-blue-600',
  Instagram: 'bg-pink-100 text-pink-600',
  Twitter: 'bg-gray-100 text-gray-800',
  TikTok: 'bg-cyan-500 text-white',
  LinkedIn: 'bg-blue-50 text-blue-800',
};

const escapeHtml = (value: unknown): string => {
  if (typeof value !== 'string') return '';
  return value.replace(/[&<>"']/g, (char) => {
    const map: Record<string, string> = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
    };
    return map[char] || char;
  });
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

  const safeContent = escapeHtml(arg.event.extendedProps?.content || '');
  const contentHtml = mediaHtml
    ? ''
    : `<p class="text-xs text-gray-700 leading-tight truncate">${safeContent}</p>`;

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

function badgeClass(platform?: string) {
  const map: Record<string, string> = {
    Facebook: 'bg-blue-500/10 text-blue-600',
    Instagram: 'bg-pink-500/10 text-pink-600',
    Twitter: 'bg-sky-500/10 text-sky-600',
    TikTok: 'bg-gray-900/10 text-gray-900',
    LinkedIn: 'bg-blue-800/10 text-blue-800'
  };
  return map[platform || ''] || 'bg-gray-200 text-gray-700';
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
                <span v-html="platformIconSvgs[post.platform as keyof typeof platformIconSvgs] || platformIconSvgs.Facebook"></span>
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
        <DialogContent class="max-w-2xl p-0 overflow-hidden">
          <div class="flex justify-between items-start px-6 pt-5">
            <div>
              <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Post Preview</p>
              <h3 class="text-2xl font-semibold text-gray-900" v-if="selectedPost">{{ selectedPost.title || 'Scheduled Post' }}</h3>
            </div>
            <DialogClose class="text-gray-400 hover:text-gray-600 transition-colors" />
          </div>

          <div v-if="selectedPost" class="px-6 pt-4 pb-6 space-y-6">
            <div class="flex flex-wrap items-center gap-3">
              <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-semibold" :class="badgeClass(selectedPost.platform)">
                <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-white/20 text-white" v-html="platformIconSvgs[selectedPost.platform as keyof typeof platformIconSvgs] || ''"></span>
                {{ selectedPost.platform }}
              </span>
              <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 px-3 py-1 text-xs font-semibold tracking-wide">{{ selectedPost.postType }}</span>
            </div>

            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
              <img
                v-if="selectedPost.media && selectedPost.media.length"
                :src="typeof selectedPost.media === 'string' ? selectedPost.media : (Array.isArray(selectedPost.media) && selectedPost.media.length ? selectedPost.media[0] : '')"
                alt="Post preview"
                class="rounded-xl w-full object-cover shadow-sm"
                style="max-height: 320px;"
              />
              <div v-else class="h-48 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 text-sm font-medium">
                No media uploaded
              </div>
            </div>

            <div class="space-y-4">
              <p class="text-base text-gray-700 leading-relaxed">{{ selectedPost.content }}</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl border border-gray-100 p-4 bg-white shadow-sm">
                  <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Scheduled</p>
                  <p class="text-gray-900 font-semibold">{{ formatDateTime((selectedPost as any).scheduleDate ?? selectedPost.created_at) }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 p-4 bg-white shadow-sm">
                  <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Platform</p>
                  <p class="text-gray-900 font-semibold">{{ selectedPost.platform }}</p>
                </div>
              </div>
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
