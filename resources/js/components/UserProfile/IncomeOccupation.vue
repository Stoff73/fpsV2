<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h4 font-semibold text-gray-900">Income & Occupation</h2>
      <p class="mt-1 text-body-sm text-gray-600">
        Update your employment and income information
      </p>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="rounded-md bg-success-50 p-4 mb-6">
      <div class="flex">
        <div class="ml-3">
          <p class="text-body-sm font-medium text-success-800">
            {{ successMessage }}
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="rounded-md bg-error-50 p-4 mb-6">
      <div class="flex">
        <div class="ml-3">
          <h3 class="text-body-sm font-medium text-error-800">Error updating information</h3>
          <div class="mt-2 text-body-sm text-error-700">
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Employment Information -->
      <div class="card p-6">
        <h3 class="text-h5 font-semibold text-gray-900 mb-4">Employment Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Occupation -->
          <div>
            <label for="occupation" class="block text-body-sm font-medium text-gray-700 mb-1">
              Occupation
            </label>
            <input
              id="occupation"
              v-model="form.occupation"
              type="text"
              class="form-input"
              :disabled="!isEditing"
              placeholder="e.g., Software Engineer"
            />
          </div>

          <!-- Employer -->
          <div>
            <label for="employer" class="block text-body-sm font-medium text-gray-700 mb-1">
              Employer
            </label>
            <input
              id="employer"
              v-model="form.employer"
              type="text"
              class="form-input"
              :disabled="!isEditing"
              placeholder="e.g., Tech Corp Ltd"
            />
          </div>

          <!-- Industry -->
          <div>
            <label for="industry" class="block text-body-sm font-medium text-gray-700 mb-1">
              Industry
            </label>
            <input
              id="industry"
              v-model="form.industry"
              type="text"
              class="form-input"
              :disabled="!isEditing"
              placeholder="e.g., Technology"
            />
          </div>

          <!-- Employment Status -->
          <div>
            <label for="employment_status" class="block text-body-sm font-medium text-gray-700 mb-1">
              Employment Status
            </label>
            <select
              id="employment_status"
              v-model="form.employment_status"
              class="form-select"
              :disabled="!isEditing"
            >
              <option value="">Select status</option>
              <option value="employed">Employed</option>
              <option value="self_employed">Self-Employed</option>
              <option value="retired">Retired</option>
              <option value="unemployed">Unemployed</option>
              <option value="student">Student</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Income Information -->
      <div class="card p-6">
        <h3 class="text-h5 font-semibold text-gray-900 mb-4">Annual Income</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Annual Employment Income -->
          <div>
            <label for="annual_employment_income" class="block text-body-sm font-medium text-gray-700 mb-1">
              Employment Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                id="annual_employment_income"
                v-model.number="form.annual_employment_income"
                type="number"
                step="0.01"
                min="0"
                class="form-input pl-7"
                :disabled="!isEditing"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Annual Self-Employment Income -->
          <div>
            <label for="annual_self_employment_income" class="block text-body-sm font-medium text-gray-700 mb-1">
              Self-Employment Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                id="annual_self_employment_income"
                v-model.number="form.annual_self_employment_income"
                type="number"
                step="0.01"
                min="0"
                class="form-input pl-7"
                :disabled="!isEditing"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Annual Rental Income (Auto-calculated from Properties) -->
          <div>
            <label for="annual_rental_income" class="block text-body-sm font-medium text-gray-700 mb-1">
              Rental Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                id="annual_rental_income"
                v-model.number="form.annual_rental_income"
                type="number"
                step="0.01"
                min="0"
                class="form-input pl-7 bg-gray-50"
                disabled
                placeholder="0.00"
              />
            </div>
            <p class="mt-1 text-body-xs text-gray-500">Automatically calculated from your properties</p>
          </div>

          <!-- Annual Dividend Income -->
          <div>
            <label for="annual_dividend_income" class="block text-body-sm font-medium text-gray-700 mb-1">
              Dividend Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                id="annual_dividend_income"
                v-model.number="form.annual_dividend_income"
                type="number"
                step="0.01"
                min="0"
                class="form-input pl-7"
                :disabled="!isEditing"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Annual Other Income -->
          <div>
            <label for="annual_other_income" class="block text-body-sm font-medium text-gray-700 mb-1">
              Other Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                id="annual_other_income"
                v-model.number="form.annual_other_income"
                type="number"
                step="0.01"
                min="0"
                class="form-input pl-7"
                :disabled="!isEditing"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Total Annual Income (Calculated) -->
          <div>
            <label class="block text-body-sm font-medium text-gray-700 mb-1">
              Total Annual Income
            </label>
            <div class="relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">£</span>
              </div>
              <input
                :value="totalIncome"
                type="text"
                class="form-input pl-7 bg-gray-50"
                disabled
              />
            </div>
            <p class="mt-1 text-body-xs text-gray-500">Automatically calculated from all income sources</p>
          </div>
        </div>
      </div>

      <!-- Tax Calculations -->
      <div v-if="incomeOccupation?.net_income" class="card p-6 bg-gradient-to-r from-blue-50 to-blue-100">
        <h3 class="text-h5 font-semibold text-gray-900 mb-4">UK Tax & NI Calculations</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Gross Income -->
          <div class="flex justify-between items-center p-3 bg-white rounded-lg">
            <span class="text-body-sm font-medium text-gray-700">Gross Annual Income</span>
            <span class="text-body font-semibold text-gray-900">{{ formatCurrency(incomeOccupation.gross_income) }}</span>
          </div>

          <!-- Income Tax -->
          <div class="flex justify-between items-center p-3 bg-white rounded-lg">
            <span class="text-body-sm font-medium text-gray-700">Income Tax</span>
            <span class="text-body font-semibold text-error-600">-{{ formatCurrency(incomeOccupation.income_tax) }}</span>
          </div>

          <!-- National Insurance -->
          <div class="flex justify-between items-center p-3 bg-white rounded-lg">
            <span class="text-body-sm font-medium text-gray-700">National Insurance</span>
            <span class="text-body font-semibold text-error-600">-{{ formatCurrency(incomeOccupation.national_insurance) }}</span>
          </div>

          <!-- Total Deductions -->
          <div class="flex justify-between items-center p-3 bg-white rounded-lg">
            <span class="text-body-sm font-medium text-gray-700">Total Deductions</span>
            <span class="text-body font-semibold text-error-600">-{{ formatCurrency(incomeOccupation.total_deductions) }}</span>
          </div>
        </div>

        <!-- Net Income (Prominent Display) -->
        <div class="mt-6 p-4 bg-white rounded-lg border-2 border-success-500">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-body-sm font-medium text-gray-700">Net Annual Income (Take-Home)</p>
              <p class="text-body-xs text-gray-500 mt-1">After tax and NI deductions</p>
            </div>
            <div class="text-right">
              <p class="text-h4 font-bold text-success-700">{{ formatCurrency(incomeOccupation.net_income) }}</p>
              <p class="text-body-sm text-gray-600">{{ formatCurrency(incomeOccupation.net_income / 12) }}/month</p>
            </div>
          </div>
        </div>

        <!-- Effective Tax Rate -->
        <div class="mt-4 p-3 bg-white rounded-lg">
          <div class="flex justify-between items-center">
            <span class="text-body-sm font-medium text-gray-700">Effective Tax Rate</span>
            <span class="text-body font-semibold text-gray-900">{{ incomeOccupation.effective_tax_rate }}%</span>
          </div>
        </div>

        <!-- Info Note -->
        <div class="mt-4 p-3 bg-blue-100 rounded-lg">
          <p class="text-body-xs text-blue-800">
            <strong>Note:</strong> Tax calculations use 2025/26 UK tax rates.
            Personal Allowance: £12,570 | Basic Rate: 20% | Higher Rate: 40% | Additional Rate: 45%
          </p>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-4">
        <button
          v-if="!isEditing"
          type="button"
          @click="isEditing = true"
          class="btn-primary"
        >
          Edit Information
        </button>
        <template v-else>
          <button
            type="button"
            @click="handleCancel"
            class="btn-secondary"
            :disabled="submitting"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn-primary"
            :disabled="submitting"
          >
            <span v-if="!submitting">Save Changes</span>
            <span v-else>Saving...</span>
          </button>
        </template>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'IncomeOccupation',

  setup() {
    const store = useStore();
    const isEditing = ref(false);
    const submitting = ref(false);
    const successMessage = ref('');
    const errorMessage = ref('');

    const incomeOccupation = computed(() => store.getters['userProfile/incomeOccupation']);

    const form = ref({
      occupation: '',
      employer: '',
      industry: '',
      employment_status: '',
      annual_employment_income: 0,
      annual_self_employment_income: 0,
      annual_rental_income: 0,
      annual_dividend_income: 0,
      annual_other_income: 0,
    });

    const totalIncome = computed(() => {
      const total =
        (form.value.annual_employment_income || 0) +
        (form.value.annual_self_employment_income || 0) +
        (form.value.annual_rental_income || 0) +
        (form.value.annual_dividend_income || 0) +
        (form.value.annual_other_income || 0);

      return new Intl.NumberFormat('en-GB', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(total);
    });

    // Initialize form from incomeOccupation
    const initializeForm = () => {
      if (incomeOccupation.value) {
        form.value = {
          occupation: incomeOccupation.value.occupation || '',
          employer: incomeOccupation.value.employer || '',
          industry: incomeOccupation.value.industry || '',
          employment_status: incomeOccupation.value.employment_status || '',
          annual_employment_income: Number(incomeOccupation.value.annual_employment_income) || 0,
          annual_self_employment_income: Number(incomeOccupation.value.annual_self_employment_income) || 0,
          annual_rental_income: Number(incomeOccupation.value.annual_rental_income) || 0,
          annual_dividend_income: Number(incomeOccupation.value.annual_dividend_income) || 0,
          annual_other_income: Number(incomeOccupation.value.annual_other_income) || 0,
        };
      }
    };

    // Watch for changes in incomeOccupation and reinitialize form
    watch(incomeOccupation, () => {
      initializeForm();
    }, { immediate: true });

    const handleSubmit = async () => {
      submitting.value = true;
      successMessage.value = '';
      errorMessage.value = '';

      try {
        // Clean up form data: convert empty strings to null for optional fields
        const cleanedData = Object.entries(form.value).reduce((acc, [key, value]) => {
          // Convert empty strings to null
          if (value === '') {
            acc[key] = null;
          } else {
            acc[key] = value;
          }
          return acc;
        }, {});

        await store.dispatch('userProfile/updateIncomeOccupation', cleanedData);
        successMessage.value = 'Income and occupation information updated successfully!';
        isEditing.value = false;

        // Trigger protection analysis refresh if user has protection module data
        // Income changes affect protection needs calculation
        try {
          await store.dispatch('protection/fetchProtectionData');
        } catch (protectionError) {
          // Silently fail - user might not have protection module set up yet
        }

        // Clear success message after 3 seconds
        setTimeout(() => {
          successMessage.value = '';
        }, 3000);
      } catch (error) {
        console.error('Update error:', error);
        // Show validation errors if available
        if (error.errors) {
          const errors = Object.values(error.errors).flat();
          errorMessage.value = errors.join('. ');
        } else {
          errorMessage.value = error.message || 'Failed to update income and occupation';
        }
      } finally {
        submitting.value = false;
      }
    };

    const handleCancel = () => {
      initializeForm();
      isEditing.value = false;
      errorMessage.value = '';
    };

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    };

    return {
      form,
      isEditing,
      submitting,
      successMessage,
      errorMessage,
      totalIncome,
      incomeOccupation,
      handleSubmit,
      handleCancel,
      formatCurrency,
    };
  },
};
</script>
