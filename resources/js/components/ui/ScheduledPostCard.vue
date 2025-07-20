<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
  platform: string;
  postType: string;
  scheduledFor: string;
  media: Array<{ url: string; type: string; name?: string }>;
  content: string;
  client: string;
}>();

const mediaCarouselIndex = ref(0);
const hasMultipleMedia = computed(() => props.media && props.media.length > 1);

function prevMedia() {
  if (mediaCarouselIndex.value > 0) mediaCarouselIndex.value--;
}
function nextMedia() {
  if (mediaCarouselIndex.value < props.media.length - 1) mediaCarouselIndex.value++;
}
</script>
<template>
  <div class="w-full max-w-md bg-white rounded-2xl p-2 flex flex-col gap-4">
    <!-- Header -->
    <div class="flex items-center gap-2 mb-1">
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-semibold bg-pink-100 text-pink-600">
        <svg v-if="platform === 'Instagram'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        <svg v-else-if="platform === 'Facebook'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3.28l.72-4H14V7a1 1 0 0 1 1-1h3z"/></svg>
        <svg v-else-if="platform === 'Twitter'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 22.4 1.64a9.09 9.09 0 0 1-2.88 1.1A4.48 4.48 0 0 0 16.5 0c-2.5 0-4.5 2.01-4.5 4.5 0 .35.04.7.11 1.03A12.94 12.94 0 0 1 3 1.13a4.48 4.48 0 0 0-.61 2.27c0 1.56.8 2.94 2.02 3.75A4.48 4.48 0 0 1 2 6.13v.06c0 2.18 1.55 4 3.8 4.42a4.52 4.52 0 0 1-2.04.08c.57 1.78 2.23 3.08 4.2 3.12A9.05 9.05 0 0 1 1 19.54a12.8 12.8 0 0 0 6.95 2.04c8.36 0 12.94-6.93 12.94-12.94 0-.2 0-.39-.01-.58A9.22 9.22 0 0 0 23 3z"/></svg>
        <svg v-else-if="platform === 'LinkedIn'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="2"/><line x1="16" y1="8" x2="16" y2="16"/><line x1="8" y1="8" x2="8" y2="16"/><line x1="12" y1="12" x2="12" y2="16"/></svg>
        {{ platform }} {{ postType }}
      </span>
    </div>
    <div class="text-gray-500 text-sm mb-2">
      Scheduled for {{ new Date(scheduledFor).toLocaleString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) }}
    </div>
    <!-- Media Carousel -->
    <div v-if="media && media.length" class="w-full flex flex-col items-center">
      <div class="relative w-full max-w-xs aspect-square flex items-center justify-center">
        <template v-for="(item, idx) in media">
          <img v-if="item.type.startsWith('image') && idx === mediaCarouselIndex" :src="item.url" :key="item.url + '-img' + idx" class="w-full h-full object-cover rounded-xl absolute left-0 top-0" v-show="idx === mediaCarouselIndex" />
          <video v-else-if="item.type.startsWith('video') && idx === mediaCarouselIndex" :src="item.url" :key="item.url + '-video' + idx" controls class="w-full h-full object-cover rounded-xl absolute left-0 top-0" v-show="idx === mediaCarouselIndex" />
        </template>
        <button v-if="hasMultipleMedia && mediaCarouselIndex > 0" @click="prevMedia" class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full shadow p-1 z-10">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button v-if="hasMultipleMedia && mediaCarouselIndex < media.length - 1" @click="nextMedia" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white rounded-full shadow p-1 z-10">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <div v-if="hasMultipleMedia" class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1">
          <span v-for="(item, idx) in media" :key="item.url + idx" class="w-2 h-2 rounded-full" :class="mediaCarouselIndex === idx ? 'bg-blue-500' : 'bg-gray-300'" />
        </div>
      </div>
    </div>
    <!-- Post Content -->
    <div class="bg-white rounded-xl p-3 flex flex-col gap-1">
      <div class="font-semibold text-sm">Post Content</div>
      <div class="text-gray-700 whitespace-pre-line min-h-[1.5em] text-sm">{{ content || 'No content' }}</div>
    </div>
    <!-- Details -->
    <div class="grid grid-cols-2 gap-3">
      <div class="bg-white rounded-xl p-3 flex flex-col gap-1">
        <div class="font-semibold mb-1 text-sm">Client</div>
        <div class="text-gray-700 min-h-[1.5em] text-sm">{{ client || '—' }}</div>
      </div>
      <div class="bg-white rounded-xl p-3 flex flex-col gap-1">
        <div class="font-semibold mb-1 text-sm">Platform</div>
        <div class="text-gray-700 min-h-[1.5em] text-sm">{{ platform || '—' }}</div>
      </div>
    </div>
  </div>
</template> 