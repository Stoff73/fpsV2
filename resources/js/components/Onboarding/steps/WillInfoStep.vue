<template>
  <OnboardingStep
    title="Will Information"
    description="Tell us about your will and estate planning documents"
    :can-go-back="true"
    :can-skip="true"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
    @skip="handleSkip"
  >
    <div class="space-y-6">
      <div>
        <label class="label">
          Do you currently have a valid will?
        </label>
        <div class="mt-2 space-y-2">
          <label class="inline-flex items-center">
            <input
              v-model="formData.has_will"
              type="radio"
              :value="true"
              class="form-radio text-primary-600"
            >
            <span class="ml-2 text-body text-gray-700">Yes</span>
          </label>
          <label class="inline-flex items-center ml-6">
            <input
              v-model="formData.has_will"
              type="radio"
              :value="false"
              class="form-radio text-primary-600"
            >
            <span class="ml-2 text-body text-gray-700">No</span>
          </label>
        </div>
      </div>

      <div v-if="formData.has_will">
        <label for="will_last_updated" class="label">
          When was your will last updated?
        </label>
        <input
          id="will_last_updated"
          v-model="formData.will_last_updated"
          type="date"
          class="input-field"
        >
        <p class="mt-1 text-body-sm text-gray-500">
          It's recommended to review your will every 5 years or after major life events
        </p>
      </div>

      <div v-if="formData.has_will">
        <label for="executor_name" class="label">
          Who is your executor?
        </label>
        <input
          id="executor_name"
          v-model="formData.executor_name"
          type="text"
          class="input-field"
          placeholder="Executor name"
        >
      </div>

      <div v-if="!formData.has_will" class="bg-amber-50 p-4 rounded-lg border border-amber-200">
        <p class="text-body-sm text-amber-800">
          <strong>Important:</strong> Without a will, your estate will be distributed according to intestacy rules, which may not reflect your wishes.
        </p>
      </div>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'WillInfoStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const formData = ref({
      has_will: null,
      will_last_updated: null,
      executor_name: '',
    });

    const loading = ref(false);
    const error = ref(null);

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'will_info',
          data: formData.value,
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    const handleSkip = () => {
      emit('skip', 'will_info');
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
      } catch (e) {
        return '';
      }
    };

    onMounted(async () => {
      const existingData = await store.dispatch('onboarding/fetchStepData', 'will_info');
      if (existingData) {
        // Format date field if exists
        if (existingData.will_last_updated) {
          existingData.will_last_updated = formatDate(existingData.will_last_updated);
        }
        Object.assign(formData.value, existingData);
      }
    });

    return {
      formData,
      loading,
      error,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
