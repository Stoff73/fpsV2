<template>
  <AppLayout>
    <div class="savings-dashboard py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Breadcrumb -->
      <nav class="mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700">
              Home
            </router-link>
          </li>
          <li>
            <svg
              class="w-4 h-4 text-gray-400"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </li>
          <li>
            <span class="text-gray-900 font-medium">Savings</span>
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Savings & Emergency Fund</h1>
        <p class="text-gray-600">
          Manage your savings accounts, track emergency fund, and monitor progress towards your goals
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-4 border-red-500 p-4 mb-6"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg
              class="h-5 w-5 text-red-400"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="bg-white rounded-lg shadow">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex overflow-x-auto" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Current Situation Tab -->
          <CurrentSituation v-if="activeTab === 'current'" />

          <!-- Emergency Fund Tab -->
          <EmergencyFund v-else-if="activeTab === 'emergency'" />

          <!-- Savings Goals Tab -->
          <SavingsGoals v-else-if="activeTab === 'goals'" />

          <!-- Recommendations Tab -->
          <Recommendations v-else-if="activeTab === 'recommendations'" />

          <!-- What-If Scenarios Tab -->
          <WhatIfScenarios v-else-if="activeTab === 'scenarios'" />

          <!-- Account Details Tab -->
          <AccountDetails v-else-if="activeTab === 'details'" />
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrentSituation from '@/components/Savings/CurrentSituation.vue';
import EmergencyFund from '@/components/Savings/EmergencyFund.vue';
import SavingsGoals from '@/components/Savings/SavingsGoals.vue';
import Recommendations from '@/components/Savings/Recommendations.vue';
import WhatIfScenarios from '@/components/Savings/WhatIfScenarios.vue';
import AccountDetails from '@/components/Savings/AccountDetails.vue';

export default {
  name: 'SavingsDashboard',

  components: {
    AppLayout,
    CurrentSituation,
    EmergencyFund,
    SavingsGoals,
    Recommendations,
    WhatIfScenarios,
    AccountDetails,
  },

  data() {
    return {
      activeTab: 'current',
      tabs: [
        { id: 'current', label: 'Current Situation' },
        { id: 'emergency', label: 'Emergency Fund' },
        { id: 'goals', label: 'Savings Goals' },
        { id: 'recommendations', label: 'Recommendations' },
        { id: 'scenarios', label: 'What-If Scenarios' },
        { id: 'details', label: 'Account Details' },
      ],
    };
  },

  computed: {
    ...mapState('savings', ['loading', 'error']),
  },

  mounted() {
    this.loadSavingsData();
  },

  methods: {
    ...mapActions('savings', ['fetchSavingsData']),

    async loadSavingsData() {
      try {
        await this.fetchSavingsData();
      } catch (error) {
        console.error('Failed to load savings data:', error);
      }
    },
  },
};
</script>

<style scoped>
/* Mobile optimization for tab navigation */
@media (max-width: 640px) {
  .savings-dashboard nav[aria-label="Tabs"] button {
    font-size: 0.875rem;
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

/* Smooth scroll for tab navigation on mobile */
nav[aria-label="Tabs"] {
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

nav[aria-label="Tabs"]::-webkit-scrollbar {
  display: none;
}
</style>
