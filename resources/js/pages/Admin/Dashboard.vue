<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const breadcrumbs = [
  { title: 'Admin Dashboard', href: '/admin-dashboard' }
]
</script>

<template>
  <Head title="Admin Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col items-center justify-center min-h-[60vh]">
      <h1 class="text-3xl font-bold mb-4">Admin Dashboard</h1>
      <p class="text-lg text-muted-foreground mb-8">This is a placeholder for the Admin dashboard. Build out your admin UI here.</p>
    </div>
  </AppLayout>
</template>
