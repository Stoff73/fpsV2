<template>
  <div class="retirement-view">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Retirement Planning</h2>
      <p class="text-gray-600">Manage your pensions and plan for retirement</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <p class="text-red-800">{{ error }}</p>
      <button
        @click="refreshData"
        class="mt-2 text-sm text-red-600 hover:text-red-700 font-medium"
      >
        Try Again
      </button>
    </div>

    <!-- Main Content -->
    <div v-else>
      <!-- Sub-Tab Navigation -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <nav class="flex overflow-x-auto border-b border-gray-200">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors duration-200',
              activeTab === tab.id
                ? 'text-blue-600 border-b-2 border-blue-600 -mb-px'
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <transition name="fade" mode="out-in">
        <component :is="currentTabComponent" :key="activeTab" />
      </transition>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import RetirementReadiness from '../Retirement/RetirementReadiness.vue';
import PensionInventory from '../Retirement/PensionInventory.vue';
import Projections from '../Retirement/Projections.vue';
import PortfolioAnalysis from '../Retirement/PortfolioAnalysis.vue';
import Recommendations from '../Retirement/Recommendations.vue';

export default {
  name: 'RetirementView',

  components: {
    RetirementReadiness,
    PensionInventory,
    Projections,
    PortfolioAnalysis,
    Recommendations,
  },

  data() {
    return {
      activeTab: 'overview',
      tabs: [
        {
          id: 'overview',
          name: 'Overview',
        },
        {
          id: 'pensions',
          name: 'Pensions',
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
      ],
    };
  },

  computed: {
    ...mapState('retirement', ['loading', 'error']),
    currentTabComponent() {
      const componentMap = {
        overview: 'RetirementReadiness',
        pensions: 'PensionInventory',
        projections: 'Projections',
        portfolio: 'PortfolioAnalysis',
        recommendations: 'Recommendations',
      };
      return componentMap[this.activeTab];
    },
  },

  methods: {
    async refreshData() {
      try {
        await this.$store.dispatch('retirement/fetchRetirementData');
        await this.$store.dispatch('retirement/analyzeRetirement');
      } catch (error) {
        console.error('Failed to load retirement data:', error);
      }
    },
  },

  async mounted() {
    // Load retirement data if not already loaded
    try {
      await this.$store.dispatch('retirement/fetchRetirementData');
      await this.$store.dispatch('retirement/analyzeRetirement');
    } catch (error) {
      console.error('Failed to load retirement data:', error);
    }
  },
};
</script>

<style scoped>
.retirement-view {
  /* Component is embedded in NetWorthDashboard, so no need for extra padding */
}

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
