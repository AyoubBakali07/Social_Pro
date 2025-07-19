<script setup lang="ts">
import { ref, reactive } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import rrulePlugin from '@fullcalendar/rrule';

const events = ref([
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
  <Head title="Calendar" />
  <AppLayout>
    <div class="flex flex-col items-center justify-center min-h-[80vh] p-4">
      <div class="w-full  bg-white border border-gray-200 rounded-xl p-4 shadow">
        <FullCalendar :options="calendarOptions" :events="events" />
      </div>
    </div>
  </AppLayout>
</template>

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