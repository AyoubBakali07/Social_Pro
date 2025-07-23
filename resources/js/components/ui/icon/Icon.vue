<script setup>
import { computed } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
    validator: (value) => [
      'facebook',
      'instagram',
      'twitter',
      'linkedin',
      'tiktok',
      'search',
      'plus',
      'check',
      'x',
      'eye',
      'calendar',
      'image',
      'video',
      'file',
      'trash',
      'loader',
      'chevron-left',
      'chevron-right',
      'dots-vertical'
    ].includes(value)
  },
  size: {
    type: [String, Number],
    default: '1em'
  },
  color: {
    type: String,
    default: 'currentColor'
  },
  strokeWidth: {
    type: [String, Number],
    default: 2
  },
  fill: {
    type: String,
    default: 'none'
  },
  viewBox: {
    type: String,
    default: '0 0 24 24'
  }
});

const iconSize = computed(() => {
  if (typeof props.size === 'number') {
    return `${props.size}px`;
  }
  return props.size;
});

const iconStrokeWidth = computed(() => {
  if (typeof props.strokeWidth === 'number') {
    return props.strokeWidth;
  }
  return props.strokeWidth || 2;
});

const iconPaths = {
  // Social Icons
  facebook: 'M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z',
  instagram: 'M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153.509.5.853 1.004 1.153 1.772.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 0 1-1.153 1.772c-.5.508-1.004.853-1.772 1.153-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 0 1-1.772-1.153A4.904 4.904 0 0 1 2.16 16.55c-.248-.637-.415-1.363-.465-2.428C1.648 12.956 1.636 12.617 1.636 9.9c0-2.717.01-3.056.06-4.122.05-1.065.217-1.79.465-2.428a4.88 4.88 0 0 1 1.153-1.772A4.897 4.897 0 0 1 5.45 2.16c.638-.248 1.362-.415 2.428-.465C8.944 1.648 9.283 1.636 12 1.636z',
  twitter: 'M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z',
  linkedin: 'M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z',
  tiktok: 'M21.53 7.15c.15 1.17.24 2.35.24 3.53 0 4.83-3.92 8.75-8.75 8.75a8.74 8.74 0 0 1-8.75-8.75c0-4.83 3.92-8.75 8.75-8.75 1.18 0 2.31.23 3.35.65v5.28c-.45-.14-.92-.22-1.4-.22-2.48 0-4.5 2.02-4.5 4.5s2.02 4.5 4.5 4.5 4.5-2.02 4.5-4.5v-8.5h4.11z',
  
  // UI Icons
  search: 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm10-8a9.97 9.97 0 0 1-2.92 7.07L21 21l-1.93-1.93A9.95 9.95 0 0 1 11 21a10 10 0 1 1 10-10z',
  plus: 'M12 4v16m8-8H4',
  check: 'M5 13l4 4L19 7',
  x: 'M18 6L6 18M6 6l12 12',
  eye: 'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z',
  calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
  image: 'M4 16l4.586-4.586a2 2 0 0 1 2.828 0L16 16m-2-2l1.586-1.586a2 2 0 0 1 2.828 0L20 14m-6-6h.01M6 20h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z',
  video: 'M15 10l4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z',
  file: 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z',
  trash: 'M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3M4 7h16',
  loader: 'M12 2v4m0 12v4M6 12H2m20 0h-4m1.08 8c.2-.65.3-1.33.3-2 0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10c2.76 0 5.27-1.12 7.08-2.92',
  'chevron-left': 'M15 19l-7-7 7-7',
  'chevron-right': 'M9 5l7 7-7 7',
  'dots-vertical': 'M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z'
};

const iconViewBox = computed(() => {
  // Special case for Instagram to handle its larger viewport
  if (props.name === 'instagram') return '0 0 24 24';
  return props.viewBox;
});
</script>

<template>
  <svg
    :width="iconSize"
    :height="iconSize"
    :viewBox="iconViewBox"
    :fill="fill"
    :stroke="color"
    :stroke-width="iconStrokeWidth"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="inline-block align-middle"
  >
    <path v-if="iconPaths[name]" :d="iconPaths[name]" />
    <circle v-else-if="name === 'circle'" cx="12" cy="12" r="10" />
    <rect v-else-if="name === 'square'" x="3" y="3" width="18" height="18" rx="2" ry="2" />
  </svg>
</template>
