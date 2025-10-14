<template>
  <div class="recommendations-tab">
    <!-- Recommendations Header -->
    <div class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Estate Planning Recommendations</h3>
      <p class="text-sm text-gray-600">
        Prioritized recommendations to optimize your estate and reduce IHT liability
      </p>
    </div>

    <!-- Recommendations List -->
    <div v-if="recommendations.length === 0" class="bg-white rounded-lg border border-gray-200 p-8 text-center">
      <svg
        class="mx-auto h-12 w-12 text-gray-400 mb-4"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      <p class="text-gray-500">No recommendations available yet</p>
      <p class="text-sm text-gray-400 mt-2">Complete your estate data to receive personalized recommendations</p>
    </div>

    <div v-else class="space-y-4">
      <!-- High Priority Recommendations -->
      <div v-if="priorityRecommendations.length > 0" class="mb-6">
        <h4 class="text-md font-semibold text-red-800 mb-3 flex items-center">
          <svg
            class="h-5 w-5 mr-2"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
          High Priority
        </h4>
        <div class="space-y-3">
          <div
            v-for="rec in priorityRecommendations"
            :key="rec.id"
            class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg"
          >
            <div class="flex items-start">
              <div class="flex-1">
                <h5 class="text-sm font-semibold text-red-800 mb-1">{{ rec.title }}</h5>
                <p class="text-sm text-red-700">{{ rec.description }}</p>
                <div v-if="rec.potential_saving" class="mt-2">
                  <span class="text-xs font-medium text-red-600">
                    Potential IHT saving: {{ formatCurrency(rec.potential_saving) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Medium Priority Recommendations -->
      <div v-if="mediumPriorityRecommendations.length > 0" class="mb-6">
        <h4 class="text-md font-semibold text-amber-800 mb-3 flex items-center">
          <svg
            class="h-5 w-5 mr-2"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clip-rule="evenodd"
            />
          </svg>
          Medium Priority
        </h4>
        <div class="space-y-3">
          <div
            v-for="rec in mediumPriorityRecommendations"
            :key="rec.id"
            class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg"
          >
            <div class="flex items-start">
              <div class="flex-1">
                <h5 class="text-sm font-semibold text-amber-800 mb-1">{{ rec.title }}</h5>
                <p class="text-sm text-amber-700">{{ rec.description }}</p>
                <div v-if="rec.potential_saving" class="mt-2">
                  <span class="text-xs font-medium text-amber-600">
                    Potential IHT saving: {{ formatCurrency(rec.potential_saving) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Low Priority Recommendations -->
      <div v-if="lowPriorityRecommendations.length > 0">
        <h4 class="text-md font-semibold text-blue-800 mb-3 flex items-center">
          <svg
            class="h-5 w-5 mr-2"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clip-rule="evenodd"
            />
          </svg>
          Low Priority
        </h4>
        <div class="space-y-3">
          <div
            v-for="rec in lowPriorityRecommendations"
            :key="rec.id"
            class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg"
          >
            <div class="flex items-start">
              <div class="flex-1">
                <h5 class="text-sm font-semibold text-blue-800 mb-1">{{ rec.title }}</h5>
                <p class="text-sm text-blue-700">{{ rec.description }}</p>
                <div v-if="rec.potential_saving" class="mt-2">
                  <span class="text-xs font-medium text-blue-600">
                    Potential IHT saving: {{ formatCurrency(rec.potential_saving) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Disclaimer -->
    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-4">
      <p class="text-xs text-gray-600">
        <strong>Important:</strong> These recommendations are for information purposes only and do not constitute
        regulated financial advice. Please consult a qualified financial adviser before making any decisions regarding
        your estate planning.
      </p>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'Recommendations',

  computed: {
    ...mapState('estate', ['recommendations']),
    ...mapGetters('estate', ['priorityRecommendations']),

    mediumPriorityRecommendations() {
      return this.recommendations.filter(rec => rec.priority === 'medium');
    },

    lowPriorityRecommendations() {
      return this.recommendations.filter(rec => rec.priority === 'low');
    },
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },
  },
};
</script>
