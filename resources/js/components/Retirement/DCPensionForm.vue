<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-centre justify-centre z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-centre justify-between">
        <h3 class="text-xl font-semibold text-gray-900">
          {{ isEdit ? 'Edit' : 'Add' }} DC Pension
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colours">
          <svg class="w-6 h-6" fill="none" stroke="currentColour" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Scheme Type -->
          <div>
            <label for="scheme_type" class="block text-sm font-medium text-gray-700 mb-2">
              Pension Type <span class="text-red-500">*</span>
            </label>
            <select
              id="scheme_type"
              v-model="formData.scheme_type"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              @change="handleSchemeTypeChange"
            >
              <option value="">Select pension type...</option>
              <option value="workplace">Workplace Pension</option>
              <option value="sipp">SIPP (Self-Invested Personal Pension)</option>
              <option value="personal">Personal Pension</option>
            </select>
            <p class="text-xs text-gray-500 mt-1">
              Workplace: employer scheme with % contributions. SIPP/Personal: fixed £ contributions
            </p>
          </div>

          <!-- Scheme Name -->
          <div>
            <label for="scheme_name" class="block text-sm font-medium text-gray-700 mb-2">
              Scheme Name <span class="text-red-500">*</span>
            </label>
            <input
              id="scheme_name"
              v-model="formData.scheme_name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., Aviva Master Trust"
            />
          </div>

          <!-- Provider and Policy Number -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">
                Provider
              </label>
              <input
                id="provider"
                v-model="formData.provider"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., Aviva"
              />
            </div>
            <div>
              <label for="policy_number" class="block text-sm font-medium text-gray-700 mb-2">
                Policy Number
              </label>
              <input
                id="policy_number"
                v-model="formData.policy_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., DC123456"
              />
            </div>
          </div>

          <!-- Current Fund Value -->
          <div>
            <label for="current_fund_value" class="block text-sm font-medium text-gray-700 mb-2">
              Current Fund Value (£) <span class="text-red-500">*</span>
            </label>
            <input
              id="current_fund_value"
              v-model.number="formData.current_fund_value"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 50000.00"
            />
          </div>

          <!-- Workplace Pension: Annual Salary -->
          <div v-if="isWorkplacePension">
            <label for="annual_salary" class="block text-sm font-medium text-gray-700 mb-2">
              Annual Salary (£) <span class="text-red-500">*</span>
            </label>
            <input
              id="annual_salary"
              v-model.number="formData.annual_salary"
              type="number"
              step="0.01"
              min="0"
              :required="isWorkplacePension"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 50000.00"
            />
            <p class="text-xs text-gray-500 mt-1">Required to calculate percentage contributions</p>
          </div>

          <!-- Workplace Pension: Percentage Contributions -->
          <div v-if="isWorkplacePension" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="employee_contribution_percent" class="block text-sm font-medium text-gray-700 mb-2">
                Employee Contribution (%)
              </label>
              <input
                id="employee_contribution_percent"
                v-model.number="formData.employee_contribution_percent"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                :class="{ 'border-red-500': validationErrors.employee_contribution_percent }"
                placeholder="e.g., 5.00"
                @blur="validateEmployeeContribution"
              />
              <p v-if="validationErrors.employee_contribution_percent" class="text-xs text-red-500 mt-1">
                {{ validationErrors.employee_contribution_percent }}
              </p>
              <p v-else-if="calculatedEmployeeContribution" class="text-xs text-gray-500 mt-1">
                = £{{ calculatedEmployeeContribution.toLocaleString() }}/month
              </p>
              <p v-else class="text-xs text-gray-500 mt-1">Enter as percentage of salary (0-100)</p>
            </div>
            <div>
              <label for="employer_contribution_percent" class="block text-sm font-medium text-gray-700 mb-2">
                Employer Contribution (%)
              </label>
              <input
                id="employer_contribution_percent"
                v-model.number="formData.employer_contribution_percent"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                :class="{ 'border-red-500': validationErrors.employer_contribution_percent }"
                placeholder="e.g., 3.00"
                @blur="validateEmployerContribution"
              />
              <p v-if="validationErrors.employer_contribution_percent" class="text-xs text-red-500 mt-1">
                {{ validationErrors.employer_contribution_percent }}
              </p>
              <p v-else-if="calculatedEmployerContribution" class="text-xs text-gray-500 mt-1">
                = £{{ calculatedEmployerContribution.toLocaleString() }}/month
              </p>
              <p v-else class="text-xs text-gray-500 mt-1">Enter as percentage of salary (0-100)</p>
            </div>
          </div>

          <!-- Personal/SIPP: Fixed Monthly Contribution -->
          <div v-if="isPersonalPension">
            <label for="monthly_contribution_amount" class="block text-sm font-medium text-gray-700 mb-2">
              Monthly Contribution (£)
            </label>
            <input
              id="monthly_contribution_amount"
              v-model.number="formData.monthly_contribution_amount"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 500.00"
            />
            <p class="text-xs text-gray-500 mt-1">Fixed monthly contribution amount</p>
          </div>

          <!-- Expected Return and Retirement Age -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="expected_return_percent" class="block text-sm font-medium text-gray-700 mb-2">
                Expected Return (% p.a.)
              </label>
              <input
                id="expected_return_percent"
                v-model.number="formData.expected_return_percent"
                type="number"
                step="0.01"
                min="0"
                max="20"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 5.00"
              />
              <p class="text-xs text-gray-500 mt-1">Typical: 4-6% for balanced funds</p>
            </div>
            <div>
              <label for="retirement_age" class="block text-sm font-medium text-gray-700 mb-2">
                Planned Retirement Age
              </label>
              <input
                id="retirement_age"
                v-model.number="formData.retirement_age"
                type="number"
                min="50"
                max="75"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 67"
              />
            </div>
          </div>

          <!-- Salary Sacrifice -->
          <div class="flex items-centre">
            <input
              id="salary_sacrifice"
              v-model="formData.salary_sacrifice"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="salary_sacrifice" class="ml-2 block text-sm text-gray-700">
              Using salary sacrifice arrangement
            </label>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="formData.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="Any additional notes about this pension..."
            ></textarea>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-centre justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colours duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colours duration-200"
          >
            {{ isEdit ? 'Update' : 'Add' }} Pension
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DCPensionForm',

  props: {
    pension: {
      type: Object,
      default: null,
    },
    isEdit: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      formData: {
        scheme_type: '',
        scheme_name: '',
        provider: '',
        policy_number: '',
        current_fund_value: null,
        annual_salary: null,
        employee_contribution_percent: null,
        employer_contribution_percent: null,
        monthly_contribution_amount: null,
        expected_return_percent: 5.0,
        retirement_age: 67,
        salary_sacrifice: false,
        notes: '',
      },
      validationErrors: {
        employee_contribution_percent: '',
        employer_contribution_percent: '',
      },
    };
  },

  computed: {
    isWorkplacePension() {
      return this.formData.scheme_type === 'workplace';
    },

    isPersonalPension() {
      return this.formData.scheme_type === 'sipp' || this.formData.scheme_type === 'personal';
    },

    calculatedEmployeeContribution() {
      if (!this.isWorkplacePension || !this.formData.annual_salary || !this.formData.employee_contribution_percent) {
        return null;
      }
      return Math.round((this.formData.annual_salary * this.formData.employee_contribution_percent / 100) / 12);
    },

    calculatedEmployerContribution() {
      if (!this.isWorkplacePension || !this.formData.annual_salary || !this.formData.employer_contribution_percent) {
        return null;
      }
      return Math.round((this.formData.annual_salary * this.formData.employer_contribution_percent / 100) / 12);
    },
  },

  mounted() {
    if (this.isEdit && this.pension) {
      this.formData = { ...this.pension };
    }
  },

  methods: {
    handleSchemeTypeChange() {
      // Clear fields that don't apply to the selected pension type
      if (this.isWorkplacePension) {
        this.formData.monthly_contribution_amount = null;
      } else {
        this.formData.annual_salary = null;
        this.formData.employee_contribution_percent = null;
        this.formData.employer_contribution_percent = null;
      }
    },

    validateEmployeeContribution() {
      this.validationErrors.employee_contribution_percent = '';
      const value = this.formData.employee_contribution_percent;

      if (value !== null && value !== '') {
        if (value < 0) {
          this.validationErrors.employee_contribution_percent = 'Cannot be negative';
        } else if (value > 100) {
          this.validationErrors.employee_contribution_percent = 'Cannot exceed 100%';
        }
      }
    },

    validateEmployerContribution() {
      this.validationErrors.employer_contribution_percent = '';
      const value = this.formData.employer_contribution_percent;

      if (value !== null && value !== '') {
        if (value < 0) {
          this.validationErrors.employer_contribution_percent = 'Cannot be negative';
        } else if (value > 100) {
          this.validationErrors.employer_contribution_percent = 'Cannot exceed 100%';
        }
      }
    },

    handleSubmit() {
      // Validate contributions before submitting
      if (this.isWorkplacePension) {
        this.validateEmployeeContribution();
        this.validateEmployerContribution();

        // Check if there are any validation errors
        if (this.validationErrors.employee_contribution_percent || this.validationErrors.employer_contribution_percent) {
          return;
        }
      }

      // Basic validation
      if (!this.formData.scheme_type) {
        alert('Please select a pension type');
        return;
      }

      if (!this.formData.scheme_name) {
        alert('Please enter a scheme name');
        return;
      }

      if (!this.formData.provider) {
        alert('Please enter a provider name');
        return;
      }

      if (!this.formData.current_fund_value || this.formData.current_fund_value < 0) {
        alert('Please enter a valid current fund value');
        return;
      }

      if (this.isWorkplacePension && (!this.formData.annual_salary || this.formData.annual_salary <= 0)) {
        alert('Please enter your annual salary for workplace pension');
        return;
      }

      this.$emit('save', this.formData);
    },
  },
};
</script>

<style scoped>
/* Modal animation */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.fixed {
  animation: fadeIn 0.3s ease-out;
}

/* Scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
