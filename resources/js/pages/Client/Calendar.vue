<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';
import type { EventInput } from '@fullcalendar/core';

// Type for our posts
interface Post {
  id: number;
  content: string;
  scheduleDate: string | Date | null;
  status: 'draft' | 'pending' | 'approved' | 'rejected' | 'scheduled';
  title?: string;
  platform?: string;
  postType?: string;
  created_at?: string;
  media?: string[] | string | null;
}


// Environment type for Vite
interface ImportMeta {
  env: {
    DEV: boolean;
    PROD: boolean;
    MODE: string;
  };
}

// Development flag
const isDevelopment = import.meta.env.DEV;

// Error state
const calendarError = ref<string | null>(null);
const calendar = ref(null);

// Initialize with error handling
try {
  // Check if FullCalendar is properly imported
  if (!FullCalendar) {
    throw new Error('FullCalendar component failed to load');
  }
  
  // Check if plugins are available
  if (!dayGridPlugin || !interactionPlugin || !rrulePlugin) {
    throw new Error('One or more FullCalendar plugins failed to load');
  }
} catch (error: any) {
  console.error('Calendar initialization error:', error);
  calendarError.value = error?.message || 'Failed to initialize calendar';
}

const { props } = usePage();

// Type assertion for page props
const pageProps = props as { posts?: Post[] };

// Get posts from the page props
const posts = ref<Post[]>(Array.isArray(pageProps.posts) ? pageProps.posts : []);

// Log the posts for debugging
onMounted(() => {
  if (!isDevelopment) return;
  console.log('Component mounted with posts:', posts.value);
  console.log('Number of posts:', posts.value.length);
  
  if (posts.value.length > 0) {
    console.log('Sample post:', {
      id: posts.value[0].id,
      content: posts.value[0].content,
      scheduleDate: posts.value[0].scheduleDate,
      status: posts.value[0].status
    });
  }
  
  console.log('Computed events:', events.value);
});

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
  return typeof media === 'string' && media.length ? [media] : [];
}

function formatCalendarEvents(postList: Post[]): EventInput[] {
  if (!Array.isArray(postList)) return [];

  return postList
    .filter((post): post is Post => !!post && typeof post === 'object')
    .map((post) => {
      const color = getPlatformColor(post.platform || '');
      const media = normalizeMedia(post.media);
      const startDate = post.scheduleDate || post.created_at || new Date().toISOString();

      return {
        id: String(post.id),
        title: post.title || (post.content ? post.content.substring(0, 40) : 'Scheduled Post'),
        start: startDate,
        allDay: true,
        backgroundColor: color,
        borderColor: color,
        extendedProps: {
          ...post,
          media,
          scheduleDate: startDate,
          postType: post.postType || 'Post',
          platform: post.platform || 'Platform',
        },
      };
    });
}

function renderEventContent(arg: any) {
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

const events = computed<EventInput[]>(() => {
  if (isDevelopment) console.log('Recomputing events...');
  try {
    if (!Array.isArray(posts.value)) {
      console.error('Posts is not an array:', posts.value);
      return [];
    }
    const formatted = formatCalendarEvents(posts.value);
    if (isDevelopment) console.log('Formatted events:', formatted);
    return formatted;
  } catch (error: unknown) {
    console.error('Error generating events:', error);
    return [];
  }
});

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, rrulePlugin],
  initialView: 'dayGridMonth',
  editable: false,
  selectable: true,
  dayMaxEvents: 3,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek,dayGridDay',
  },
  eventDisplay: 'block',
  eventTimeFormat: {
    hour: '2-digit' as const,
    minute: '2-digit' as const,
    meridiem: false,
    hour12: true
  },
  eventClick: (info: any) => {
    const event = info.event;
    if (isDevelopment) console.log('Event clicked:', event);
  },
  eventContent: renderEventContent
});

</script>

<template>
  <AppLayout>
    <Head title="Calendar" />
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden sm:rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Content Calendar</h2>
          
          <div v-if="calendarError" class="bg-red-50 text-red-700 p-4 rounded mb-4">
            {{ calendarError }}
          </div>
          
          <div class="w-full bg-white border border-gray-200 rounded-xl p-4">
            <div v-if="!calendarError" class="calendar-container">
              <FullCalendar 
                ref="calendar"
                :options="{
                  ...calendarOptions,
                  events: events,
                  height: 'auto',
                  headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek,dayGridDay'
                  },
                  eventDidMount: (info: any) => {
                    if (isDevelopment) console.log('Event mounted:', info.event);
                  },
                  eventClick: (info: any) => {
                    const event = info.event;
                    if (isDevelopment) console.log('Event clicked:', event);
                  },
                  datesSet: (dateInfo: any) => {
                    if (isDevelopment) console.log('View changed:', dateInfo.view.type, dateInfo.view.title);
                  },
                  loading: (isLoading: boolean) => {
                    if (isDevelopment) console.log('Calendar loading:', isLoading);
                  }
                }"
              />
            </div>
          </div>
          
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
/* Ensure the calendar takes full width and height of its container */
.calendar-container {
  width: 100%;
  min-height: 600px; /* Set a minimum height */
}

/* Style for FullCalendar */
:root {
  --fc-border-color: #e5e7eb;
  --fc-today-bg-color: #f3f4f6;
  --fc-button-bg-color: #3b82f6;
  --fc-button-border-color: #3b82f6;
  --fc-button-hover-bg-color: #2563eb;
  --fc-button-hover-border-color: #2563eb;
  --fc-button-active-bg-color: #1d4ed8;
  --fc-button-active-border-color: #1d4ed8;
}

/* Ensure the calendar has proper spacing */
.fc {
  width: 100%;
  height: 100%;
  margin: 0 auto;
}

/* Style for events */
.fc-event {
  cursor: pointer;
  border: none;
  font-size: 0.875rem;
  padding: 2px 4px;
  margin: 1px 0;
}

/* Style for event dots */
.fc-event-dot {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 4px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .fc-toolbar {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .fc-toolbar-title {
    margin: 0.5rem 0;
  }
}
</style>

<style scoped>
/* Minimal style for calendar container */
/* .fc {
  background: white;
  border-radius: 0.75rem;
  padding: 1rem;
} */

/* FullCalendar button style overrides for a modern, accessible UI */
.fc .fc-button, .fc .fc-button-primary {
  background-color: #fff !important;
  color: #2563eb !important; /* blue-600 */
  border: 1px solidrgb(40, 42, 45) !important; /* blue-600 */
  border-radius: 0.5rem !important; /* rounded-lg */
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
.fc .fc-button:hover, .fc .fc-button-primary:hover {
  background-color: #eff6ff !important; /* blue-50 */
  color: #1d4ed8 !important; /* blue-700 */
  border-color: #1d4ed8 !important; /* blue-700 */
}
.fc .fc-button:focus, .fc .fc-button-primary:focus {
  outline: none !important;
  box-shadow: 0 0 0 3px #bfdbfe !important; /* blue-200 */
}
.fc .fc-button-active, .fc .fc-button-primary.fc-button-active {
  background-color: #2563eb !important; /* blue-600 */
  color: #fff !important;
  border-color: #2563eb !important;
}
.fc .fc-button-group {
  gap: 0.5rem;
}
.fc .fc-button .fc-icon {
  font-size: 1.2em;
  vertical-align: middle;
}
.fc-custom-event.group:hover .group-hover\:inline-block {
  display: inline-block !important;
}
.fc-event-card .delete-x {
  display: none;
}
.fc-event-card.group:hover .delete-x {
  display: flex !important;
}
</style> 
