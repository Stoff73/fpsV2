<template>
  <div class="max-w-3xl mx-auto">
    <div class="mb-6">
      <h2 class="text-h2 font-display text-gray-900 mb-2">
        {{ title }}
      </h2>
      <p class="text-body text-gray-600">
        {{ description }}
      </p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <slot></slot>
    </div>

    <div v-if="error" class="mt-4 p-4 bg-error-50 border border-error-200 rounded-lg">
      <p class="text-body-sm text-error-700">{{ error }}</p>
    </div>

    <!-- Navigation -->
    <div class="mt-6 flex items-centre justify-between">
      <button
        v-if="canGoBack"
        @click="onBack"
        :disabled="loading"
        type="button"
        class="btn-secondary"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
      <div v-else></div>

      <div class="flex items-centre gap-3">
        <button
          v-if="canSkip"
          @click="onSkip"
          :disabled="loading"
          type="button"
          class="text-body-sm text-gray-600 hover:text-gray-900 underline disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Skip this step
        </button>

        <button
          @click="onNext"
          :disabled="loading || disabled"
          type="button"
          class="btn-primary flex items-centre"
        >
          {{ loading ? 'Saving...' : nextButtonText }}
          <svg v-if="!loading" class="w-4 h-4 ml-2" fill="none" stroke="currentColour" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OnboardingStep',

  props: {
    title: {
      type: String,
      required: true,
    },
    description: {
      type: String,
      required: true,
    },
    canGoBack: {
      type: Boolean,
      default: true,
    },
    canSkip: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    error: {
      type: String,
      default: null,
    },
    nextButtonText: {
      type: String,
      default: 'Continue',
    },
  },

  emits: ['next', 'back', 'skip'],

  methods: {
    onNext() {
      this.$emit('next');
    },

    onBack() {
      this.$emit('back');
    },

    onSkip() {
      this.$emit('skip');
    },
  },
};
</script>
