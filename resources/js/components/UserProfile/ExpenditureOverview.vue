<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h4 font-semibold text-gray-900">Household Expenditure</h2>
      <p class="mt-1 text-body-sm text-gray-600">
        Manage your spending patterns for accurate financial planning
      </p>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <p class="text-body-sm text-blue-800">
        <strong>Why this matters:</strong> Understanding your expenditure helps us calculate your emergency fund needs, discretionary income, and protection requirements.
      </p>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
      <p class="text-body-sm text-amber-800">
        <strong>Note:</strong> Household expenditure such as Council Tax, utilities, and maintenance are entered in the Properties tab. Loans, credit cards, and hire purchase are entered in the Liabilities section.
      </p>
    </div>

    <!-- Entry Mode Toggle -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
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

    <form @submit.prevent="handleSubmit">
      <!-- Simple Entry Mode -->
      <div v-if="useSimpleEntry" class="card p-6 mb-6">
        <h3 class="text-h5 font-semibold text-gray-900 mb-4">Total Monthly Expenditure</h3>

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
                {{ formatCurrency(simpleMonthlyExpenditure) }}
              </p>
            </div>
            <div>
              <p class="text-body-sm text-gray-600">Total Annual Expenditure</p>
              <p class="text-h3 font-display text-gray-900">
                {{ formatCurrency(simpleMonthlyExpenditure * 12) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Entry Mode -->
      <div v-if="!useSimpleEntry" class="space-y-6">
        <!-- Essential Living Expenses -->
        <div class="card p-6">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
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
        <div class="card p-6">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
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
                Broadband, TV packages, streaming
              </p>
            </div>

            <!-- Subscriptions -->
            <div>
              <label for="subscriptions" class="label">
                Subscriptions
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
                Netflix, Spotify, gym memberships
              </p>
            </div>
          </div>
        </div>

        <!-- Personal & Lifestyle -->
        <div class="card p-6">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
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
                Clothes, toiletries, haircuts
              </p>
            </div>

            <!-- Entertainment & Dining -->
            <div>
              <label for="entertainment_dining" class="label">
                Entertainment & Dining
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="entertainment_dining"
                  v-model.number="formData.entertainment_dining"
                  type="number"
                  min="0"
                  step="50"
                  class="input-field pl-8"
                  placeholder="200"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                Restaurants, cinema, activities
              </p>
            </div>

            <!-- Holidays & Travel -->
            <div>
              <label for="holidays_travel" class="label">
                Holidays & Travel
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="holidays_travel"
                  v-model.number="formData.holidays_travel"
                  type="number"
                  min="0"
                  step="100"
                  class="input-field pl-8"
                  placeholder="200"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                Monthly average for annual holidays
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

        <!-- Children & Dependents -->
        <div class="card p-6">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
            Children & Dependents (Monthly)
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
                Nursery, childminder, after school
              </p>
            </div>

            <!-- School Fees -->
            <div>
              <label for="school_fees" class="label">
                School Fees
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
                Private education fees
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
                Sports, music lessons, clubs
              </p>
            </div>
          </div>
        </div>

        <!-- Other -->
        <div class="card p-6">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
            Other Expenses (Monthly)
          </h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Gifts & Charity -->
            <div>
              <label for="gifts_charity" class="label">
                Gifts & Charity
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="gifts_charity"
                  v-model.number="formData.gifts_charity"
                  type="number"
                  min="0"
                  step="25"
                  class="input-field pl-8"
                  placeholder="50"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                Birthday gifts, charitable giving
              </p>
            </div>

            <!-- Regular Savings -->
            <div>
              <label for="regular_savings" class="label">
                Regular Savings
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="regular_savings"
                  v-model.number="formData.regular_savings"
                  type="number"
                  min="0"
                  step="50"
                  class="input-field pl-8"
                  placeholder="200"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                Automatic savings contributions
              </p>
            </div>

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
                Any other monthly expenses
              </p>
            </div>
          </div>
        </div>

        <!-- Detailed Summary -->
        <div class="card p-6 bg-gray-50">
          <h4 class="text-h5 font-semibold text-gray-900 mb-4">
            Expenditure Summary
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-body-sm text-gray-600">Total Monthly Expenditure</p>
              <p class="text-h3 font-display text-gray-900">
                {{ formatCurrency(totalMonthlyExpenditure) }}
              </p>
            </div>
            <div>
              <p class="text-body-sm text-gray-600">Total Annual Expenditure</p>
              <p class="text-h3 font-display text-gray-900">
                {{ formatCurrency(totalAnnualExpenditure) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="rounded-md bg-error-50 p-4 mb-6">
        <p class="text-body-sm text-error-800">{{ error }}</p>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="rounded-md bg-success-50 p-4 mb-6">
        <p class="text-body-sm text-success-800">{{ successMessage }}</p>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="resetForm"
          class="btn-secondary"
          :disabled="saving"
        >
          Reset
        </button>
        <button
          type="submit"
          class="btn-primary"
          :disabled="saving"
        >
          <span v-if="saving">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'ExpenditureOverview',

  setup() {
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
      gifts_charity: 0,
      regular_savings: 0,
      other_expenditure: 0,
    });

    const saving = ref(false);
    const error = ref(null);
    const successMessage = ref(null);

    const profile = computed(() => store.getters['userProfile/profile']);
    const user = computed(() => store.getters['auth/currentUser']);

    const totalMonthlyExpenditure = computed(() => {
      if (useSimpleEntry.value) {
        return simpleMonthlyExpenditure.value || 0;
      }

      return (
        (formData.value.food_groceries || 0) +
        (formData.value.transport_fuel || 0) +
        (formData.value.healthcare_medical || 0) +
        (formData.value.insurance || 0) +
        (formData.value.mobile_phones || 0) +
        (formData.value.internet_tv || 0) +
        (formData.value.subscriptions || 0) +
        (formData.value.clothing_personal_care || 0) +
        (formData.value.entertainment_dining || 0) +
        (formData.value.holidays_travel || 0) +
        (formData.value.pets || 0) +
        (formData.value.childcare || 0) +
        (formData.value.school_fees || 0) +
        (formData.value.children_activities || 0) +
        (formData.value.gifts_charity || 0) +
        (formData.value.regular_savings || 0) +
        (formData.value.other_expenditure || 0)
      );
    });

    const totalAnnualExpenditure = computed(() => totalMonthlyExpenditure.value * 12);

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    };

    const loadExistingData = () => {
      if (!user.value) {
        console.log('User not loaded yet');
        return;
      }

      console.log('Loading expenditure data:', {
        monthly: user.value.monthly_expenditure,
        food: user.value.food_groceries,
        type_monthly: typeof user.value.monthly_expenditure,
        type_food: typeof user.value.food_groceries
      });

      // Check if user has detailed breakdown data
      const hasDetailedData =
        (user.value.food_groceries || 0) > 0 ||
        (user.value.transport_fuel || 0) > 0 ||
        (user.value.healthcare_medical || 0) > 0 ||
        (user.value.insurance || 0) > 0 ||
        (user.value.mobile_phones || 0) > 0 ||
        (user.value.internet_tv || 0) > 0 ||
        (user.value.subscriptions || 0) > 0 ||
        (user.value.clothing_personal_care || 0) > 0 ||
        (user.value.entertainment_dining || 0) > 0 ||
        (user.value.holidays_travel || 0) > 0 ||
        (user.value.pets || 0) > 0 ||
        (user.value.childcare || 0) > 0 ||
        (user.value.school_fees || 0) > 0 ||
        (user.value.children_activities || 0) > 0 ||
        (user.value.gifts_charity || 0) > 0 ||
        (user.value.regular_savings || 0) > 0 ||
        (user.value.other_expenditure || 0) > 0;

      console.log('Has detailed data:', hasDetailedData);

      if (hasDetailedData) {
        useSimpleEntry.value = false;
        formData.value = {
          food_groceries: Number(user.value.food_groceries) || 0,
          transport_fuel: Number(user.value.transport_fuel) || 0,
          healthcare_medical: Number(user.value.healthcare_medical) || 0,
          insurance: Number(user.value.insurance) || 0,
          mobile_phones: Number(user.value.mobile_phones) || 0,
          internet_tv: Number(user.value.internet_tv) || 0,
          subscriptions: Number(user.value.subscriptions) || 0,
          clothing_personal_care: Number(user.value.clothing_personal_care) || 0,
          entertainment_dining: Number(user.value.entertainment_dining) || 0,
          holidays_travel: Number(user.value.holidays_travel) || 0,
          pets: Number(user.value.pets) || 0,
          childcare: Number(user.value.childcare) || 0,
          school_fees: Number(user.value.school_fees) || 0,
          children_activities: Number(user.value.children_activities) || 0,
          gifts_charity: Number(user.value.gifts_charity) || 0,
          regular_savings: Number(user.value.regular_savings) || 0,
          other_expenditure: Number(user.value.other_expenditure) || 0,
        };
        console.log('Set detailed formData:', formData.value);
      } else {
        useSimpleEntry.value = true;
        simpleMonthlyExpenditure.value = Number(user.value.monthly_expenditure) || 0;
        console.log('Set simple expenditure:', simpleMonthlyExpenditure.value);
      }
    };

    const handleSubmit = async () => {
      saving.value = true;
      error.value = null;
      successMessage.value = null;

      try {
        let dataToSave = {};

        if (useSimpleEntry.value) {
          // Simple entry: save just the monthly total, clear detailed fields
          dataToSave = {
            monthly_expenditure: simpleMonthlyExpenditure.value,
            annual_expenditure: simpleMonthlyExpenditure.value * 12,
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
            gifts_charity: 0,
            regular_savings: 0,
            other_expenditure: 0,
          };
        } else {
          // Detailed entry: save all fields and calculate total
          dataToSave = {
            ...formData.value,
            monthly_expenditure: totalMonthlyExpenditure.value,
            annual_expenditure: totalAnnualExpenditure.value,
          };
        }

        await store.dispatch('userProfile/updateExpenditure', dataToSave);

        // Refresh user data
        await store.dispatch('auth/fetchUser');
        await store.dispatch('userProfile/fetchProfile');

        successMessage.value = 'Expenditure updated successfully';

        setTimeout(() => {
          successMessage.value = null;
        }, 3000);
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to update expenditure. Please try again.';
      } finally {
        saving.value = false;
      }
    };

    const resetForm = () => {
      loadExistingData();
      error.value = null;
      successMessage.value = null;
    };

    onMounted(() => {
      if (!profile.value) {
        store.dispatch('userProfile/fetchProfile');
      }
      loadExistingData();
    });

    watch(user, () => {
      loadExistingData();
    });

    return {
      useSimpleEntry,
      simpleMonthlyExpenditure,
      formData,
      saving,
      error,
      successMessage,
      totalMonthlyExpenditure,
      totalAnnualExpenditure,
      formatCurrency,
      handleSubmit,
      resetForm,
    };
  },
};
</script>
