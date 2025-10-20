<template>
  <div class="recommendations">
    <!-- Coming Soon Message -->
    <div class="flex flex-col items-center justify-center py-16 px-4">
      <div class="bg-white border-2 border-blue-200 rounded-lg p-8 max-w-md w-full text-center shadow-sm">
        <svg class="mx-auto h-16 w-16 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Recommendations</h2>
        <p class="text-gray-600 mb-4">
          AI-powered portfolio optimization and actionable recommendations
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
              <span>Portfolio rebalancing suggestions</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Tax optimization opportunities</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Fee reduction strategies</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Recommendations',
};
</script>
