<template>
  <AppLayout>
    <div class="retirement-dashboard p-6">
    <!-- Header -->
    <div class="mb-8">
      <nav class="text-sm text-gray-600 mb-4">
        <router-link to="/dashboard" class="hover:text-indigo-600">Dashboard</router-link>
        <span class="mx-2">/</span>
        <span class="text-gray-900">Retirement Planning</span>
      </nav>
      <h1 class="text-3xl font-bold text-gray-900">Retirement Planning</h1>
      <p class="text-gray-600 mt-2">Plan your retirement with confidence</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <p class="text-red-800">{{ error }}</p>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Tab Navigation -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="flex overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors duration-200 bg-transparent',
              activeTab === tab.id
                ? 'text-indigo-600 border-b-2 border-indigo-600'
                : 'text-gray-600 hover:text-gray-900 border-b-2 border-transparent'
            ]"
          >
            <span class="flex items-center">
              <svg v-html="tab.icon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"></svg>
              {{ tab.name }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <transition name="fade" mode="out-in">
        <component :is="currentTabComponent" :key="activeTab" />
      </transition>
    </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapState } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import RetirementReadiness from './RetirementReadiness.vue';
import PensionInventory from './PensionInventory.vue';
import ContributionsAllowances from './ContributionsAllowances.vue';
import Projections from './Projections.vue';
import PortfolioAnalysis from './PortfolioAnalysis.vue';
import Recommendations from './Recommendations.vue';
import WhatIfScenarios from './WhatIfScenarios.vue';
import DecumulationPlanning from './DecumulationPlanning.vue';

export default {
  name: 'RetirementDashboard',

  components: {
    AppLayout,
    RetirementReadiness,
    PensionInventory,
    ContributionsAllowances,
    Projections,
    PortfolioAnalysis,
    Recommendations,
    WhatIfScenarios,
    DecumulationPlanning,
  },

  data() {
    return {
      activeTab: 'readiness',
      tabs: [
        {
          id: 'readiness',
          name: 'Readiness',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        },
        {
          id: 'inventory',
          name: 'Pensions',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>',
        },
        {
          id: 'contributions',
          name: 'Contributions',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        },
        {
          id: 'projections',
          name: 'Projections',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>',
        },
        {
          id: 'portfolio',
          name: 'Portfolio Analysis',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
        },
        {
          id: 'recommendations',
          name: 'Recommendations',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>',
        },
        {
          id: 'scenarios',
          name: 'What-If',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        },
        {
          id: 'decumulation',
          name: 'Decumulation',
          icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>',
        },
      ],
    };
  },

  computed: {
    ...mapState('retirement', ['loading', 'error']),

    currentTabComponent() {
      const componentMap = {
        readiness: 'RetirementReadiness',
        inventory: 'PensionInventory',
        contributions: 'ContributionsAllowances',
        projections: 'Projections',
        portfolio: 'PortfolioAnalysis',
        recommendations: 'Recommendations',
        scenarios: 'WhatIfScenarios',
        decumulation: 'DecumulationPlanning',
      };
      return componentMap[this.activeTab];
    },
  },

  async mounted() {
    try {
      await this.$store.dispatch('retirement/fetchRetirementData');
      await this.$store.dispatch('retirement/analyseRetirement');
    } catch (error) {
      console.error('Failed to load retirement data:', error);
    }
  },
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Scrollbar styling for tab navigation */
nav::-webkit-scrollbar {
  height: 4px;
}

nav::-webkit-scrollbar-track {
  background: #f1f1f1;
}

nav::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
