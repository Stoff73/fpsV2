<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <!-- Progress Bar -->
    <div v-if="focusArea" class="max-w-5xl mx-auto mb-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="mb-2">
          <span class="text-body-sm font-medium text-gray-700">
            Step {{ currentStepIndex + 1 }} of {{ totalSteps }}
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-primary-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto">
      <!-- Focus Area Selection -->
      <FocusAreaSelection
        v-if="!focusArea"
        @selected="handleFocusAreaSelected"
      />

      <!-- Step Content -->
      <Transition name="fade" mode="out-in">
        <component
          v-if="focusArea && currentStep"
          :is="currentStepComponent"
          :key="currentStep.name"
          @next="handleNext"
          @back="handleBack"
          @skip="handleSkipRequest"
        />
      </Transition>
    </div>

    <!-- Skip Confirmation Modal -->
    <SkipConfirmationModal
      :show="showSkipModal"
      :reason="skipReason"
      @cancel="hideSkipModal"
      @skip="confirmSkip"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import FocusAreaSelection from './FocusAreaSelection.vue';
import SkipConfirmationModal from './SkipConfirmationModal.vue';
import PersonalInfoStep from './steps/PersonalInfoStep.vue';
import IncomeStep from './steps/IncomeStep.vue';
import ExpenditureStep from './steps/ExpenditureStep.vue';
import DomicileInformationStep from './steps/DomicileInformationStep.vue';
import ProtectionPoliciesStep from './steps/ProtectionPoliciesStep.vue';
import AssetsStep from './steps/AssetsStep.vue';
import LiabilitiesStep from './steps/LiabilitiesStep.vue';
import FamilyInfoStep from './steps/FamilyInfoStep.vue';
import WillInfoStep from './steps/WillInfoStep.vue';
import TrustInfoStep from './steps/TrustInfoStep.vue';
import CompletionStep from './steps/CompletionStep.vue';

export default {
  name: 'OnboardingWizard',

  components: {
    FocusAreaSelection,
    SkipConfirmationModal,
    PersonalInfoStep,
    IncomeStep,
    ExpenditureStep,
    DomicileInformationStep,
    ProtectionPoliciesStep,
    AssetsStep,
    LiabilitiesStep,
    FamilyInfoStep,
    WillInfoStep,
    TrustInfoStep,
    CompletionStep,
  },

  setup() {
    const store = useStore();
    const router = useRouter();

    const showSkipModal = ref(false);
    const skipReason = ref('');
    const pendingSkipStep = ref(null);

    const focusArea = computed(() => store.state.onboarding.focusArea);
    const currentStep = computed(() => store.getters['onboarding/currentStep']);
    const currentStepIndex = computed(() => store.state.onboarding.currentStepIndex);
    const totalSteps = computed(() => store.state.onboarding.totalSteps);
    const progressPercentage = computed(() => store.state.onboarding.progressPercentage);

    const currentStepComponent = computed(() => {
      if (!currentStep.value) return null;

      const componentMap = {
        personal_info: 'PersonalInfoStep',
        income: 'IncomeStep',
        expenditure: 'ExpenditureStep',
        domicile_info: 'DomicileInformationStep',
        protection_policies: 'ProtectionPoliciesStep',
        assets: 'AssetsStep',
        liabilities: 'LiabilitiesStep',
        family_info: 'FamilyInfoStep',
        will_info: 'WillInfoStep',
        trust_info: 'TrustInfoStep',
        completion: 'CompletionStep',
      };

      return componentMap[currentStep.value.name] || null;
    });

    const handleFocusAreaSelected = async (area) => {
      // Focus area is set in FocusAreaSelection component
      // Just fetch the steps
      await store.dispatch('onboarding/fetchSteps');
    };

    const handleNext = async () => {
      await store.dispatch('onboarding/goToNextStep');
    };

    const handleBack = async () => {
      await store.dispatch('onboarding/goToPreviousStep');
    };

    const handleSkipRequest = async (stepName) => {
      pendingSkipStep.value = stepName || currentStep.value?.name;
      await store.dispatch('onboarding/showSkipConfirmation', pendingSkipStep.value);
      showSkipModal.value = true;
      skipReason.value = store.state.onboarding.currentSkipReason;
    };

    const hideSkipModal = () => {
      showSkipModal.value = false;
      skipReason.value = '';
      pendingSkipStep.value = null;
      store.dispatch('onboarding/hideSkipConfirmation');
    };

    const confirmSkip = async () => {
      if (pendingSkipStep.value) {
        await store.dispatch('onboarding/skipStep', pendingSkipStep.value);
        await store.dispatch('onboarding/goToNextStep');
      }
      hideSkipModal();
    };

    onMounted(async () => {
      // Fetch onboarding status on mount
      await store.dispatch('onboarding/fetchOnboardingStatus');

      // Always reset to welcome screen when user navigates to onboarding
      // This ensures users see the welcome screen whether:
      // 1. They just registered (new user)
      // 2. They clicked "Complete Setup" (returning user)
      // 3. Onboarding is already completed (revisiting)
      store.commit('onboarding/SET_FOCUS_AREA', null);
      store.commit('onboarding/SET_CURRENT_STEP_INDEX', 0);
      store.commit('onboarding/SET_CURRENT_STEP', null);
    });

    return {
      focusArea,
      currentStep,
      currentStepIndex,
      totalSteps,
      progressPercentage,
      currentStepComponent,
      showSkipModal,
      skipReason,
      handleFocusAreaSelected,
      handleNext,
      handleBack,
      handleSkipRequest,
      hideSkipModal,
      confirmSkip,
    };
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
</style>
