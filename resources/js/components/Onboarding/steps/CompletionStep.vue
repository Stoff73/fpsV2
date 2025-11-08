<template>
  <div class="max-w-3xl mx-auto text-center">
    <div class="mb-8">
      <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-success-100 mb-6">
        <svg class="h-12 w-12 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      </div>

      <h2 class="text-h1 font-display text-gray-900 mb-4">
        Setup Complete!
      </h2>
      <p class="text-body text-gray-600 mb-2">
        Thank you for providing your information. Here's what we captured during onboarding:
      </p>
    </div>

    <!-- Onboarding Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 text-left">
      <h3 class="text-h4 font-display text-gray-900 mb-4">
        Your Setup Summary
      </h3>

      <div v-if="loading" class="text-center py-8">
        <p class="text-gray-500">Loading your information...</p>
      </div>

      <div v-else class="space-y-3">
        <!-- Personal Information -->
        <div class="flex items-start">
          <svg class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Personal Information</p>
            <p class="text-body-sm text-gray-600">Profile setup complete</p>
          </div>
        </div>

        <!-- Protection Policies -->
        <div class="flex items-start">
          <svg
            v-if="summary.policies > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Protection Policies</p>
            <p v-if="summary.policies > 0" class="text-body-sm text-gray-600">
              {{ summary.policies }} {{ summary.policies === 1 ? 'policy' : 'policies' }} added
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add policies later</p>
          </div>
        </div>

        <!-- Properties -->
        <div class="flex items-start">
          <svg
            v-if="summary.properties > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Properties</p>
            <p v-if="summary.properties > 0" class="text-body-sm text-gray-600">
              {{ summary.properties }} {{ summary.properties === 1 ? 'property' : 'properties' }} added (Total value: £{{ summary.propertyValue.toLocaleString() }})
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add properties later</p>
          </div>
        </div>

        <!-- Investment Accounts -->
        <div class="flex items-start">
          <svg
            v-if="summary.investments > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Investment Accounts</p>
            <p v-if="summary.investments > 0" class="text-body-sm text-gray-600">
              {{ summary.investments }} {{ summary.investments === 1 ? 'account' : 'accounts' }} added (Total value: £{{ summary.investmentValue.toLocaleString() }})
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add investments later</p>
          </div>
        </div>

        <!-- Savings Accounts -->
        <div class="flex items-start">
          <svg
            v-if="summary.savings > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Savings Accounts</p>
            <p v-if="summary.savings > 0" class="text-body-sm text-gray-600">
              {{ summary.savings }} {{ summary.savings === 1 ? 'account' : 'accounts' }} added (Total balance: £{{ summary.savingsValue.toLocaleString() }})
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add savings later</p>
          </div>
        </div>

        <!-- Liabilities -->
        <div class="flex items-start">
          <svg
            v-if="summary.liabilities > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Liabilities</p>
            <p v-if="summary.liabilities > 0" class="text-body-sm text-gray-600">
              {{ summary.liabilities }} {{ summary.liabilities === 1 ? 'liability' : 'liabilities' }} added (Total balance: £{{ summary.liabilityValue.toLocaleString() }})
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add liabilities later</p>
          </div>
        </div>

        <!-- Family Members -->
        <div class="flex items-start">
          <svg
            v-if="summary.familyMembers > 0"
            class="h-5 w-5 text-green-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
          </svg>
          <div class="flex-1">
            <p class="text-body font-medium text-gray-900">Family Members</p>
            <p v-if="summary.familyMembers > 0" class="text-body-sm text-gray-600">
              {{ summary.familyMembers}} {{ summary.familyMembers === 1 ? 'family member' : 'family members' }} added
            </p>
            <p v-else class="text-body-sm text-gray-500">Skipped - you can add family members later</p>
          </div>
        </div>
      </div>
    </div>

    <!-- What Happens Next -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8 text-left">
      <h3 class="text-h4 font-display text-gray-900 mb-4">
        What happens next?
      </h3>
      <ul class="space-y-3">
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            Your dashboard will show your current financial planning position
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            We'll calculate your potential Inheritance Tax liability
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            You'll receive personalized recommendations to optimize your finances
          </span>
        </li>
        <li class="flex items-start">
          <svg class="h-5 w-5 text-primary-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-body text-gray-700">
            You can add more detailed information at any time in your profile
          </span>
        </li>
      </ul>
    </div>

    <div v-if="error" class="mb-6 p-4 bg-error-50 border border-error-200 rounded-lg">
      <p class="text-body-sm text-error-700">{{ error }}</p>
    </div>

    <button
      @click="handleComplete"
      :disabled="completionLoading"
      class="btn-primary btn-lg"
    >
      {{ completionLoading ? 'Completing...' : 'Go to Dashboard' }}
    </button>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import propertyService from '@/services/propertyService';
import investmentService from '@/services/investmentService';
import savingsService from '@/services/savingsService';
import protectionService from '@/services/protectionService';
import estateService from '@/services/estateService';
import userProfileService from '@/services/userProfileService';

export default {
  name: 'CompletionStep',

  setup() {
    const store = useStore();
    const router = useRouter();

    const loading = ref(true);
    const completionLoading = ref(false);
    const error = ref(null);

    const summary = ref({
      properties: 0,
      propertyValue: 0,
      investments: 0,
      investmentValue: 0,
      savings: 0,
      savingsValue: 0,
      liabilities: 0,
      liabilityValue: 0,
      policies: 0,
      familyMembers: 0,
    });

    onMounted(async () => {
      await loadSummary();
    });

    async function loadSummary() {
      loading.value = true;

      try {
        // Load all data in parallel
        const [
          propertyResponse,
          investmentResponse,
          savingsResponse,
          protectionResponse,
          estateResponse,
          profileResponse,
        ] = await Promise.all([
          propertyService.getProperties().catch(() => ({ data: [] })),
          investmentService.getInvestmentData().catch(() => ({ data: { accounts: [] } })),
          savingsService.getSavingsData().catch(() => ({ data: { accounts: [] } })),
          protectionService.getProtectionData().catch(() => ({ data: {} })),
          estateService.getEstateData().catch(() => ({ data: { liabilities: [] } })),
          userProfileService.getProfile().catch(() => ({ data: { family_members: [] } })),
        ]);

        // Calculate property summary
        const properties = propertyResponse.data || [];
        summary.value.properties = properties.length;
        summary.value.propertyValue = properties.reduce((sum, p) => sum + (p.current_value || 0), 0);

        // Calculate investment summary
        const investments = investmentResponse.data?.accounts || [];
        summary.value.investments = investments.length;
        summary.value.investmentValue = investments.reduce((sum, i) => sum + (i.current_value || 0), 0);

        // Calculate savings summary
        const savings = savingsResponse.data?.accounts || [];
        summary.value.savings = savings.length;
        summary.value.savingsValue = savings.reduce((sum, s) => sum + (s.current_balance || 0), 0);

        // Calculate liabilities summary
        const liabilities = estateResponse.data?.liabilities || [];
        summary.value.liabilities = liabilities.length;
        summary.value.liabilityValue = liabilities.reduce((sum, l) => sum + (l.current_balance || 0), 0);

        // Calculate protection policy summary
        const protectionData = protectionResponse.data || {};
        summary.value.policies =
          (protectionData.life_policies?.length || 0) +
          (protectionData.critical_illness_policies?.length || 0) +
          (protectionData.income_protection_policies?.length || 0) +
          (protectionData.disability_policies?.length || 0) +
          (protectionData.sickness_illness_policies?.length || 0);

        // Family members summary
        summary.value.familyMembers = profileResponse.data?.family_members?.length || 0;

      } catch (err) {
        console.error('Failed to load summary', err);
      } finally {
        loading.value = false;
      }
    }

    const handleComplete = async () => {
      completionLoading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/completeOnboarding');
        await store.dispatch('auth/fetchUser'); // Refresh user data

        router.push({ name: 'Dashboard' });
      } catch (err) {
        error.value = err.message || 'Failed to complete. Please try again.';
      } finally {
        completionLoading.value = false;
      }
    };

    return {
      loading,
      completionLoading,
      error,
      summary,
      handleComplete,
    };
  },
};
</script>
