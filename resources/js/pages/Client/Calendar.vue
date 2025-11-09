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
  media?: string[];
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
  console.log('Component mounted with posts:', posts.value);
  console.log('Number of posts:', posts.value.length);
  
  // Log sample post data if available
  if (posts.value.length > 0) {
    console.log('Sample post:', {
      id: posts.value[0].id,
      content: posts.value[0].content,
      scheduleDate: posts.value[0].scheduleDate,
      status: posts.value[0].status
    });
  }
  
  // Log the computed events
  console.log('Computed events:', events.value);
});

const events = computed<EventInput[]>(() => {
  console.log('Recomputing events...');
  try {
    if (!Array.isArray(posts.value)) {
      console.error('Posts is not an array:', posts.value);
      return [];
    }
    
    return posts.value
      .filter((post): post is Post => !!post && typeof post === 'object')
      .map(post => {
        // Format the date for FullCalendar
        let startDate = post.scheduleDate;
        if (!startDate) {
          console.warn('Post has no scheduleDate, using current date');
          startDate = post.created_at ? new Date(post.created_at) : new Date();
        }
        
        // Ensure startDate is a valid Date object
        const eventDate = startDate instanceof Date ? 
          startDate : 
          (typeof startDate === 'string' ? new Date(startDate) : new Date());
        
        // Create the event object with proper typing
        const event: EventInput = {
          id: String(post.id),
          title: post.content ? post.content.substring(0, 30) + (post.content.length > 30 ? '...' : '') : 'No content',
          start: eventDate.toISOString(),
          allDay: true,
          backgroundColor: getStatusColor(post.status),
          borderColor: getStatusColor(post.status),
          extendedProps: {
            ...post,
            status: post.status || 'draft'
          }
        };
        
        console.log('Created event:', event);
        return event;
      });
  } catch (error: unknown) {
    console.error('Error generating events:', error);
    return [];
  }
});

const getStatusColor = (status: string) => {
  switch (status) {
    case 'draft':
      return '#6b7280'; // Gray-500
    case 'pending':
      return '#3b82f6'; // Blue-500
    case 'approved':
      return '#22c55e'; // Green-500
    case 'rejected':
      return '#ef4444'; // Red-500
    case 'scheduled':
      return '#f97316'; // Orange-500
    default:
      return '#6b7280';
  }
};

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
    meridiem: false, // Disable custom meridiem formatting
    hour12: true
  },
  eventClick: (info: any) => {
    const event = info.event;
    console.log('Event clicked:', event);
    // You can add a modal or sidebar here to show event details
  },
  eventContent: (arg: any) => {
    const event = arg.event;
    const status = event.extendedProps.status || 'draft';
    const title = event.title || 'No title';
    
    // Create a custom HTML structure for the event
    const eventEl = document.createElement('div');
    eventEl.className = `fc-event-main ${status}`;
    eventEl.innerHTML = `
      <div class="flex items-center">
        <span class="fc-event-dot" style="background-color: ${getStatusColor(status)}"></span>
        <span class="fc-event-title">${title}</span>
      </div>
    `;
    
    return { domNodes: [eventEl] };
  }
});

</script>

<template>
  <AppLayout>
    <Head title="Calendar" />
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h2 class="text-2xl font-semibold text-gray-800 mb-6">Content Calendar</h2>
          
          <div v-if="calendarError" class="bg-red-50 text-red-700 p-4 rounded mb-4">
            {{ calendarError }}
          </div>
          
          <div class="w-full bg-white border border-gray-200 rounded-xl p-4 shadow">
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
                    console.log('Event mounted:', info.event);
                  },
                  eventClick: (info: any) => {
                    const event = info.event;
                    console.log('Event clicked:', event);
                    // You can add a modal or sidebar here to show event details
                  },
                  datesSet: (dateInfo: any) => {
                    console.log('View changed:', dateInfo.view.type, dateInfo.view.title);
                  },
                  loading: (isLoading: boolean) => {
                    console.log('Calendar loading:', isLoading);
                  }
                }"
              />
            </div>
          </div>
          
          <!-- Debug panel (visible in development) -->
          <div v-if="isDevelopment" class="mt-8 p-4 bg-gray-100 rounded-lg w-full max-w-4xl">
            <h3 class="font-bold mb-2">Debug Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <h4 class="font-semibold mb-1">Posts Data ({{ posts.length }})</h4>
                <pre class="bg-white p-3 rounded text-xs overflow-auto max-h-60">{{ JSON.stringify(posts, null, 2) }}</pre>
              </div>
              <div>
                <h4 class="font-semibold mb-1">Events ({{ events.length }})</h4>
                <pre class="bg-white p-3 rounded text-xs overflow-auto max-h-60">{{ JSON.stringify(events, null, 2) }}</pre>
              </div>
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