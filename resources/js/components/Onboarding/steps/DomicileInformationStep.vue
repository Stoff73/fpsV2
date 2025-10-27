<template>
  <OnboardingStep
    title="Domicile Information"
    description="Your domicile status affects your UK tax liability and IHT calculations"
    :can-go-back="true"
    :can-skip="false"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
  >
    <div class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-body-sm text-blue-800">
          <strong>Why this matters:</strong> UK domicile status determines which assets are subject to UK Inheritance Tax. Non-UK domiciled individuals only pay IHT on UK assets, while UK domiciled individuals pay IHT on worldwide assets.
        </p>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <!-- Domicile Status -->
        <div>
          <label for="domicile_status" class="label">
            Domicile Status <span class="text-red-500">*</span>
          </label>
          <select
            id="domicile_status"
            v-model="formData.domicile_status"
            class="input-field"
            required
            @change="handleDomicileChange"
          >
            <option value="">Select domicile status</option>
            <option value="uk_domiciled">UK Domiciled</option>
            <option value="non_uk_domiciled">Non-UK Domiciled</option>
          </select>
          <p class="mt-1 text-body-sm text-gray-500">
            Generally, you're UK domiciled if you were born in the UK and intend to remain here permanently.
          </p>
        </div>

        <!-- Country of Birth -->
        <div>
          <label for="country_of_birth" class="label">
            Country of Birth <span class="text-red-500">*</span>
          </label>
          <CountrySelector
            v-model="formData.country_of_birth"
            :required="true"
            :disabled="false"
            placeholder="Search for your country of birth..."
          />
        </div>

        <!-- UK Arrival Date (shown for non-UK born or non-UK domiciled) -->
        <div v-if="shouldShowUKArrivalDate" class="space-y-4 border-t pt-4">
          <h4 class="text-body font-medium text-gray-900">
            UK Residency Information
          </h4>

          <div>
            <label for="uk_arrival_date" class="label">
              Date Moved to UK <span class="text-red-500">*</span>
            </label>
            <input
              id="uk_arrival_date"
              v-model="formData.uk_arrival_date"
              type="date"
              class="input-field"
              :max="today"
              required
              @change="calculateYearsResident"
            >
            <p class="mt-1 text-body-sm text-gray-500">
              When did you first move to the UK?
            </p>
          </div>

          <div v-if="yearsResident !== null" class="bg-gray-50 rounded-lg p-4">
            <p class="text-body-sm text-gray-700">
              <strong>Years UK Resident:</strong> {{ yearsResident }} years
            </p>
            <p v-if="isDeemedDomiciled" class="mt-2 text-body-sm text-amber-700">
              <strong>Deemed Domicile:</strong> You are considered deemed domiciled in the UK because you have been resident for at least 15 of the last 20 tax years. This means you are subject to UK IHT on your worldwide assets.
            </p>
            <p v-else class="mt-2 text-body-sm text-green-700">
              <strong>Not Deemed Domiciled:</strong> You are not yet deemed domiciled. You only pay UK IHT on UK assets.
            </p>
          </div>
        </div>
      </div>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';
import CountrySelector from '@/components/Shared/CountrySelector.vue';

export default {
  name: 'DomicileInformationStep',

  components: {
    OnboardingStep,
    CountrySelector,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const formData = ref({
      domicile_status: '',
      country_of_birth: '',
      uk_arrival_date: null,
      years_uk_resident: null,
      deemed_domicile_date: null,
    });

    const loading = ref(false);
    const error = ref(null);
    const yearsResident = ref(null);

    const today = computed(() => {
      const date = new Date();
      return date.toISOString().split('T')[0];
    });

    const isDeemedDomiciled = computed(() => {
      return yearsResident.value !== null && yearsResident.value >= 15;
    });

    const shouldShowUKArrivalDate = computed(() => {
      // Show if non-UK domiciled (regardless of birth country)
      if (formData.value.domicile_status === 'non_uk_domiciled') {
        return true;
      }

      // Show if UK domiciled but born outside UK
      if (formData.value.domicile_status === 'uk_domiciled' &&
          formData.value.country_of_birth &&
          formData.value.country_of_birth !== 'United Kingdom') {
        return true;
      }

      return false;
    });

    const calculateYearsResident = () => {
      if (!formData.value.uk_arrival_date) {
        yearsResident.value = null;
        return;
      }

      const arrival = new Date(formData.value.uk_arrival_date);
      const now = new Date();
      const years = Math.floor((now - arrival) / (365.25 * 24 * 60 * 60 * 1000));

      yearsResident.value = Math.max(0, years);
      formData.value.years_uk_resident = yearsResident.value;

      // Calculate deemed domicile date if applicable
      if (yearsResident.value >= 15) {
        const deemedDate = new Date(arrival);
        deemedDate.setFullYear(deemedDate.getFullYear() + 15);
        formData.value.deemed_domicile_date = deemedDate.toISOString().split('T')[0];
      } else {
        formData.value.deemed_domicile_date = null;
      }
    };

    const handleDomicileChange = () => {
      // Only clear UK arrival fields if UK domiciled AND born in UK
      if (formData.value.domicile_status === 'uk_domiciled' &&
          formData.value.country_of_birth === 'United Kingdom') {
        formData.value.uk_arrival_date = null;
        formData.value.years_uk_resident = null;
        formData.value.deemed_domicile_date = null;
        yearsResident.value = null;
      }
    };

    const handleNext = async () => {
      // Validate required fields
      if (!formData.value.domicile_status) {
        error.value = 'Please select your domicile status';
        return;
      }

      if (!formData.value.country_of_birth) {
        error.value = 'Please select your country of birth';
        return;
      }

      // Validate UK arrival date if required
      if (shouldShowUKArrivalDate.value && !formData.value.uk_arrival_date) {
        error.value = 'Please enter the date you moved to the UK';
        return;
      }

      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'domicile_info',
          data: formData.value,
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save domicile information. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    onMounted(async () => {
      // Load existing step data if available
      try {
        const stepData = await store.dispatch('onboarding/fetchStepData', 'domicile_info');
        if (stepData && Object.keys(stepData).length > 0) {
          formData.value = { ...formData.value, ...stepData };

          // Recalculate years resident if uk_arrival_date exists
          if (formData.value.uk_arrival_date) {
            calculateYearsResident();
          }
        }
      } catch (err) {
        // No existing data, start fresh
        console.log('No existing domicile data');
      }
    });

    return {
      formData,
      loading,
      error,
      today,
      yearsResident,
      isDeemedDomiciled,
      shouldShowUKArrivalDate,
      calculateYearsResident,
      handleDomicileChange,
      handleNext,
      handleBack,
    };
  },
};
</script>
