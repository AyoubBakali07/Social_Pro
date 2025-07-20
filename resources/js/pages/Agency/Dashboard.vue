<script setup lang="ts">
const props = defineProps<{ stats: Array<{ label: string; value: number; color: string; icon: string }>, posts: Array<any> }>();

import { watch, ref, reactive, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { EventDropArg } from '@fullcalendar/core';
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import rrulePlugin from '@fullcalendar/rrule'
// Dialog components
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';

// Platform Icons
import { Facebook, Instagram, Linkedin, Twitter } from 'lucide-vue-next';
import StatCard from '@/components/ui/card/StatCard.vue'
import ScheduledPostCard from '@/components/ui/ScheduledPostCard.vue'

// Map platform names to Vue icon components (for modal pill)
const platformIconComponents = {
  Facebook,
  Instagram,
  Twitter,
  LinkedIn: Linkedin,
};

// Map platform names to SVG strings (for FullCalendar event cards)
const platformIconSvgs = {
  Facebook: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-blue-600" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3.28l.72-4H14V7a1 1 0 0 1 1-1h3z"/></svg>`,
  
  Instagram: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-pink-600" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>`,
  Twitter: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-sky-500" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.4 1.64a9.09 9.09 0 0 1-2.88 1.1A4.48 4.48 0 0 0 16.5 0c-2.5 0-4.5 2.01-4.5 4.5 0 .35.04.7.11 1.03A12.94 12.94 0 0 1 3 1.13a4.48 4.48 0 0 0-.61 2.27c0 1.56.8 2.94 2.02 3.75A4.48 4.48 0 0 1 2 6.13v.06c0 2.18 1.55 4 3.8 4.42a4.52 4.52 0 0 1-2.04.08c.57 1.78 2.23 3.08 4.2 3.12A9.05 9.05 0 0 1 1 19.54a12.8 12.8 0 0 0 6.95 2.04c8.36 0 12.94-6.93 12.94-12.94 0-.2 0-.39-.01-.58A9.22 9.22 0 0 0 23 3z"/></svg>`,
  LinkedIn: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 text-blue-800" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="16" y1="8" x2="16" y2="16"/><line x1="8" y1="8" x2="8" y2="16"/><line x1="12" y1="12" x2="12" y2="16"/></svg>`,
};

// Platform pill color classes
const platformPillColors = {
  Facebook: 'bg-blue-100 text-blue-600',
  Instagram: 'bg-pink-100 text-pink-600',
  Twitter: 'bg-gray-100 text-gray-800',
  LinkedIn: 'bg-blue-50 text-blue-800',
};

// Add template ref for FullCalendar
const calendarRef = ref<InstanceType<typeof FullCalendar>>();

// Modal state and form fields
const showScheduleModal = ref(false);
const showEventDetails = ref(false);
const selectedEvent = ref<any>(null);

const form = ref({
  platform: 'Facebook',
  content: '',
  media: [] as File[],
  mediaPreviews: [] as { url: string, type: string, name: string }[],
  datetime: '',
  client: '',
  postType: 'Post',
});
const platforms = ['Facebook', 'Instagram', 'Twitter', 'LinkedIn'];

// Computed property for dynamic post types based on selected platform
const availablePostTypes = computed(() => {
  const config = platformConfig[form.value.platform as keyof typeof platformConfig];
  return config?.postTypes || ['Post'];
});
const clients = ['ABC Company', 'XYZ Brand']; // Example clients

function openScheduleModal() {
  showScheduleModal.value = true;
}

function closeScheduleModal() {
  showScheduleModal.value = false;
  // Reset form
  form.value = {
    platform: 'Facebook',
    content: '',
    media: [],
    mediaPreviews: [],
    datetime: '',
    client: '',
    postType: 'Post',
  };
}

function handleFileUpload(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    form.value.media = Array.from(target.files);
    form.value.mediaPreviews = form.value.media.map(file => ({
      url: URL.createObjectURL(file),
      type: file.type,
      name: file.name
    }));
  }
}

// Platform and post type configurations
const platformConfig = {
  'Facebook': {
    shortName: 'FB',
    postTypes: ['Post', 'Story', 'Reel']
  },
  'Instagram': {
    shortName: 'IG',
    postTypes: ['Post', 'Story', 'Reel', 'Carousel']
  },
  'Twitter': {
    shortName: 'X',
    postTypes: ['Post', 'Thread']
  },
  'LinkedIn': {
    shortName: 'IN',
    postTypes: ['Post', 'Article']
  }
};

const postTypeIcons = {
  'Post': '📝',
  'Story': '📱',
  'Reel': '🎬',
  'Carousel': '🎠',
  'Thread': '🧵',
  'Article': '📄'
};

function getPlatformColor(platform: string): string {
  return platformConfig[platform as keyof typeof platformConfig]?.shortName ;
}

function getEventTitle(platform: string, postType: string): string {
  const config = platformConfig[platform as keyof typeof platformConfig];
  const postIcon = postTypeIcons[postType as keyof typeof postTypeIcons];
  return ` ${postIcon || '📝'} ${config?.shortName || platform} ${postType}`;
}

function getTextPreview(text: string, maxLength = 40) {
  if (!text) return '';
  return text.length > maxLength ? text.slice(0, maxLength) + '…' : text;
}

function submitSchedule() {
    console.log('Raw datetime from form:', form.value.datetime);
    let startValue = '';
    let allDay = false;
    if (form.value.datetime) {
        startValue = form.value.datetime;
        // If the datetime string has no time part (just a date), treat as all-day
        allDay = !form.value.datetime.includes('T') || form.value.datetime.endsWith('T00:00');
        console.log('Start value for event:', startValue, 'allDay:', allDay);
    }
    
    const newEvent = {
        title: getEventTitle(form.value.platform, form.value.postType),
        start: startValue,
        allDay: allDay,
        backgroundColor: getPlatformColor(form.value.platform),
        borderColor: getPlatformColor(form.value.platform),
        extendedProps: {
            client: form.value.client,
            content: form.value.content,
            platform: form.value.platform,
            postType: form.value.postType,
            // Add media preview if available
            mediaList: form.value.mediaPreviews
        }
    };
    
    console.log('New event to add:', newEvent);
    
    // Add to reactive array for state management
    events.value.push(newEvent);
    
    // IMPORTANT: Add the event directly to FullCalendar using its API
    if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        calendarApi.addEvent(newEvent);
        console.log('Event added to calendar via API');
    }
    
    closeScheduleModal();
}

function handleDeleteEvent(eventId: string) {
  // For now, just log. Integrate with backend later.
  console.log('Delete event with id:', eventId);
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

import type { EventInput } from '@fullcalendar/core';

const events = computed<EventInput[]>(() =>
  (props.posts ?? []).map(post => {
    // Determine color by platform
    let backgroundColor = '#2563eb';
    let borderColor = '#2563eb';
    if (post.platform === 'Facebook') {
      backgroundColor = '#1877F2'; borderColor = '#1877F2';
    } else if (post.platform === 'Instagram') {
      backgroundColor = '#E4405F'; borderColor = '#E4405F';
    } else if (post.platform === 'Twitter') {
      backgroundColor = '#1DA1F2'; borderColor = '#1DA1F2';
    } else if (post.platform === 'LinkedIn') {
      backgroundColor = '#0077B5'; borderColor = '#0077B5';
    }
    // All-day if no time part
    const allDay = post.scheduleDate && post.scheduleDate.length <= 10;
    return {
      id: post.id,
      title: `${post.platform ? post.platform.charAt(0) : ''}${post.postType ? ' ' + post.postType : ''}`,
      start: post.scheduleDate,
      allDay,
      backgroundColor,
      borderColor,
      extendedProps: {
        content: post.content,
        platform: post.platform,
        postType: post.postType,
        client: post.client,
        media: post.media,
        feedback: post.feedback,
        status: post.status,
        created_at: post.created_at,
      }
    };
  })
);

watch(events, (val) => {
  console.log('FullCalendar events.value:', JSON.stringify(val, null, 2));
}, { immediate: true });

const calendarOptions = reactive({
  plugins: [dayGridPlugin, interactionPlugin, rrulePlugin],
  initialView: 'dayGridMonth',
  editable: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,dayGridWeek,dayGridDay'
  },
  eventContent: function(arg: any) {
    const postType = arg.event.extendedProps?.postType?.toLowerCase() || '';
    let icon = '';
    let pillBg = '';
    let pillText = '';
    if (postType === 'story') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="3" y="3" width="18" height="18" rx="5" stroke="#d97706"/><circle cx="12" cy="12" r="4" stroke="#d97706"/></svg>`;
      pillBg = 'bg-yellow-100';
      pillText = 'text-yellow-800';
    } else if (postType === 'carousel') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="7" y="7" width="10" height="10" rx="2" stroke="#ef4444"/><rect x="3" y="3" width="10" height="10" rx="2" stroke="#ef4444"/></svg>`;
      pillBg = 'bg-red-100';
      pillText = 'text-red-800';
    } else if (postType === 'reel') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="3" y="5" width="18" height="14" rx="3" stroke="#22c55e"/><polygon points="10,9 16,12 10,15" fill="#22c55e"/></svg>`;
      pillBg = 'bg-green-100';
      pillText = 'text-green-800';
    } else if (postType === 'post') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="4" y="4" width="16" height="16" rx="4" stroke="#64748b"/><circle cx="12" cy="12" r="4" stroke="#64748b"/></svg>`;
      pillBg = 'bg-gray-100';
      pillText = 'text-gray-800';
    } else {
      icon = '';
      pillBg = 'bg-gray-100';
      pillText = 'text-gray-800';
    }
    let shortName = '';
    const eventPlatform = arg.event.extendedProps?.platform || '';
    if (eventPlatform && platformConfig[eventPlatform as keyof typeof platformConfig]) {
      shortName = platformConfig[eventPlatform as keyof typeof platformConfig].shortName;
    }
    let label = shortName && postType ? `${shortName.toUpperCase()} ${postType.charAt(0).toUpperCase() + postType.slice(1)}` : (postType || arg.event.title);
    const client = arg.event.extendedProps?.client;
    if (client) {
      label += ` - ${client.slice(0, 5) +'...'}`;
    }
    // Media preview
    const mediaList = arg.event.extendedProps?.mediaList || [];
    let mediaHtml = '';
    if (mediaList.length) {
      const first = mediaList[0];
      if (first.type.startsWith('image')) {
        mediaHtml = `<div class='w-full aspect-square mb-1'><img src='${first.url}' class='w-full h-full object-cover rounded border' style='aspect-ratio:1/1;'/></div>`;
      } else if (first.type.startsWith('video')) {
        mediaHtml = `<div class='w-full aspect-square mb-1'><video src='${first.url}' class='w-full rounded border' style='aspect-ratio:1/1;' muted></video></div>`;
      }
    }
    // Time preview
    let timeHtml = '';
    if (!arg.event.allDay && arg.event.start) {
      const date = new Date(arg.event.start);
      const hours = date.getHours().toString().padStart(2, '0');
      const minutes = date.getMinutes().toString().padStart(2, '0');
      timeHtml = `<span class='block text-xs font-bold text-gray-800 mb-1'>${hours}:${minutes}</span>`;
    }
    // Add X icon for delete (absolute, only on hover)
    const xIcon = `<span class='absolute top-2 right-2 z-20 hidden group-hover:flex items-center justify-center w-6 h-6 rounded-full bg-white border border-gray-300 shadow cursor-pointer delete-x' title='Delete'>
      <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l8 8M6 14L14 6"/></svg>
    </span>`;
    const platformIconSvg = eventPlatform && platformIconSvgs[eventPlatform as keyof typeof platformIconSvgs] ? platformIconSvgs[eventPlatform as keyof typeof platformIconSvgs] : '';
    const pillColor = eventPlatform && platformPillColors[eventPlatform as keyof typeof platformPillColors] ? platformPillColors[eventPlatform as keyof typeof platformPillColors] : 'bg-gray-100 text-gray-800';
    return {
      html: `
        <div class="fc-event-card relative rounded-xl border border-gray-200 p-2 flex flex-col items-start w-full group cursor-pointer">
          ${xIcon}
          ${timeHtml}
          <span class="inline-flex items-center gap-1 mb-1 px-2 py-0.5 rounded-full text-xs font-semibold ${pillColor}">
            ${platformIconSvg}
            <span>${label}</span>
          </span>
          ${mediaHtml}
        </div>
      `
    };
  },
  eventDrop: function(info: EventDropArg) {
    // Handle the event drop (update your data here)
    // alert(`Event moved to ${info.event.startStr}`);
    // You can also update your backend or local state here.
  },
  // Add event click handler for debugging
  eventClick: function(info: any) {
    selectedEvent.value = info.event;
    showEventDetails.value = true;
  },
  // Force events to use their individual colors
  eventDidMount: function(info: any) {
    if (info.event.backgroundColor) {
      info.el.style.backgroundColor = info.event.backgroundColor;
      info.el.style.borderColor = info.event.borderColor || info.event.backgroundColor;
    }
    // Add delete X icon click handler
    const x = info.el.querySelector('.delete-x');
    if (x) {
      x.addEventListener('click', (e: Event) => {
        e.stopPropagation();
        handleDeleteEvent(info.event.id);
      });
    }
  }
})

function getShortFileName(name: string, maxBase = 10) {
  const dotIdx = name.lastIndexOf('.');
  if (dotIdx === -1) return name.length > maxBase ? name.slice(0, maxBase) + '…' : name;
  const base = name.slice(0, dotIdx);
  const ext = name.slice(dotIdx);
  return base.length > maxBase ? base.slice(0, maxBase) + '…' + ext : name;
}

const mediaCarouselIndex = ref(0);
watch(showEventDetails, (open) => {
  if (open) mediaCarouselIndex.value = 0;
});

// Add these computed properties in <script setup lang="ts">


const postDetailsPill = computed(() => {
  if (!selectedEvent.value) return { icon: null, label: '', color: '' };
  const modalPlatform = selectedEvent.value.extendedProps?.platform;
  const postType = selectedEvent.value.extendedProps?.postType;
    const shortName = modalPlatform && platformConfig[modalPlatform as keyof typeof platformConfig]?.shortName ? platformConfig[modalPlatform as keyof typeof platformConfig].shortName : '';
  const icon = modalPlatform && platformIconComponents[modalPlatform as keyof typeof platformIconComponents] ? platformIconComponents[modalPlatform as keyof typeof platformIconComponents] : null;
  const color = modalPlatform && platformPillColors[modalPlatform as keyof typeof platformPillColors] ? platformPillColors[modalPlatform as keyof typeof platformPillColors] : 'bg-gray-100 text-gray-800';
  return {
    icon,
    label: `${shortName} ${postType || ''}`.trim(),
    color
  };
});

const formattedSchedule = computed(() => {
  if (!selectedEvent.value?.start) return '';
  const date = new Date(selectedEvent.value.start);
  return date.toLocaleString(undefined, {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: true
  });
});

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div> -->
            </div>
            <!-- Dashboard Summary Section -->
            <div class="mb-6 flex flex-col items-center">
                <div class="mb-6 w-full flex flex-col items-start">
                    <h2 class="text-2xl font-semibold mb-2">Agency Dashboard</h2>
                    <p class="text-sm text-muted-foreground">Manage your clients and social media campaigns</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 w-full mb-8">
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
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold">Content Calendar</h2>
                <div class="flex items-center gap-3">
                    <button 
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50"
                        @click="openScheduleModal"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Schedule Post
                    </button>
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-200 p-4 bg-white">
                <!-- Add ref to FullCalendar -->
                <FullCalendar 
                  ref="calendarRef" 
                  :options="calendarOptions" 
                  :events="events"
                />
            </div>
        </div>
    </AppLayout>

    <!-- Schedule Post Modal -->
    <Dialog v-model:open="showScheduleModal">
      <DialogContent class="max-w-lg w-full">
        <DialogHeader>
          <DialogTitle>Schedule New Post</DialogTitle>
          <DialogDescription>
            Fill in the details to schedule a new post.
          </DialogDescription>
        </DialogHeader>
        <form @submit.prevent="submitSchedule" class="flex flex-col gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Platform</label>
            <select v-model="form.platform" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]">
              <option v-for="p in platforms" :key="p" :value="p">
                {{ p }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select v-model="form.postType" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]">
              <option v-for="type in availablePostTypes" :key="type" :value="type">
                {{ postTypeIcons[type as keyof typeof postTypeIcons] }} {{ type }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Content</label>
            <textarea v-model="form.content" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]" rows="3" placeholder="Write your post content..."></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Media Upload</label>
            <input type="file" accept="image/*,video/*" multiple @change="handleFileUpload" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]" />
            <div v-if="form.mediaPreviews.length" class="flex flex-row gap-4 mt-2">
              <div v-for="(preview, idx) in form.mediaPreviews" :key="preview.url" class="flex flex-col items-center w-24">
                <img v-if="preview.type.startsWith('image')" :src="preview.url" class="w-20 h-20 object-cover rounded border mb-1" />
                <video v-else controls :src="preview.url" class="w-20 h-20 object-cover rounded border mb-1" />
                <div class="text-xs text-green-600 break-all text-center">{{ getShortFileName(preview.name, 8) }}</div>
              </div>
            </div>
            <div class="text-xs text-muted-foreground">PNG, JPG, GIF up to 10MB</div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Schedule Date & Time</label>
            <input v-model="form.datetime" type="datetime-local" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]" />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Client</label>
            <select v-model="form.client" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]">
              <option v-for="c in clients" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
          <DialogFooter class="mt-4 flex justify-end gap-3">
            <button 
              type="button" 
              class="inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              @click="closeScheduleModal"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Schedule Post
            </button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
    
    <!-- Event Details Modal -->
    <Dialog v-model:open="showEventDetails">
      <DialogContent class="max-w-lg w-full p-0 bg-gray-50 rounded-2xl max-h-[85vh] overflow-y-auto">
        <div class="flex flex-col gap-4 p-6">
          <ScheduledPostCard
            :platform="selectedEvent?.extendedProps?.platform"
            :postType="selectedEvent?.extendedProps?.postType"
            :scheduledFor="selectedEvent?.start"
            :media="selectedEvent?.extendedProps?.mediaList || []"
            :content="selectedEvent?.extendedProps?.content"
            :client="selectedEvent?.extendedProps?.client"
          />
        </div>
      </DialogContent>
    </Dialog>
</template>

<style>
/* FullCalendar button style overrides for modern, accessible UI */
.fc .fc-button, .fc .fc-button-primary {
  background-color: #fff !important;
  color: #2563eb !important; /* blue-600 */
  border: 1px solid #2563eb !important; /* blue-600 */
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