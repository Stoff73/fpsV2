<template>
  <OnboardingStep
    title="Confirm Your Information"
    description="Let's confirm the information from your registration"
    :can-go-back="false"
    :can-skip="false"
    :loading="loading"
    :error="error"
    @next="handleNext"
  >
    <div class="space-y-6">
      <!-- Display marital status from registration -->
      <div class="bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-body-sm text-gray-600">Name</p>
            <p class="text-body font-medium text-gray-900">{{ userName }}</p>
          </div>
          <div>
            <p class="text-body-sm text-gray-600">Date of Birth</p>
            <p class="text-body font-medium text-gray-900">{{ userDob }}</p>
          </div>
          <div>
            <p class="text-body-sm text-gray-600">Marital Status</p>
            <p class="text-body font-medium text-gray-900">{{ maritalStatusLabel }}</p>
          </div>
          <div>
            <p class="text-body-sm text-gray-600">Gender</p>
            <p class="text-body font-medium text-gray-900">{{ userGender }}</p>
          </div>
        </div>
        <p class="mt-3 text-body-sm text-gray-500 italic">
          This information was taken from your registration. You can update it later in your profile if needed.
        </p>
      </div>

      <!-- Confirmation checkbox -->
      <div>
        <label class="inline-flex items-start">
          <input
            v-model="formData.confirmed"
            type="checkbox"
            class="form-checkbox text-primary-600 mt-1"
          >
          <span class="ml-3 text-body text-gray-700">
            I confirm this information is correct and I'm ready to proceed with estate planning setup.
          </span>
        </label>
      </div>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'PersonalInfoStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const formData = ref({
      confirmed: false,
    });

    const loading = ref(false);
    const error = ref(null);

    const currentUser = computed(() => store.getters['auth/currentUser']);

    const userName = computed(() => currentUser.value?.name || 'Not provided');
    const userDob = computed(() => {
      if (!currentUser.value?.date_of_birth) return 'Not provided';
      return new Date(currentUser.value.date_of_birth).toLocaleDateString('en-GB');
    });
    const maritalStatusLabel = computed(() => {
      const status = currentUser.value?.marital_status;
      if (!status) return 'Not provided';
      return status.charAt(0).toUpperCase() + status.slice(1);
    });
    const userGender = computed(() => {
      const gender = currentUser.value?.gender;
      if (!gender) return 'Not provided';
      return gender.charAt(0).toUpperCase() + gender.slice(1);
    });

    const handleNext = async () => {
      if (!formData.value.confirmed) {
        error.value = 'Please confirm your information to proceed';
        return;
      }

      loading.value = true;
      error.value = null;

      try {
        // Just mark step as complete, data already exists in user profile
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'personal_info',
          data: { confirmed: true },
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    onMounted(async () => {
      // Load existing data if available
      const existingData = await store.dispatch('onboarding/fetchStepData', 'personal_info');
      if (existingData?.confirmed) {
        formData.value.confirmed = true;
      }
    });

    return {
      formData,
      loading,
      error,
      userName,
      userDob,
      maritalStatusLabel,
      userGender,
      handleNext,
    };
  },
};
</script>
