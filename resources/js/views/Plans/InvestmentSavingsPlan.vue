<template>
  <AppLayout>
    <div class="investment-savings-plan py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6" aria-label="Breadcrumb">
          <ol class="flex items-centre space-x-2 text-sm">
            <li>
              <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700">
                Home
              </router-link>
            </li>
            <li>
              <svg class="w-4 h-4 text-gray-400" fill="currentColour" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </li>
            <li>
              <router-link to="/plans" class="text-gray-500 hover:text-gray-700">
                Plans
              </router-link>
            </li>
            <li>
              <svg class="w-4 h-4 text-gray-400" fill="currentColour" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </li>
            <li>
              <span class="text-gray-900 font-medium">Investment & Savings Plan</span>
            </li>
          </ol>
        </nav>

        <!-- Header -->
        <div class="mb-6 flex items-centre justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Investment & Savings Plan</h1>
            <p class="text-gray-600">
              Comprehensive analysis of your investment portfolio and savings strategy
            </p>
          </div>
          <router-link
            to="/plans"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Back to Plans
          </router-link>
        </div>

        <!-- Comprehensive Investment & Savings Plan -->
        <InvestmentSavingsPlanView />
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import InvestmentSavingsPlanView from '@/components/Plans/InvestmentSavingsPlanView.vue';

export default {
  name: 'InvestmentSavingsPlan',

  components: {
    AppLayout,
    InvestmentSavingsPlanView,
  },
};
</script>

<style scoped>
/* Additional styles if needed */
</style>
