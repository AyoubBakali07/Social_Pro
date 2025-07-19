<script setup lang="ts">
import { ref, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';

const props = defineProps<{
  open: boolean;
  feedbackType: 'comment' | 'reject';
  feedbackText: string;
}>();
const emit = defineEmits(['update:open', 'update:feedbackText', 'submit', 'cancel']);

const title = computed(() => props.feedbackType === 'comment' ? 'Add Comment' : 'Reject Post');

function handleSubmit() {
  emit('submit');
  emit('update:open', false);
}
function handleCancel() {
  emit('cancel');
  emit('update:open', false);
}
</script>
<template>
  <Dialog :open="props.open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogClose />
      </DialogHeader>
      <div class="flex flex-col gap-4">
        <textarea
          :value="props.feedbackText"
          @input="e => emit('update:feedbackText', (e.target as HTMLTextAreaElement).value)"
          rows="4"
          class="w-full border-2 border-teal-200 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-teal-400"
          placeholder="Enter your feedback..."
        />
        <div class="flex gap-2 justify-end">
          <button
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-500 bg-white text-blue-600 text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:ring-offset-2 hover:bg-blue-50"
            @click="handleSubmit"
          >
            Submit Feedback
          </button>
          <button
            class="bg-white border border-gray-300 text-gray-700 px-5 py-2 rounded-lg font-semibold"
            @click="handleCancel"
          >
            Cancel
          </button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template> 