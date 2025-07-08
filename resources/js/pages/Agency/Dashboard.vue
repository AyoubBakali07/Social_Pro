<script setup lang="ts">
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

// Add template ref for FullCalendar
const calendarRef = ref<InstanceType<typeof FullCalendar>>();

// Modal state and form fields
const showScheduleModal = ref(false);

const form = ref({
  platform: 'Facebook',
  content: '',
  media: null as File | null,
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
    media: null,
    datetime: '',
    client: '',
    postType: 'Post',
  };
}

function handleFileUpload(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.value.media = target.files[0];
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
        // Add some additional properties for better identification
        extendedProps: {
            client: form.value.client,
            content: form.value.content,
            platform: form.value.platform,
            postType: form.value.postType
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

import type { EventInput } from '@fullcalendar/core';

const events = ref<EventInput[]>([
  { 
    title: '📝 FB Post', 
    start: '2025-07-11', 
    allDay: true,
    backgroundColor: '#1877F2',
    borderColor: '#1877F2'
  },
  { 
    title: '🎬 IG Reel', 
    start: '2025-07-16T14:30', 
    allDay: false,
    backgroundColor: '#E4405F',
    borderColor: '#E4405F'
  },
  { 
    title: '📝 X Post', 
    start: '2025-07-15', 
    allDay: true,
    backgroundColor: '#1DA1F2',
    borderColor: '#1DA1F2'
  },
  { 
    title: '📝 IN Post', 
    start: '2025-07-06', 
    allDay: true,
    backgroundColor: '#0077B5',
    borderColor: '#0077B5'
  },
  {
    title: '📝 FB Weekly Post',
    rrule: {
      freq: 'weekly',
      interval: 1,
      byweekday: ['mo'],
      dtstart: '2025-07-07T10:00:00',
      until: '2025-12-31'
    },
    backgroundColor: '#1877F2',
    borderColor: '#1877F2'
  }
]);

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
    // Get type and platform from event
    const postType = arg.event.extendedProps?.postType?.toLowerCase() || '';
    // Icon SVG and styles for each type
    let icon = '';
    let bg = '';
    let text = '';
    let border = '';
    if (postType === 'story') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="3" y="3" width="18" height="18" rx="5" stroke="#d97706"/><circle cx="12" cy="12" r="4" stroke="#d97706"/></svg>`;
      bg = 'bg-yellow-100';
      text = 'text-yellow-800';
      border = 'border-yellow-200';
    } else if (postType === 'carousel') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="7" y="7" width="10" height="10" rx="2" stroke="#ef4444"/><rect x="3" y="3" width="10" height="10" rx="2" stroke="#ef4444"/></svg>`;
      bg = 'bg-red-100';
      text = 'text-red-800';
      border = 'border-red-200';
    } else if (postType === 'reel') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="3" y="5" width="18" height="14" rx="3" stroke="#22c55e"/><polygon points="10,9 16,12 10,15" fill="#22c55e"/></svg>`;
      bg = 'bg-green-100';
      text = 'text-green-800';
      border = 'border-green-200';
    } else if (postType === 'post') {
      icon = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="mr-1"><rect x="4" y="4" width="16" height="16" rx="4" stroke="#64748b"/><circle cx="12" cy="12" r="4" stroke="#64748b"/></svg>`;
      bg = 'bg-gray-100';
      text = 'text-gray-800';
      border = 'border-gray-200';
    } else {
      icon = '';
      bg = 'bg-gray-100';
      text = 'text-gray-800';
      border = 'border-gray-200';
    }
    // Compose pill label as 'FB post', 'IG reel', etc.
    let shortName = '';
    const platform = arg.event.extendedProps?.platform || '';
    if (platform && platformConfig[platform]) {
      shortName = platformConfig[platform].shortName;
    }
    const label = shortName && postType ? `${shortName} ${postType}` : (postType || arg.event.title);
    // Add X icon for delete on hover
    return {
      html: `
        <span class="relative group inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium ${bg} ${text} ${border} border gap-1 fc-custom-event" style="width: 100%; display: flex;">
          ${icon}<span style="text-transform: capitalize; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">${label}</span>
          <span class='absolute top-0 right-0 mt-[-6px] mr-[-6px] hidden group-hover:inline-block cursor-pointer z-10 bg-white rounded-full border border-gray-300 p-0.5 shadow-sm transition hover:bg-red-100' title='Delete'>
            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l8 8M6 14L14 6"/></svg>
          </span>
        </span>
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
    console.log('Event clicked:', info.event);
  },
  // Force events to use their individual colors
  eventDidMount: function(info: any) {
    if (info.event.backgroundColor) {
      info.el.style.backgroundColor = info.event.backgroundColor;
      info.el.style.borderColor = info.event.borderColor || info.event.backgroundColor;
    }
  }
})
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
  <!-- Card: Total Clients -->
  <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-blue-100">
      <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87M16 3.13a4 4 0 1 1-8 0"/><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v2"/></svg>
    </div>
    <div>
      <div class="text-gray-500 text-sm">Total Clients</div>
      <div class="text-2xl font-bold text-gray-900">12</div>
    </div>
  </div>
  <!-- Card: Scheduled Posts -->
  <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-green-100">
      <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
      <div class="text-gray-500 text-sm">Scheduled Posts</div>
      <div class="text-2xl font-bold text-gray-900">24</div>
    </div>
  </div>
  <!-- Card: Pending Approvals -->
  <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-yellow-100">
      <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
    </div>
    <div>
      <div class="text-gray-500 text-sm">Pending Approvals</div>
      <div class="text-2xl font-bold text-gray-900">8</div>
    </div>
  </div>
  <!-- Card: Published Today -->
  <div class="bg-white border border-gray-200 rounded-xl px-6 py-5 flex items-center gap-4">
    <div class="w-12 h-12 flex items-center justify-center rounded-lg bg-green-50">
      <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <div>
      <div class="text-gray-500 text-sm">Published Today</div>
      <div class="text-2xl font-bold text-gray-900">5</div>
    </div>
  </div>
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
            <input type="file" accept="image/*,video/*" @change="handleFileUpload" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]" />
            <div v-if="form.media" class="text-xs mt-1 text-green-400">{{ form.media.name }}</div>
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
</style>