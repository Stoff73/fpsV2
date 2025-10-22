<template>
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-8">
      <h2 class="text-h2 font-display text-gray-900 mb-2">
        What brings you to FPS?
      </h2>
      <p class="text-body text-gray-600">
        Choose the area you'd like to focus on. We'll guide you through the information we need.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Estate Planning -->
      <button
        @click="selectFocusArea('estate')"
        :disabled="loading"
        class="relative p-6 bg-white rounded-lg border-2 border-gray-200 hover:border-primary-500 hover:shadow-lg transition-all text-left group disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-100 text-primary-600 mb-4 group-hover:bg-primary-600 group-hover:text-white transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h3 class="text-h4 font-display text-gray-900 mb-2">
          Estate Planning
        </h3>
        <p class="text-body-sm text-gray-600">
          Minimize inheritance tax and ensure your wealth passes to your loved ones efficiently.
        </p>
      </button>

      <!-- Protection Planning (Coming Soon) -->
      <div class="relative p-6 bg-gray-50 rounded-lg border-2 border-gray-200 text-left opacity-60 cursor-not-allowed">
        <span class="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          Coming Soon
        </span>
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-200 text-gray-400 mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h3 class="text-h4 font-display text-gray-700 mb-2">
          Protection Planning
        </h3>
        <p class="text-body-sm text-gray-500">
          Ensure your family is financially protected if the unexpected happens.
        </p>
      </div>

      <!-- Retirement Planning (Coming Soon) -->
      <div class="relative p-6 bg-gray-50 rounded-lg border-2 border-gray-200 text-left opacity-60 cursor-not-allowed">
        <span class="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          Coming Soon
        </span>
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-200 text-gray-400 mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-h4 font-display text-gray-700 mb-2">
          Retirement Planning
        </h3>
        <p class="text-body-sm text-gray-500">
          Plan for a comfortable retirement with adequate income and savings.
        </p>
      </div>

      <!-- Investment Planning (Coming Soon) -->
      <div class="relative p-6 bg-gray-50 rounded-lg border-2 border-gray-200 text-left opacity-60 cursor-not-allowed">
        <span class="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          Coming Soon
        </span>
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-200 text-gray-400 mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
        </div>
        <h3 class="text-h4 font-display text-gray-700 mb-2">
          Investment Planning
        </h3>
        <p class="text-body-sm text-gray-500">
          Build wealth through strategic investment portfolio management.
        </p>
      </div>

      <!-- Tax Optimisation (Coming Soon) -->
      <div class="relative p-6 bg-gray-50 rounded-lg border-2 border-gray-200 text-left opacity-60 cursor-not-allowed md:col-span-2 lg:col-span-1">
        <span class="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
          Coming Soon
        </span>
        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-200 text-gray-400 mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
        </div>
        <h3 class="text-h4 font-display text-gray-700 mb-2">
          Tax Optimisation
        </h3>
        <p class="text-body-sm text-gray-500">
          Maximize tax efficiency across income, investments, and allowances.
        </p>
      </div>
    </div>

    <div v-if="error" class="mt-6 p-4 bg-error-50 border border-error-200 rounded-lg">
      <p class="text-body-sm text-error-700">{{ error }}</p>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'FocusAreaSelection',

  emits: ['selected'],

  setup(props, { emit }) {
    const store = useStore();
    const loading = ref(false);
    const error = ref(null);

    const selectFocusArea = async (focusArea) => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/setFocusArea', focusArea);
        emit('selected', focusArea);
      } catch (err) {
        error.value = err.message || 'Failed to set focus area. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      loading,
      error,
      selectFocusArea,
    };
  },
};
</script>
