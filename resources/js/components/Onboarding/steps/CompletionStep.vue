<template>
  <div class="max-w-3xl mx-auto text-center">
    <div class="mb-8">
      <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-success-100 mb-6">
        <svg class="h-12 w-12 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h2 class="text-h1 font-display text-gray-900 mb-4">
        Setup Complete!
      </h2>
      <p class="text-body text-gray-600 mb-2">
        Thank you for providing your information. We now have enough to start analyzing your estate planning needs.
      </p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 text-left">
      <h3 class="text-h4 font-display text-gray-900 mb-4">
        What happens next?
      </h3>
      <ul class="space-y-3">
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            Your dashboard will show your current estate planning position
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            We'll calculate your potential Inheritance Tax liability
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            You'll receive personalized recommendations to minimize tax
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            You can add more detailed information at any time in your profile
          </span>
        </li>
      </ul>
    </div>

    <div v-if="error" class="mb-6 p-4 bg-error-50 border border-error-200 rounded-lg">
      <p class="text-body-sm text-error-700">{{ error }}</p>
    </div>

    <button
      @click="handleComplete"
      :disabled="loading"
      class="btn-primary btn-lg"
    >
      {{ loading ? 'Completing...' : 'Go to Dashboard' }}
    </button>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'CompletionStep',

  setup() {
    const store = useStore();
    const router = useRouter();

    const loading = ref(false);
    const error = ref(null);

    const handleComplete = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/completeOnboarding');
        await store.dispatch('auth/fetchUser'); // Refresh user data

        router.push({ name: 'Dashboard' });
      } catch (err) {
        error.value = err.message || 'Failed to complete. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      loading,
      error,
      handleComplete,
    };
  },
};
</script>
