<template>
  <div
    class="card bg-gradient-to-br from-purple-50 to-indigo-50 border-2 border-purple-200 hover:border-purple-300 hover:shadow-lg transition-all duration-200 cursor-pointer"
    @click="$router.push('/plans')"
  >
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-h4 text-purple-900 mb-1">Financial Plans</h3>
        <p class="text-body-sm text-purple-700">Comprehensive planning tools</p>
      </div>
      <div class="p-2 bg-purple-600 rounded-lg">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
    </div>

    <div class="space-y-3 mb-4">
      <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-purple-100">
        <div class="flex items-center">
          <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
          <span class="text-body-sm text-gray-700">Investment & Savings</span>
        </div>
        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </div>

      <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-100 opacity-60">
        <div class="flex items-center">
          <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
          <span class="text-body-sm text-gray-500">Retirement Plan</span>
        </div>
        <span class="text-xs text-gray-400 font-medium">Coming Soon</span>
      </div>

      <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-100 opacity-60">
        <div class="flex items-center">
          <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
          <span class="text-body-sm text-gray-500">Estate Plan</span>
        </div>
        <span class="text-xs text-gray-400 font-medium">Coming Soon</span>
      </div>
    </div>

    <div class="pt-3 border-t border-purple-200">
      <button class="text-body-sm font-medium text-purple-700 hover:text-purple-900 flex items-center w-full justify-center">
        <span>View All Plans</span>
        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlansOverviewCard',
};
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg shadow p-6 transition-shadow;
}
</style>
