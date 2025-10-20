<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-h2 font-display text-gray-900">User Profile</h1>
        <p class="mt-2 text-body-base text-gray-600">
          Manage your personal information, family, income, assets, and liabilities
        </p>
      </div>

      <!-- Tab Navigation -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-primary-600 text-primary-700'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-body-sm transition-colors',
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
              <p class="mt-4 text-body-base text-gray-600">Loading profile...</p>
            </div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="rounded-md bg-error-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-body-sm font-medium text-error-800">Error loading profile</h3>
                <div class="mt-2 text-body-sm text-error-700">
                  <p>{{ error }}</p>
                </div>
                <div class="mt-4">
                  <button
                    @click="loadProfile"
                    class="btn-secondary"
                  >
                    Try Again
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab Content Components -->
          <div v-else>
            <PersonalInformation v-show="activeTab === 'personal'" />
            <FamilyMembers v-show="activeTab === 'family'" />
            <IncomeOccupation v-show="activeTab === 'income'" />
            <AssetsOverview v-show="activeTab === 'assets'" />
            <LiabilitiesOverview v-show="activeTab === 'liabilities'" />
            <PersonalAccounts v-show="activeTab === 'accounts'" />
            <Settings v-show="activeTab === 'settings'" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import PersonalInformation from '@/components/UserProfile/PersonalInformation.vue';
import FamilyMembers from '@/components/UserProfile/FamilyMembers.vue';
import IncomeOccupation from '@/components/UserProfile/IncomeOccupation.vue';
import AssetsOverview from '@/components/UserProfile/AssetsOverview.vue';
import LiabilitiesOverview from '@/components/UserProfile/LiabilitiesOverview.vue';
import PersonalAccounts from '@/components/UserProfile/PersonalAccounts.vue';
import Settings from '@/components/UserProfile/Settings.vue';

export default {
  name: 'UserProfile',

  components: {
    AppLayout,
    PersonalInformation,
    FamilyMembers,
    IncomeOccupation,
    AssetsOverview,
    LiabilitiesOverview,
    PersonalAccounts,
    Settings,
  },

  setup() {
    const store = useStore();
    const activeTab = ref('personal');

    const tabs = [
      { id: 'personal', label: 'Personal Info' },
      { id: 'family', label: 'Family' },
      { id: 'income', label: 'Income & Occupation' },
      { id: 'assets', label: 'Assets' },
      { id: 'liabilities', label: 'Liabilities' },
      { id: 'accounts', label: 'Personal Accounts' },
      { id: 'settings', label: 'Settings' },
    ];

    const loading = computed(() => store.getters['userProfile/loading']);
    const error = computed(() => store.getters['userProfile/error']);

    const loadProfile = async () => {
      try {
        await store.dispatch('userProfile/fetchProfile');
      } catch (err) {
        console.error('Failed to load profile:', err);
      }
    };

    onMounted(() => {
      loadProfile();
    });

    return {
      activeTab,
      tabs,
      loading,
      error,
      loadProfile,
    };
  },
};
</script>
