<template>
  <AppLayout>
    <div class="estate-dashboard py-6">
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
            <span class="text-gray-900 font-medium">Estate Planning</span>
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Estate Planning</h1>
        <p class="text-gray-600">
          Plan your estate with IHT calculations, gifting strategies, and trust planning
        </p>
      </div>

      <!-- Profile Completeness Alert -->
      <ProfileCompletenessAlert
        v-if="profileCompleteness && !loadingCompleteness"
        :completenessData="profileCompleteness"
        :dismissible="true"
      />

      <!-- Loading State -->
      <div v-if="initialLoading" class="flex justify-center items-center py-12">
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
          <!-- IHT Planning Tab -->
          <IHTPlanning v-if="activeTab === 'iht'" @will-updated="reloadIHTCalculation" @switch-tab="switchTab" />

          <!-- Will Tab -->
          <WillPlanning v-else-if="activeTab === 'will'" @will-updated="reloadIHTCalculation" />

          <!-- Gifting Strategy Tab -->
          <GiftingStrategy v-else-if="activeTab === 'gifting'" />

          <!-- Life Policy Strategy Tab -->
          <LifePolicyStrategy v-else-if="activeTab === 'life-policy'" />

          <!-- Trust Planning Tab -->
          <TrustPlanning v-else-if="activeTab === 'trusts'" />

          <!-- Recommendations Tab -->
          <Recommendations v-else-if="activeTab === 'recommendations'" />

          <!-- What-If Scenarios Tab -->
          <WhatIfScenarios v-else-if="activeTab === 'scenarios'" />
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import IHTPlanning from '@/components/Estate/IHTPlanning.vue';
import GiftingStrategy from '@/components/Estate/GiftingStrategy.vue';
import LifePolicyStrategy from '@/components/Estate/LifePolicyStrategy.vue';
import TrustPlanning from '@/components/Estate/TrustPlanning.vue';
import WillPlanning from '@/components/Estate/WillPlanning.vue';
import Recommendations from '@/components/Estate/Recommendations.vue';
import WhatIfScenarios from '@/components/Estate/WhatIfScenarios.vue';
import ProfileCompletenessAlert from '@/components/Shared/ProfileCompletenessAlert.vue';
import api from '@/services/api';

export default {
  name: 'EstateDashboard',

  components: {
    AppLayout,
    IHTPlanning,
    GiftingStrategy,
    LifePolicyStrategy,
    TrustPlanning,
    WillPlanning,
    Recommendations,
    WhatIfScenarios,
    ProfileCompletenessAlert,
  },

  data() {
    return {
      activeTab: 'iht',
      initialLoading: true,
      tabs: [
        { id: 'iht', label: 'IHT Planning' },
        { id: 'will', label: 'Will' },
        { id: 'gifting', label: 'Gifting Strategy' },
        { id: 'life-policy', label: 'Life Policy Strategy' },
        { id: 'trusts', label: 'Trust Strategy' },
        { id: 'recommendations', label: 'Recommendations' },
        { id: 'scenarios', label: 'What-If Scenarios' },
      ],
      profileCompleteness: null,
      loadingCompleteness: false,
    };
  },

  computed: {
    ...mapState('estate', ['error']),
  },

  mounted() {
    this.loadEstateData();
    this.loadProfileCompleteness();
  },

  methods: {
    ...mapActions('estate', ['fetchEstateData']),

    async loadEstateData() {
      try {
        await this.fetchEstateData();
      } catch (error) {
        console.error('Failed to load estate data:', error);
      } finally {
        this.initialLoading = false;
      }
    },

    async loadProfileCompleteness() {
      this.loadingCompleteness = true;
      try {
        const response = await api.get('/user/profile/completeness');
        this.profileCompleteness = response.data.data;
      } catch (error) {
        console.error('Failed to load profile completeness:', error);
      } finally {
        this.loadingCompleteness = false;
      }
    },

    reloadIHTCalculation() {
      // Force reload IHT calculation when will is updated
      if (this.activeTab === 'iht') {
        // IHTPlanning component will reload automatically
        this.$forceUpdate();
      }
    },

    switchTab(tabId) {
      // Switch to a specific tab (e.g., from IHT Planning to Gifting)
      this.activeTab = tabId;
    },
  },
};
</script>

<style scoped>
/* Mobile optimization for tab navigation */
@media (max-width: 640px) {
  .estate-dashboard nav[aria-label="Tabs"] button {
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
