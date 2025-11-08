<template>
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-8">
      <h1 class="text-h1 font-display text-gray-900 mb-4">
        Welcome to TenGo
      </h1>
      <h2 class="text-h3 font-display text-gray-700 mb-4">
        Your Comprehensive Financial Planning System
      </h2>
      <div class="max-w-3xl mx-auto mb-6 text-left bg-white rounded-lg border border-gray-200 p-6">
        <p class="text-body text-gray-700 mb-4">
          TenGo is designed to help UK individuals and families take control of their financial future. Our system provides:
        </p>
        <ul class="space-y-2 text-body-sm text-gray-600">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span><strong>Estate Planning:</strong> Calculate IHT liability, optimize gifting strategies, and plan for second death scenarios</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span><strong>Protection Analysis:</strong> Assess life cover, disability cover, critical illness and income protection</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span><strong>Retirement Planning:</strong> Project pension pots and ensure retirement readiness</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span><strong>Net Worth Tracking:</strong> Monitor all assets and liabilities in one place</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span><strong>Tax Optimisation:</strong> Optimise your tax in line with your current financial situation</span>
          </li>
        </ul>
      </div>
      <div class="text-center mt-8">
        <button
          @click="selectFocusArea('estate')"
          :disabled="loading"
          class="inline-flex items-center px-8 py-3 border border-transparent text-body font-medium rounded-button text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
          <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Loading...' : 'Continue to Onboarding' }}
        </button>
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
