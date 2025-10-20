<template>
  <div class="performance">
    <!-- Coming Soon Message -->
    <div class="flex flex-col items-center justify-center py-16 px-4">
      <div class="bg-white border-2 border-blue-200 rounded-lg p-8 max-w-md w-full text-center shadow-sm">
        <svg class="mx-auto h-16 w-16 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Performance Analysis</h2>
        <p class="text-gray-600 mb-4">
          Comprehensive performance tracking with benchmarks and detailed analytics
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
              <span>Portfolio performance tracking over time</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Benchmark comparison (FTSE, S&P 500, etc.)</span>
            </li>
            <li class="flex items-start">
              <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>Risk-adjusted returns (Sharpe ratio, etc.)</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Performance',
};
</script>
