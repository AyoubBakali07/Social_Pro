<script setup lang="ts">
const props = defineProps<{ stats: Array<{ label: string; value: number; color: string; icon: string }>, posts: Array<any>, clients: Array<{ id: number; company_name: string }> }>();

import { watch, ref, reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
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

const form = useForm({
  platform: 'Facebook',
  content: '',
  media: [] as File[],
  scheduleDate: '',
  client_id: '',
  postType: 'Post',
  status: 'scheduled',
  feedback: ''
});

form.resetOnSuccess = false;
form.onSuccess = () => {
  closeScheduleModal();
  form.reset();
  router.reload({ only: ['posts'] });
};
form.onError = (errors: any) => {
  console.error('Form submission error:', errors);
};

const validateForm = () => {
  const errors: Record<string, string> = {};

  if (!form.content || typeof form.content !== 'string' || !form.content.trim()) {
    errors.content = 'Content is required';
  }

  if (!form.scheduleDate || typeof form.scheduleDate !== 'string') {
    errors.scheduleDate = 'Schedule date is required';
  } else if (new Date(form.scheduleDate) < new Date()) {
    errors.scheduleDate = 'Schedule date must be in the future';
  }

  if (!form.client_id) {
    errors.client_id = 'Please select a client';
  }

  form.clearErrors();

  if (Object.keys(errors).length > 0) {
    form.setError(errors);
    return false;
  }

  return true;
};

const submitSchedule = async () => {
  if (!validateForm()) {
    return;
  }

  try {
    const formData = new FormData();
    Object.keys(form).forEach(key => {
      if (key === 'media') {
        if (Array.isArray(form.media) && form.media.length > 0) {
          form.media.forEach((file, index) => {
            formData.append(`media[${index}]`, file);
          });
        }
      } else if (key === 'scheduleDate' && form.scheduleDate && typeof form.scheduleDate === 'string') {
        formData.append(key, new Date(form.scheduleDate).toISOString());
      } else if (form[key] !== null && form[key] !== undefined && typeof form[key] !== 'object') {
        formData.append(key, String(form[key]));
      }
    });
    if (!formData.get('status')) {
      formData.append('status', 'scheduled');
    }
    if (!formData.get('postType')) {
      formData.append('postType', 'Post');
    }
    await form.post('/agency/posts', {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        closeScheduleModal();
        form.reset();
        router.reload({ only: ['posts'] });
      },
      onError: (errors: any) => {
        console.error('Error scheduling post:', errors);
        if (errors && typeof errors === 'object') {
          const errorMessages: Record<string, string> = {};
          Object.entries(errors).forEach(([key, value]) => {
            errorMessages[key] = Array.isArray(value) ? value[0] : value;
          });
          form.setError(errorMessages);
        }
      },
    });
  } catch (error) {
    console.error('Unexpected error:', error);
    form.setError({ form: 'An unexpected error occurred. Please try again.' });
  }
};
const platforms = ['Facebook', 'Instagram', 'Twitter', 'TikTok', 'LinkedIn'];

// Computed property for dynamic post types based on selected platform
const availablePostTypes = computed(() => {
  const config = platformConfig[form.platform as keyof typeof platformConfig];
  return config?.postTypes || ['Post'];
});

function openScheduleModal() {
  showScheduleModal.value = true;
}

function closeScheduleModal() {
  showScheduleModal.value = false;
  // Reset form
  form.reset();
}

const isDragging = ref(false);

function handleFileUpload(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const files = Array.from(target.files);
    const validFiles = files.filter(file => {
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
      const maxSize = 10 * 1024 * 1024;
      if (!validTypes.includes(file.type)) {
        form.setError('media', 'Invalid file type. Only images and videos are allowed.');
        return false;
      }
      if (file.size > maxSize) {
        form.setError('media', `File ${file.name} is too large. Maximum size is 10MB.`);
        return false;
      }
      return true;
    });
    if (validFiles.length > 0) {
      form.media = [...(Array.isArray(form.media) ? form.media : []), ...validFiles];
    }
  }
  target.value = '';
}

function handleFileDrop(e: DragEvent) {
  isDragging.value = false;
  if (e.dataTransfer && e.dataTransfer.files.length > 0) {
    const input = document.getElementById('media-upload') as HTMLInputElement;
    if (input) {
      input.files = e.dataTransfer.files;
      const event = new Event('change');
      input.dispatchEvent(event);
    }
  }
}

function removeFile(index: number) {
  if (Array.isArray(form.media) && form.media.length > 0) {
    const newMedia = [...form.media];
    newMedia.splice(index, 1);
    form.media = newMedia;
  }
}

// Auto-upload when files are selected
function triggerFileInput() {
  const input = document.getElementById('media-upload');
  if (input) {
    input.click();
  }
}

// Platform and post type configurations
const platformConfig = {
  'Facebook': {
    shortName: 'FB',
    postTypes: ['Post', 'Story', 'Reel'],
    icon: 'facebook',
    color: 'bg-blue-100 text-blue-600',
    iconColor: 'text-blue-500'
  },
  'Instagram': {
    shortName: 'IG',
    postTypes: ['Post', 'Story', 'Reel', 'Carousel'],
    icon: 'instagram',
    color: 'bg-pink-100 text-pink-600',
    iconColor: 'text-pink-500'
  },
  'Twitter': {
    shortName: 'X',
    postTypes: ['Post', 'Thread'],
    icon: 'twitter',
    color: 'bg-sky-100 text-sky-600',
    iconColor: 'text-sky-500'
  },
  'TikTok': {
    shortName: 'TT',
    postTypes: ['Video', 'Live', 'Duet', 'Stitch'],
    icon: 'tiktok',
    color: 'bg-black text-white',
    iconColor: 'text-black'
  },
  'LinkedIn': {
    shortName: 'IN',
    postTypes: ['Post', 'Article', 'Document'],
    icon: 'linkedin',
    color: 'bg-blue-50 text-blue-700',
    iconColor: 'text-blue-600'
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

const posts = ref([...props.posts]); // Local posts state
const errors: Record<string, string[]> = reactive({}); // Use reactive object for errors with string[] values

const events = computed<EventInput[]>(() => {
  return (posts.value ?? [])
    .filter(post => !!post.scheduleDate)
    .map(post => {
      // Defensive: fallback for platform/postType
      const platform = typeof post.platform === 'string' ? post.platform : '';
      const postType = typeof post.postType === 'string' ? post.postType : '';
      let backgroundColor = '#2563eb';
      let borderColor = '#2563eb';
      if (platform === 'Facebook') {
        backgroundColor = '#1877F2'; borderColor = '#1877F2';
      } else if (platform === 'Instagram') {
        backgroundColor = '#E4405F'; borderColor = '#E4405F';
      } else if (platform === 'Twitter') {
        backgroundColor = '#1DA1F2'; borderColor = '#1DA1F2';
      } else if (platform === 'LinkedIn') {
        backgroundColor = '#0077B5'; borderColor = '#0077B5';
      }
      // Defensive: coerce scheduleDate to string
      const scheduleDateStr = post.scheduleDate ? String(post.scheduleDate) : '';
      const allDay = scheduleDateStr.length > 0 && scheduleDateStr.length <= 10;
      // Defensive: fallback for eventDate
      let eventDate = new Date().toISOString();
      if (scheduleDateStr) {
        try {
          eventDate = new Date(scheduleDateStr).toISOString();
        } catch {}
      }
      return {
        id: String(post.id),
        title: `${platform ? platform.charAt(0) : ''}${postType ? ' ' + postType : ''}`,
        start: eventDate,
        allDay,
        backgroundColor,
        borderColor,
        extendedProps: {
          content: post.content ?? '',
          platform,
          postType,
          client: post.client ?? '',
          media: post.media ?? [],
          feedback: post.feedback ?? '',
          status: post.status ?? '',
          created_at: post.created_at ?? '',
        }
      };
    });
});

// Add this watcher to debug calendar events
watch(events, (newEvents) => {
  console.log('Computed events:', newEvents);
  if (calendarRef.value) {
    const calendarApi = calendarRef.value.getApi();
    console.log('Calendar API events:', calendarApi.getEvents().map(e => ({
      id: e.id,
      title: e.title,
      start: e.start,
      allDay: e.allDay
    })));
  }
}, { immediate: true, deep: true });

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
                  :options="{
                    ...calendarOptions,
                    events: events
                  }"
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
            <label class="block text-sm font-medium mb-2">Platform</label>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-2">
              <button
                v-for="platform in platforms"
                :key="platform"
                type="button"
                @click="form.platform = platform"
                class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors"
                :class="{
                  'border-blue-500 bg-blue-50 dark:bg-blue-900/30': form.platform === platform,
                  'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800': form.platform !== platform
                }"
              >
                <div class="w-8 h-8 mb-1 flex items-center justify-center">
                  <img 
                    :src="`/images/platforms/${platform.toLowerCase()}.svg`" 
                    :alt="platform"
                    class="w-6 h-6"
                    v-if="!['TikTok'].includes(platform)"
                  />
                  <span v-else class="text-xl">
                    {{ platform === 'TikTok' ? '🎵' : '' }}
                  </span>
                </div>
                <span class="text-xs font-medium">{{ platformConfig[platform]?.shortName || platform }}</span>
              </button>
            </div>
            <div v-if="form.errors.platform" class="text-xs text-red-500 mt-1">{{ form.errors.platform }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Post Type</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-2">
              <button
                v-for="type in availablePostTypes"
                :key="type"
                type="button"
                @click="form.postType = type"
                class="flex items-center justify-center p-2 rounded-lg border transition-colors text-sm"
                :class="{
                  'border-blue-500 bg-blue-50 dark:bg-blue-900/30': form.postType === type,
                  'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800': form.postType !== type
                }"
              >
                <span class="mr-1">{{ postTypeIcons[type as keyof typeof postTypeIcons] || '📌' }}</span>
                <span>{{ type }}</span>
              </button>
            </div>
            <div v-if="form.errors.postType" class="text-xs text-red-500 mt-1">{{ form.errors.postType }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Content</label>
            <div class="relative">
              <textarea 
                v-model="form.content" 
                class="w-full rounded border p-3 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC] focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                rows="4" 
                :placeholder="`What's on your mind?`"
              ></textarea>
              <div class="absolute bottom-2 right-2 text-xs text-gray-500">
                {{ form.content?.length || 0 }}/500
              </div>
            </div>
            <div v-if="form.errors.content" class="text-xs text-red-500 mt-1">
              {{ form.errors.content }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Media Upload</label>
            <div class="border-2 border-dashed rounded-lg p-4 transition-colors hover:border-blue-500"
                 @dragover.prevent
                 @drop.prevent="handleFileDrop"
                 :class="{'border-blue-500 bg-blue-50 dark:bg-blue-900/10': isDragging}">
              <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                  <button type="button" class="text-blue-600 hover:text-blue-500">
                    Upload files
                  </button>
                  or drag and drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  PNG, JPG, GIF, MP4 up to 10MB
                </p>
                <input 
                  type="file" 
                  id="media-upload"
                  accept="image/*,video/*" 
                  multiple 
                  @change="handleFileUpload" 
                  class="hidden"
                />
              </div>
              <!-- Preview of selected files -->
              <div v-if="form.media && form.media.length > 0" class="mt-4">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Selected files ({{ form.media.length }})
                </div>
                <div class="space-y-2">
                  <div v-for="(file, index) in form.media" :key="index" class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-800 rounded">
                    <div class="flex items-center space-x-2">
                      <svg v-if="file.type.startsWith('image/')" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <svg v-else-if="file.type.startsWith('video/')" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate max-w-xs">
                        {{ file.name }}
                      </span>
                    </div>
                    <button 
                      type="button" 
                      @click="removeFile(index)"
                      class="text-red-500 hover:text-red-700"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="form.errors.media" class="text-xs text-red-500 mt-1">
              {{ Array.isArray(form.errors.media) ? form.errors.media.join(', ') : form.errors.media }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Schedule Date & Time</label>
            <input v-model="form.scheduleDate" type="datetime-local" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]" />
            <div v-if="form.errors.scheduleDate" class="text-xs text-red-500 mt-1">{{ form.errors.scheduleDate }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Client</label>
            <select v-model="form.client_id" class="w-full rounded border p-2 bg-white text-black dark:bg-[#161615] dark:text-[#EDEDEC]">
              <option value="">Select a client</option>
              <option v-for="c in props.clients" :key="c.id" :value="c.id">{{ c.company_name }}</option>
            </select>
            <div v-if="form.errors.client_id" class="text-xs text-red-500 mt-1">{{ form.errors.client_id }}</div>
          </div>
          <div v-if="form.hasErrors" class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  There {{ Object.keys(form.errors).length === 1 ? 'is' : 'are' }} {{ Object.keys(form.errors).length }} error{{ Object.keys(form.errors).length === 1 ? '' : 's' }} with your submission
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc pl-5 space-y-1">
                    <li v-for="(error, field) in form.errors" :key="field">
                      {{ error }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <DialogFooter class="mt-4 flex justify-end gap-3">
            <button 
              type="button"
              @click="closeScheduleModal"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="form.processing"
              class="relative px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span :class="{ 'invisible': form.processing }">Schedule</span>
              <span v-if="form.processing" class="absolute inset-0 flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </span>
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