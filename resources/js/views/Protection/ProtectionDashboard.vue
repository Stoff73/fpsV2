<template>
  <AppLayout>
    <div class="protection-dashboard py-6">
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
            <span class="text-gray-900 font-medium">Protection</span>
          </li>
        </ol>
      </nav>

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Protection Planning</h1>
        <p class="text-gray-600">
          Analyse your protection coverage and identify gaps in your insurance portfolio
        </p>
      </div>

      <!-- Profile Completeness Alert -->
      <ProfileCompletenessAlert
        v-if="profileCompleteness && !loadingCompleteness"
        :completenessData="profileCompleteness"
        :dismissible="true"
      />

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

          <!-- Gap Analysis Tab -->
          <GapAnalysis
            v-else-if="activeTab === 'gaps'"
            @add-policy="handleAddPolicy"
          />

          <!-- Recommendations Tab -->
          <Recommendations v-else-if="activeTab === 'recommendations'" />

          <!-- What-If Scenarios Tab -->
          <WhatIfScenarios v-else-if="activeTab === 'scenarios'" />

          <!-- Policy Details Tab -->
          <PolicyDetails
            v-else-if="activeTab === 'details'"
            :show-modal="showAddPolicyModal"
            @modal-closed="showAddPolicyModal = false"
          />
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import CurrentSituation from '@/components/Protection/CurrentSituation.vue';
import GapAnalysis from '@/components/Protection/GapAnalysis.vue';
import Recommendations from '@/components/Protection/Recommendations.vue';
import WhatIfScenarios from '@/components/Protection/WhatIfScenarios.vue';
import PolicyDetails from '@/components/Protection/PolicyDetails.vue';
import ProfileCompletenessAlert from '@/components/Shared/ProfileCompletenessAlert.vue';
import api from '@/services/api';

export default {
  name: 'ProtectionDashboard',

  components: {
    AppLayout,
    CurrentSituation,
    GapAnalysis,
    Recommendations,
    WhatIfScenarios,
    PolicyDetails,
    ProfileCompletenessAlert,
  },

  data() {
    return {
      activeTab: 'current',
      tabs: [
        { id: 'current', label: 'Current Situation' },
        { id: 'gaps', label: 'Gap Analysis' },
        { id: 'recommendations', label: 'Recommendations' },
        { id: 'scenarios', label: 'What-If Scenarios' },
        { id: 'details', label: 'Policy Details' },
      ],
      showAddPolicyModal: false,
      profileCompleteness: null,
      loadingCompleteness: false,
    };
  },

  computed: {
    ...mapState('protection', ['loading', 'error']),
  },

  mounted() {
    this.loadProtectionData();
    this.loadProfileCompleteness();
  },

  methods: {
    ...mapActions('protection', ['fetchProtectionData']),

    async loadProtectionData() {
      try {
        await this.fetchProtectionData();
      } catch (error) {
        console.error('Failed to load protection data:', error);
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

    handleAddPolicy() {
      // Switch to Policy Details tab and open the add modal
      this.activeTab = 'details';
      // Use nextTick to ensure the PolicyDetails component is mounted
      this.$nextTick(() => {
        this.showAddPolicyModal = true;
      });
    },
  },
};
</script>

<style scoped>
/* Mobile optimization for tab navigation */
@media (max-width: 640px) {
  .protection-dashboard nav[aria-label="Tabs"] button {
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
