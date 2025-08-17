<template>
  <Head title="Set Password" />

  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-xl rounded-2xl">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Set Your Password</h1>
        <p class="text-gray-600">Create a secure password for your account</p>
      </div>

      
      <!-- Success Message -->
      <div v-if="status" class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg text-sm">
        <div class="flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          {{ status }}
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
              </svg>
            </div>
            <Input
              id="email"
              type="email"
              class="w-full pl-10"
              :model-value="form.email"
              @update:model-value="(val) => form.email = String(val)"
              required
              autocomplete="username"
              :disabled="true"
            />
          </div>
          <InputError class="mt-1.5 text-sm" :message="form.errors.email" />
        </div>

        <!-- Password Field -->
        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <span class="text-xs text-gray-500">At least 8 characters</span>
          </div>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <Input
              id="password"
              type="password"
              class="w-full pl-10"
              :model-value="form.password"
              @update:model-value="(val) => form.password = String(val)"
              required
              autocomplete="new-password"
              :class="{ 'border-red-300': form.errors.password }"
            />
          </div>
          <InputError class="mt-1.5 text-sm" :message="form.errors.password" />
          <div class="mt-2">
            <div class="grid grid-cols-4 gap-2 mb-2">
              <div :class="[
                'h-1 rounded-full',
                passwordStrength > 0 ? 'bg-red-400' : 'bg-gray-200',
                passwordStrength >= 2 ? 'bg-yellow-400' : '',
                passwordStrength >= 4 ? 'bg-green-400' : ''
              ]"></div>
              <div :class="[
                'h-1 rounded-full',
                passwordStrength >= 2 ? 'bg-yellow-400' : 'bg-gray-200',
                passwordStrength >= 4 ? 'bg-green-400' : ''
              ]"></div>
              <div :class="[
                'h-1 rounded-full',
                passwordStrength >= 3 ? 'bg-yellow-400' : 'bg-gray-200',
                passwordStrength >= 4 ? 'bg-green-400' : ''
              ]"></div>
              <div :class="[
                'h-1 rounded-full',
                passwordStrength >= 4 ? 'bg-green-400' : 'bg-gray-200'
              ]"></div>
            </div>
            <p v-if="form.password" class="text-xs text-gray-500">
              Password strength: 
              <span :class="{
                'text-red-500': passwordStrength <= 1,
                'text-yellow-500': passwordStrength === 2 || passwordStrength === 3,
                'text-green-500': passwordStrength >= 4
              }">
                {{ getPasswordStrengthText() }}
              </span>
            </p>
          </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <Input
              id="password_confirmation"
              type="password"
              class="w-full pl-10"
              :model-value="form.password_confirmation"
              @update:model-value="(val) => form.password_confirmation = String(val)"
              required
              autocomplete="new-password"
              :class="{ 'border-red-300': form.errors.password_confirmation }"
            />
          </div>
          <InputError class="mt-1.5 text-sm" :message="form.errors.password_confirmation" />
          <div v-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1.5 text-sm text-red-600">
            Passwords do not match
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <Button 
            type="submit" 
            :disabled="form.processing || !isFormValid" 
            class="w-full justify-center py-2.5 text-base font-medium"
            :class="{
              'opacity-70 cursor-not-allowed': form.processing || !isFormValid,
              'bg-indigo-600 hover:bg-indigo-700': !form.processing && isFormValid
            }"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ form.processing ? 'Setting up your account...' : 'Set Password' }}</span>
          </Button>
          
          <div v-if="form.recentlySuccessful" class="mt-4 text-center">
            <p class="text-sm text-green-600">Password set successfully! Redirecting...</p>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';

interface Props {
  email: string;
  token: string;
  status?: string;
}

const props = defineProps<Props>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

// Password strength calculation
const passwordStrength = computed(() => {
  let strength = 0;
  const password = form.password;
  
  if (!password) return 0;
  
  // Length check
  if (password.length >= 8) strength += 1;
  if (password.length >= 12) strength += 1;
  
  // Complexity checks
  if (/[A-Z]/.test(password)) strength += 1;
  if (/[0-9]/.test(password)) strength += 1;
  if (/[^A-Za-z0-9]/.test(password)) strength += 1;
  
  return Math.min(strength, 4);
});

// Form validation
const isFormValid = computed(() => {
  return (
    form.password &&
    form.password_confirmation &&
    form.password === form.password_confirmation &&
    form.password.length >= 8 &&
    passwordStrength.value >= 2
  );
});

// Password strength text
const getPasswordStrengthText = () => {
  if (!form.password) return '';
  if (passwordStrength.value <= 1) return 'Very Weak';
  if (passwordStrength.value <= 2) return 'Weak';
  if (passwordStrength.value <= 3) return 'Good';
  return 'Strong';
};

// Handle form submission
const submit = () => {
  if (!isFormValid.value) return;
  
  form.post(route('password.setup.store'), {
    onSuccess: () => {
      form.reset('password', 'password_confirmation');
      // The page will redirect from the server after successful submission
    },
    onError: (errors) => {
      // Handle specific errors if needed
      console.error('Password setup failed:', errors);
    },
  });
};

// Auto-focus the password field on mount
const passwordInput = ref<HTMLInputElement | null>(null);

onMounted(() => {
  const input = document.getElementById('password');
  if (input) {
    input.focus();
  }
});
</script>
