<template>
  <AppLayout>
    <div class="investment-savings-plan py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
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
