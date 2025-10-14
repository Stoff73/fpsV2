<template>
  <div class="recommendations">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Recommendations</h2>
    <p class="text-gray-600 mb-6">Prioritized recommendations based on your portfolio analysis</p>

    <!-- Summary Stats -->
    <div v-if="recommendations && recommendations.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-red-900 mb-1">High Priority</h4>
        <p class="text-3xl font-bold text-red-600">{{ highPriorityCount }}</p>
        <p class="text-xs text-red-700 mt-1">Requires immediate attention</p>
      </div>
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-yellow-900 mb-1">Medium Priority</h4>
        <p class="text-3xl font-bold text-yellow-600">{{ mediumPriorityCount }}</p>
        <p class="text-xs text-yellow-700 mt-1">Consider addressing soon</p>
      </div>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-900 mb-1">Low Priority</h4>
        <p class="text-3xl font-bold text-blue-600">{{ lowPriorityCount }}</p>
        <p class="text-xs text-blue-700 mt-1">Optional improvements</p>
      </div>
    </div>

    <!-- Recommendations List -->
    <div v-if="recommendations && recommendations.length > 0" class="space-y-4">
      <div
        v-for="(recommendation, index) in recommendations"
        :key="index"
        class="bg-white border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
        :class="getBorderClass(recommendation.priority)"
      >
        <div class="p-6">
          <!-- Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-start flex-1">
              <!-- Priority Icon -->
              <div class="flex-shrink-0 mr-4">
                <div
                  class="w-10 h-10 rounded-full flex items-center justify-center"
                  :class="getIconBgClass(recommendation.priority)"
                >
                  <svg v-if="recommendation.priority === 'high'" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <svg v-else-if="recommendation.priority === 'medium'" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1">
                <div class="flex items-start justify-between">
                  <h3 class="text-lg font-semibold text-gray-900">{{ recommendation.title }}</h3>
                  <span
                    class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getPriorityBadgeClass(recommendation.priority)"
                  >
                    {{ recommendation.priority.toUpperCase() }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 mt-2">{{ recommendation.description }}</p>

                <!-- Category Tag -->
                <div class="mt-3">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                    {{ formatCategory(recommendation.category) }}
                  </span>
                </div>

                <!-- Action Items -->
                <div v-if="recommendation.action_items && recommendation.action_items.length > 0" class="mt-4">
                  <h4 class="text-sm font-medium text-gray-900 mb-2">Suggested Actions:</h4>
                  <ul class="list-disc list-inside space-y-1">
                    <li v-for="(action, actionIndex) in recommendation.action_items" :key="actionIndex" class="text-sm text-gray-700">
                      {{ action }}
                    </li>
                  </ul>
                </div>

                <!-- Potential Impact -->
                <div v-if="recommendation.potential_impact" class="mt-4 bg-gray-50 border border-gray-200 rounded-md p-3">
                  <h4 class="text-xs font-semibold text-gray-700 mb-1">Potential Impact:</h4>
                  <p class="text-sm text-gray-600">{{ recommendation.potential_impact }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white border border-gray-200 rounded-lg p-12 text-center">
      <svg class="mx-auto h-16 w-16 text-green-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Recommendations</h3>
      <p class="text-gray-500">Your portfolio is well-optimized! Run an analysis to generate new recommendations.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Recommendations',

  computed: {
    recommendations() {
      return this.$store.state.investment.recommendations || [];
    },

    highPriorityCount() {
      return this.recommendations.filter(r => r.priority === 'high').length;
    },

    mediumPriorityCount() {
      return this.recommendations.filter(r => r.priority === 'medium').length;
    },

    lowPriorityCount() {
      return this.recommendations.filter(r => r.priority === 'low').length;
    },
  },

  methods: {
    getBorderClass(priority) {
      switch (priority) {
        case 'high':
          return 'border-l-4 border-l-red-500';
        case 'medium':
          return 'border-l-4 border-l-yellow-500';
        case 'low':
          return 'border-l-4 border-l-blue-500';
        default:
          return 'border-l-4 border-l-gray-300';
      }
    },

    getIconBgClass(priority) {
      switch (priority) {
        case 'high':
          return 'bg-red-100';
        case 'medium':
          return 'bg-yellow-100';
        case 'low':
          return 'bg-blue-100';
        default:
          return 'bg-gray-100';
      }
    },

    getPriorityBadgeClass(priority) {
      switch (priority) {
        case 'high':
          return 'bg-red-100 text-red-800';
        case 'medium':
          return 'bg-yellow-100 text-yellow-800';
        case 'low':
          return 'bg-blue-100 text-blue-800';
        default:
          return 'bg-gray-100 text-gray-800';
      }
    },

    formatCategory(category) {
      if (!category) return 'General';
      return category.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
    },
  },
};
</script>
