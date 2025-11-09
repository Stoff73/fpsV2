<template>
  <div class="card hover:shadow-lg transition-shadow">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h3 class="text-h3 text-gray-900">Plans</h3>
        <p class="text-sm text-gray-600 mt-1">Your financial planning modules</p>
      </div>
      <div class="p-3 bg-primary-100 rounded-lg">
        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
    </div>

    <!-- Plan Buttons -->
    <div class="space-y-3">
      <!-- Protection Plan -->
      <button
        @click="navigateTo('/protection-plan')"
        class="w-full flex items-centre justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colours group"
      >
        <div class="flex items-centre space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-centre justify-centre group-hover:bg-blue-200 transition-colours">
            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Protection Plan</p>
            <p class="text-xs text-gray-600">Life, CI & IP coverage</p>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Estate Plan -->
      <button
        @click="navigateTo('/estate-plan')"
        class="w-full flex items-centre justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colours group"
      >
        <div class="flex items-centre space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-centre justify-centre group-hover:bg-emerald-200 transition-colours">
            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Estate Plan</p>
            <p class="text-xs text-gray-600">IHT & succession planning</p>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Investment & Savings Plan -->
      <button
        @click="navigateTo('/plans/investment-savings')"
        class="w-full flex items-centre justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colours group"
      >
        <div class="flex items-centre space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-lg flex items-centre justify-centre group-hover:bg-indigo-200 transition-colours">
            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Investment & Savings Plan</p>
            <p class="text-xs text-gray-600">Portfolio & cash strategy</p>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Tax Plan (Greyed Out) -->
      <div class="w-full flex items-centre justify-between p-3 bg-gray-100 rounded-lg opacity-50 cursor-not-allowed">
        <div class="flex items-centre space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg flex items-centre justify-centre">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColour" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-600">Tax Plan</p>
            <p class="text-xs text-gray-500">Coming soon</p>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>

      <!-- Financial Plan (Greyed Out) -->
      <div class="w-full flex items-centre justify-between p-3 bg-gray-100 rounded-lg opacity-50 cursor-not-allowed">
        <div class="flex items-centre space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg flex items-centre justify-centre">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColour" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-600">Financial Plan</p>
            <p class="text-xs text-gray-500">Coming soon</p>
          </div>
        </div>
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColour" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuickActions',

  methods: {
    navigateTo(path) {
      this.$router.push(path);
    },
  },
};
</script>
