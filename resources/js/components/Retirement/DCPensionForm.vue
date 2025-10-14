<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900">
          {{ isEdit ? 'Edit' : 'Add' }} DC Pension
        </h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
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

          <!-- Contributions -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                max="100"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 5.00"
              />
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
                max="100"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 3.00"
              />
            </div>
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
          <div class="flex items-center">
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
        <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
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
        scheme_name: '',
        provider: '',
        policy_number: '',
        current_fund_value: null,
        employee_contribution_percent: null,
        employer_contribution_percent: null,
        expected_return_percent: 5.0,
        retirement_age: 67,
        salary_sacrifice: false,
        notes: '',
      },
    };
  },

  mounted() {
    if (this.isEdit && this.pension) {
      this.formData = { ...this.pension };
    }
  },

  methods: {
    handleSubmit() {
      // Basic validation
      if (!this.formData.scheme_name) {
        alert('Please enter a scheme name');
        return;
      }

      if (!this.formData.current_fund_value || this.formData.current_fund_value < 0) {
        alert('Please enter a valid current fund value');
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
