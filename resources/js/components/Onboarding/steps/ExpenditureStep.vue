<template>
  <OnboardingStep
    title="Household Expenditure"
    description="Help us understand your spending patterns for accurate financial planning"
    :can-go-back="true"
    :can-skip="false"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
  >
    <!-- Shared Expenditure Form -->
    <ExpenditureForm
      :initial-data="initialData"
      :spouse-data="spouseData"
      :spouse-name="spouseName"
      :is-married="isMarried"
      :always-show-tabs="false"
      :show-cancel="false"
      save-text="Continue"
      @save="handleFormSave"
    />
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';
import ExpenditureForm from '../../UserProfile/ExpenditureForm.vue';

export default {
  name: 'ExpenditureStep',

  components: {
    OnboardingStep,
    ExpenditureForm,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();
    const loading = ref(false);
    const error = ref(null);
    const initialData = ref({});
    const spouseData = ref({});

    const user = computed(() => store.getters['auth/currentUser']);

    const isMarried = computed(() => {
      return user.value?.marital_status === 'married' && user.value?.spouse_id;
    });

    const spouseName = computed(() => {
      if (!spouseData.value || !spouseData.value.name) return 'Spouse';
      return spouseData.value.name.split(' ')[0]; // Get first name only
    });

    const fetchSpouseData = async () => {
      if (!user.value?.spouse_id) return;

      try {
        const response = await store.dispatch('auth/fetchUserById', user.value.spouse_id);
        spouseData.value = response || {};
      } catch (err) {
        console.error('Failed to fetch spouse data:', err);
        spouseData.value = {};
      }
    };

    const handleFormSave = async (formData) => {
      error.value = null;

      // Check if this is separate mode data (has userData and spouseData)
      if (formData.userData && formData.spouseData) {
        // Validate that at least one spouse has entered expenditure
        const userHasData = formData.userData.monthly_expenditure > 0 || formData.userData.annual_expenditure > 0;
        const spouseHasData = formData.spouseData.monthly_expenditure > 0 || formData.spouseData.annual_expenditure > 0;

        if (!userHasData && !spouseHasData) {
          error.value = 'Please enter at least some expenditure information';
          return;
        }

        handleNext(formData);
      } else {
        // Joint mode or single user
        if (formData.monthly_expenditure === 0 && formData.annual_expenditure === 0) {
          error.value = 'Please enter at least some expenditure information';
          return;
        }

        handleNext(formData);
      }
    };

    const handleNext = async (formData) => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'expenditure',
          data: formData,
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save expenditure information. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    onMounted(async () => {
      // Load existing data from backend API
      try {
        const stepData = await store.dispatch('onboarding/fetchStepData', 'expenditure');
        if (stepData && Object.keys(stepData).length > 0) {
          initialData.value = stepData;
        }
      } catch (err) {
        // No existing data, start with empty form
      }

      // Fetch spouse data if married
      if (isMarried.value) {
        await fetchSpouseData();
      }
    });

    return {
      initialData,
      spouseData,
      spouseName,
      isMarried,
      loading,
      error,
      handleFormSave,
      handleBack,
    };
  },
};
</script>
