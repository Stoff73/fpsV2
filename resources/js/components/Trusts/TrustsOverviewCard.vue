<template>
  <div class="card bg-gray-100 opacity-50 cursor-not-allowed">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-h3 text-gray-600">Trusts</h3>
        <p class="text-sm text-gray-500 mt-1">Asset protection & IHT planning</p>
      </div>
      <div class="p-3 bg-gray-200 rounded-lg">
        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>
    </div>

    <!-- Coming Soon Message -->
    <div class="flex flex-col items-center justify-center py-12">
      <svg class="h-16 w-16 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-lg font-semibold text-gray-600 mb-2">Coming Soon</p>
      <p class="text-sm text-gray-500 text-center px-4">Trust planning and management tools will be available soon</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TrustsOverviewCard',
  // Coming Soon - No props or methods needed
};
</script>
