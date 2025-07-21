<script setup lang="ts">
import { computed } from 'vue';
import { Facebook, Instagram, Linkedin, Twitter } from 'lucide-vue-next';

const props = defineProps<{
  platform?: string;
  postType?: string;
  scheduledFor: string;
  media: string[];
  content?: string;
  client?: string;
}>();

// Map platform names to Vue icon components
const platformIconComponents: Record<string, any> = {
  Facebook: Facebook,
  Instagram: Instagram,
  Linkedin: Linkedin,
  Twitter: Twitter,
};

const iconComponent = computed(() => {
  if (!props.platform) {
    return null;
  }
  return platformIconComponents[props.platform];
});

const formattedSchedule = computed(() => {
  if (!props.scheduledFor) return '';
  const date = new Date(props.scheduledFor);
  return date.toLocaleString(undefined, {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: true
  });
});

function isImage(url: string) {
  return typeof url === 'string' && /\.(jpe?g|png|gif)$/i.test(url);
}

function isVideo(url: string) {
  return typeof url === 'string' && /\.(mp4|mov|webm)$/i.test(url);
}
</script>

<template>
  <div class="scheduled-post-card w-full max-w-md rounded-xl bg-white p-6 shadow-lg border border-gray-200">
    <div class="flex items-center mb-2">
      <component :is="iconComponent" class="mr-2 text-blue-500" />
      <span class="text-sm font-medium text-gray-800">{{ platform }}</span>
    </div>

    <div class="flex items-center text-sm text-gray-600 mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/><path d="M12 7v5l3 3"/></svg>
      <span>{{ formattedSchedule }}</span>
    </div>

    <!-- Media Preview -->
    <div v-if="media && media.length" class="mt-2">
      <div v-for="(mediaUrl, index) in media.filter(Boolean)" :key="index" class="w-full aspect-video rounded-lg overflow-hidden border border-gray-200">
        <img v-if="isImage(mediaUrl)" :src="mediaUrl" alt="Post media" class="w-full h-full object-cover">
        <video v-else-if="isVideo(mediaUrl)" :src="mediaUrl" controls class="w-full h-full object-cover"></video>
      </div>
    </div>

    <!-- Post Content -->
    <div v-if="content" class="mt-2">
      <h3 class="text-sm font-semibold text-gray-800 mb-1">
        {{ postType }}
      </h3>
      <p class="text-sm text-gray-700">{{ content }}</p>
    </div>

    <div v-if="client" class="mt-2 text-sm text-gray-600">
      <span>Client: {{ client }}</span>
    </div>
  </div>
</template> 