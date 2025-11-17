<template>
  <component :is="isEmbedded ? 'div' : 'AppLayout'">
    <div class="retirement-dashboard p-6">
    <!-- Header (only show when not embedded) -->
    <div v-if="!isEmbedded" class="mb-8">
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
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <transition name="fade" mode="out-in">
        <component :is="currentTabComponent" :key="activeTab" @change-tab="handleTabChange" />
      </transition>
    </div>
    </div>
  </component>
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
    DecumulationPlanning,
  },

  data() {
    return {
      activeTab: 'readiness',
      tabs: [
        {
          id: 'readiness',
          name: 'Overview',
        },
        {
          id: 'inventory',
          name: 'Pensions',
        },
        {
          id: 'contributions',
          name: 'Contributions',
        },
        {
          id: 'projections',
          name: 'Projections',
        },
        {
          id: 'portfolio',
          name: 'Portfolio Analysis',
        },
        {
          id: 'recommendations',
          name: 'Strategies',
        },
        {
          id: 'decumulation',
          name: 'Decumulation',
        },
      ],
    };
  },

  computed: {
    ...mapState('retirement', ['loading', 'error']),

    // Check if this component is embedded in Net Worth module
    isEmbedded() {
      return this.$route.path.startsWith('/net-worth/');
    },

    currentTabComponent() {
      const componentMap = {
        readiness: 'RetirementReadiness',
        inventory: 'PensionInventory',
        contributions: 'ContributionsAllowances',
        projections: 'Projections',
        portfolio: 'PortfolioAnalysis',
        recommendations: 'Recommendations',
        decumulation: 'DecumulationPlanning',
      };
      return componentMap[this.activeTab];
    },
  },

  methods: {
    handleTabChange(tabId) {
      this.activeTab = tabId;
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
