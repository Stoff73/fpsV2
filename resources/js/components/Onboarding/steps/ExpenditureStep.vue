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
    <div class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-body-sm text-blue-800">
          <strong>Why this matters:</strong> Understanding your expenditure helps us calculate your emergency fund needs, discretionary income, and protection requirements.
        </p>
      </div>

      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <p class="text-body-sm text-amber-800">
          <strong>Note:</strong> Household expenditure such as Council Tax, utilities, and maintenance are entered in the Properties tab. Loans, credit cards, and hire purchase are entered in the Liabilities section.
        </p>
      </div>

      <!-- Entry Mode Toggle -->
      <div class="bg-white border border-gray-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-body font-medium text-gray-900">Entry Method</h4>
            <p class="text-body-sm text-gray-600 mt-1">
              Choose how you'd like to enter your expenditure
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <button
              type="button"
              :class="[
                'px-4 py-2 rounded-md text-body-sm font-medium transition-colors',
                useSimpleEntry
                  ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                  : 'bg-primary-600 text-white'
              ]"
              @click="useSimpleEntry = false"
            >
              Detailed Breakdown
            </button>
            <button
              type="button"
              :class="[
                'px-4 py-2 rounded-md text-body-sm font-medium transition-colors',
                useSimpleEntry
                  ? 'bg-primary-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
              @click="useSimpleEntry = true"
            >
              Simple Total
            </button>
          </div>
        </div>
      </div>

      <!-- Simple Entry Mode -->
      <div v-if="useSimpleEntry" class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Total Monthly Expenditure
        </h4>

        <div class="max-w-md">
          <label for="simple_monthly_expenditure" class="label">
            Monthly Expenditure <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
            <input
              id="simple_monthly_expenditure"
              v-model.number="simpleMonthlyExpenditure"
              type="number"
              min="0"
              step="100"
              class="input-field pl-8"
              placeholder="3000"
              required
            >
          </div>
          <p class="mt-1 text-body-sm text-gray-500">
            Enter your average total monthly household expenses
          </p>
        </div>

        <div class="mt-6 bg-gray-50 rounded-lg p-6">
          <h4 class="text-body font-medium text-gray-900 mb-4">
            Summary
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-body-sm text-gray-600">Total Monthly Expenditure</p>
              <p class="text-h3 font-display text-gray-900">
                £{{ simpleMonthlyExpenditure.toLocaleString() }}
              </p>
            </div>
            <div>
              <p class="text-body-sm text-gray-600">Total Annual Expenditure</p>
              <p class="text-h3 font-display text-gray-900">
                £{{ (simpleMonthlyExpenditure * 12).toLocaleString() }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Entry Mode -->
      <div v-if="!useSimpleEntry">
        <!-- Essential Living Expenses -->
        <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Essential Living Expenses (Monthly)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Food & Groceries -->
          <div>
            <label for="food_groceries" class="label">
              Food & Groceries
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="food_groceries"
                v-model.number="formData.food_groceries"
                type="number"
                min="0"
                step="50"
                class="input-field pl-8"
                placeholder="400"
              >
            </div>
          </div>

          <!-- Transport & Fuel -->
          <div>
            <label for="transport_fuel" class="label">
              Transport & Fuel
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="transport_fuel"
                v-model.number="formData.transport_fuel"
                type="number"
                min="0"
                step="50"
                class="input-field pl-8"
                placeholder="200"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Petrol, public transport, parking
            </p>
          </div>

          <!-- Healthcare & Medical -->
          <div>
            <label for="healthcare_medical" class="label">
              Healthcare & Medical
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="healthcare_medical"
                v-model.number="formData.healthcare_medical"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="50"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Prescriptions, dental, optician
            </p>
          </div>

          <!-- Insurance -->
          <div>
            <label for="insurance" class="label">
              Insurance (non-property)
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="insurance"
                v-model.number="formData.insurance"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="150"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Car, life, health insurance premiums
            </p>
          </div>
        </div>
      </div>

      <!-- Communication & Technology -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Communication & Technology (Monthly)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Mobile Phones -->
          <div>
            <label for="mobile_phones" class="label">
              Mobile Phones
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="mobile_phones"
                v-model.number="formData.mobile_phones"
                type="number"
                min="0"
                step="10"
                class="input-field pl-8"
                placeholder="50"
              >
            </div>
          </div>

          <!-- Internet & TV -->
          <div>
            <label for="internet_tv" class="label">
              Internet & TV
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="internet_tv"
                v-model.number="formData.internet_tv"
                type="number"
                min="0"
                step="10"
                class="input-field pl-8"
                placeholder="60"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Broadband, TV licence, streaming services
            </p>
          </div>

          <!-- Subscriptions -->
          <div>
            <label for="subscriptions" class="label">
              Subscriptions & Memberships
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="subscriptions"
                v-model.number="formData.subscriptions"
                type="number"
                min="0"
                step="10"
                class="input-field pl-8"
                placeholder="30"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Netflix, Spotify, gym, magazines
            </p>
          </div>
        </div>
      </div>

      <!-- Personal & Lifestyle -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Personal & Lifestyle (Monthly)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Clothing & Personal Care -->
          <div>
            <label for="clothing_personal_care" class="label">
              Clothing & Personal Care
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="clothing_personal_care"
                v-model.number="formData.clothing_personal_care"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="100"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Clothes, shoes, haircuts, toiletries
            </p>
          </div>

          <!-- Entertainment & Dining Out -->
          <div>
            <label for="entertainment_dining" class="label">
              Entertainment & Dining Out
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="entertainment_dining"
                v-model.number="formData.entertainment_dining"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="200"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Restaurants, cinema, concerts, hobbies
            </p>
          </div>

          <!-- Holidays & Travel -->
          <div>
            <label for="holidays_travel" class="label">
              Holidays & Travel (Monthly Average)
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="holidays_travel"
                v-model.number="formData.holidays_travel"
                type="number"
                min="0"
                step="50"
                class="input-field pl-8"
                placeholder="250"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Annual holiday costs divided by 12
            </p>
          </div>

          <!-- Pets -->
          <div>
            <label for="pets" class="label">
              Pets
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="pets"
                v-model.number="formData.pets"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="50"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Food, vet bills, insurance
            </p>
          </div>
        </div>
      </div>

      <!-- Children & Education -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Children & Education (Monthly)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Childcare -->
          <div>
            <label for="childcare" class="label">
              Childcare
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="childcare"
                v-model.number="formData.childcare"
                type="number"
                min="0"
                step="100"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Nursery, after-school club, childminder
            </p>
          </div>

          <!-- School Fees -->
          <div>
            <label for="school_fees" class="label">
              School Fees (Monthly Average)
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="school_fees"
                v-model.number="formData.school_fees"
                type="number"
                min="0"
                step="100"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Private school fees divided by 12
            </p>
          </div>

          <!-- Children's Activities -->
          <div>
            <label for="children_activities" class="label">
              Children's Activities
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="children_activities"
                v-model.number="formData.children_activities"
                type="number"
                min="0"
                step="25"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Sports clubs, music lessons, uniforms
            </p>
          </div>
        </div>
      </div>

      <!-- Other Expenses -->
      <div class="border-t pt-4">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          Other Expenses (Monthly)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Other Expenditure -->
          <div>
            <label for="other_expenditure" class="label">
              Other Expenditure
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="other_expenditure"
                v-model.number="formData.other_expenditure"
                type="number"
                min="0"
                step="50"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              Any other regular expenses
            </p>
          </div>
        </div>
      </div>

        <!-- Summary -->
        <div class="border-t pt-4">
          <div class="bg-gray-50 rounded-lg p-6">
            <h4 class="text-body font-medium text-gray-900 mb-4">
              Expenditure Summary
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-body-sm text-gray-600">Total Monthly Expenditure</p>
                <p class="text-h3 font-display text-gray-900">
                  £{{ totalMonthlyExpenditure.toLocaleString() }}
                </p>
              </div>
              <div>
                <p class="text-body-sm text-gray-600">Total Annual Expenditure</p>
                <p class="text-h3 font-display text-gray-900">
                  £{{ totalAnnualExpenditure.toLocaleString() }}
                </p>
              </div>
            </div>
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

export default {
  name: 'ExpenditureStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const useSimpleEntry = ref(false);
    const simpleMonthlyExpenditure = ref(0);

    const formData = ref({
      food_groceries: 0,
      transport_fuel: 0,
      healthcare_medical: 0,
      insurance: 0,
      mobile_phones: 0,
      internet_tv: 0,
      subscriptions: 0,
      clothing_personal_care: 0,
      entertainment_dining: 0,
      holidays_travel: 0,
      pets: 0,
      childcare: 0,
      school_fees: 0,
      children_activities: 0,
      other_expenditure: 0,
    });

    const loading = ref(false);
    const error = ref(null);

    const totalMonthlyExpenditure = computed(() => {
      // Sum only the individual category fields, NOT the totals
      const categories = [
        'food_groceries',
        'transport_fuel',
        'healthcare_medical',
        'insurance',
        'mobile_phones',
        'internet_tv',
        'subscriptions',
        'clothing_personal_care',
        'entertainment_dining',
        'holidays_travel',
        'pets',
        'childcare',
        'school_fees',
        'children_activities',
        'other_expenditure'
      ];

      return categories.reduce((sum, key) => sum + (formData.value[key] || 0), 0);
    });

    const totalAnnualExpenditure = computed(() => {
      return totalMonthlyExpenditure.value * 12;
    });

    const validateForm = () => {
      // Check based on entry mode
      if (useSimpleEntry.value) {
        if (!simpleMonthlyExpenditure.value || simpleMonthlyExpenditure.value <= 0) {
          error.value = 'Please enter your monthly expenditure';
          return false;
        }
      } else {
        // Validation is optional for detailed entry, but check if at least some expenditure is entered
        if (totalMonthlyExpenditure.value === 0) {
          error.value = 'Please enter at least some expenditure information';
          return false;
        }
      }

      return true;
    };

    const handleNext = async () => {
      if (!validateForm()) {
        return;
      }

      loading.value = true;
      error.value = null;

      try {
        // Prepare data based on entry mode
        let dataToSave;

        if (useSimpleEntry.value) {
          // Simple entry - just save the total
          dataToSave = {
            monthly_expenditure: simpleMonthlyExpenditure.value,
            annual_expenditure: simpleMonthlyExpenditure.value * 12,
            // Set all detail fields to 0
            food_groceries: 0,
            transport_fuel: 0,
            healthcare_medical: 0,
            insurance: 0,
            mobile_phones: 0,
            internet_tv: 0,
            subscriptions: 0,
            clothing_personal_care: 0,
            entertainment_dining: 0,
            holidays_travel: 0,
            pets: 0,
            childcare: 0,
            school_fees: 0,
            children_activities: 0,
            other_expenditure: 0,
          };
        } else {
          // Detailed entry - save all fields
          dataToSave = {
            ...formData.value,
            monthly_expenditure: totalMonthlyExpenditure.value,
            annual_expenditure: totalAnnualExpenditure.value,
          };
        }

        await store.dispatch('onboarding/saveStepData', {
          stepName: 'expenditure',
          data: dataToSave,
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
      // ONLY load from backend API - single source of truth
      try {
        const stepData = await store.dispatch('onboarding/fetchStepData', 'expenditure');
        if (stepData && Object.keys(stepData).length > 0) {
          // Check if step data has detailed breakdown
          const hasStepDetailedData =
            (stepData.food_groceries || 0) > 0 ||
            (stepData.transport_fuel || 0) > 0 ||
            (stepData.healthcare_medical || 0) > 0 ||
            (stepData.insurance || 0) > 0 ||
            (stepData.mobile_phones || 0) > 0 ||
            (stepData.internet_tv || 0) > 0 ||
            (stepData.subscriptions || 0) > 0 ||
            (stepData.clothing_personal_care || 0) > 0 ||
            (stepData.entertainment_dining || 0) > 0 ||
            (stepData.holidays_travel || 0) > 0 ||
            (stepData.pets || 0) > 0 ||
            (stepData.childcare || 0) > 0 ||
            (stepData.school_fees || 0) > 0 ||
            (stepData.children_activities || 0) > 0 ||
            (stepData.other_expenditure || 0) > 0;

          if (hasStepDetailedData) {
            useSimpleEntry.value = false;
            formData.value = {
              food_groceries: stepData.food_groceries || 0,
              transport_fuel: stepData.transport_fuel || 0,
              healthcare_medical: stepData.healthcare_medical || 0,
              insurance: stepData.insurance || 0,
              mobile_phones: stepData.mobile_phones || 0,
              internet_tv: stepData.internet_tv || 0,
              subscriptions: stepData.subscriptions || 0,
              clothing_personal_care: stepData.clothing_personal_care || 0,
              entertainment_dining: stepData.entertainment_dining || 0,
              holidays_travel: stepData.holidays_travel || 0,
              pets: stepData.pets || 0,
              childcare: stepData.childcare || 0,
              school_fees: stepData.school_fees || 0,
              children_activities: stepData.children_activities || 0,
              other_expenditure: stepData.other_expenditure || 0,
            };
          } else if (stepData.monthly_expenditure && stepData.monthly_expenditure > 0) {
            useSimpleEntry.value = true;
            simpleMonthlyExpenditure.value = stepData.monthly_expenditure;
          }
        }
      } catch (err) {
        // No existing data, start with empty form (correct for new users)
      }
    });

    return {
      useSimpleEntry,
      simpleMonthlyExpenditure,
      formData,
      loading,
      error,
      totalMonthlyExpenditure,
      totalAnnualExpenditure,
      handleNext,
      handleBack,
    };
  },
};
</script>
