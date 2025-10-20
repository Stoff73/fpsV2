<template>
  <div class="what-if-scenarios">
    <!-- Coming Soon Message -->
    <div class="flex flex-col items-center justify-center py-16 px-4">
      <div class="bg-white border-2 border-blue-200 rounded-lg p-8 max-w-md w-full text-center shadow-sm">
        <svg class="mx-auto h-16 w-16 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">What-If Scenarios</h2>
        <p class="text-gray-600 mb-4">
          Model different market conditions and contribution strategies
        </p>
        <div class="inline-block bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold">
          Coming in Version 0.2
        </div>
        <div class="mt-6 text-left text-sm text-gray-600">
          <p class="font-semibold text-gray-900 mb-2">Upcoming features:</p>
          <ul class="space-y-1">
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Market scenario modeling (bull/bear/crash)</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Contribution strategy comparison</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Asset allocation optimization scenarios</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WhatIfScenarios',
};
</script>
