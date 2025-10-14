<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-lg shadow-xl max-w-xl w-full">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <h3 class="text-xl font-semibold text-gray-900">Update State Pension Details</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Info Box -->
      <div class="mx-6 mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
        <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
          <p class="text-sm font-medium text-blue-900">Get Your State Pension Forecast</p>
          <p class="text-sm text-blue-800 mt-1">
            Check your State Pension forecast at
            <a href="https://www.gov.uk/check-state-pension" target="_blank" class="underline font-medium">gov.uk/check-state-pension</a>
          </p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="space-y-6">
          <!-- Forecast Weekly Amount -->
          <div>
            <label for="forecast_weekly_amount" class="block text-sm font-medium text-gray-700 mb-2">
              Forecast Weekly Amount (£) <span class="text-red-500">*</span>
            </label>
            <input
              id="forecast_weekly_amount"
              v-model.number="formData.forecast_weekly_amount"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 203.85"
            />
            <p class="text-xs text-gray-500 mt-1">
              Full new State Pension (2024/25): £203.85/week (£10,600/year)
            </p>
          </div>

          <!-- Qualifying Years -->
          <div>
            <label for="qualifying_years" class="block text-sm font-medium text-gray-700 mb-2">
              Qualifying Years <span class="text-red-500">*</span>
            </label>
            <input
              id="qualifying_years"
              v-model.number="formData.qualifying_years"
              type="number"
              min="0"
              max="50"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 25"
            />
            <p class="text-xs text-gray-500 mt-1">
              You need 35 qualifying years for the full new State Pension
            </p>
          </div>

          <!-- State Pension Age -->
          <div>
            <label for="state_pension_age" class="block text-sm font-medium text-gray-700 mb-2">
              Your State Pension Age <span class="text-red-500">*</span>
            </label>
            <input
              id="state_pension_age"
              v-model.number="formData.state_pension_age"
              type="number"
              min="60"
              max="75"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="e.g., 67"
            />
            <p class="text-xs text-gray-500 mt-1">
              Check your State Pension age at
              <a href="https://www.gov.uk/state-pension-age" target="_blank" class="text-indigo-600 hover:underline">gov.uk/state-pension-age</a>
            </p>
          </div>

          <!-- Forecast Date -->
          <div>
            <label for="forecast_date" class="block text-sm font-medium text-gray-700 mb-2">
              Forecast Date
            </label>
            <input
              id="forecast_date"
              v-model="formData.forecast_date"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">When did you check your forecast?</p>
          </div>

          <!-- NI Gaps -->
          <div class="flex items-start">
            <input
              id="has_ni_gaps"
              v-model="formData.has_ni_gaps"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-1"
            />
            <label for="has_ni_gaps" class="ml-2 block text-sm text-gray-700">
              I have National Insurance gaps that can be filled
            </label>
          </div>

          <!-- NI Gaps Details (conditional) -->
          <div v-if="formData.has_ni_gaps" class="pl-6 space-y-4">
            <div>
              <label for="gaps_years" class="block text-sm font-medium text-gray-700 mb-2">
                Number of Gap Years
              </label>
              <input
                id="gaps_years"
                v-model.number="formData.gaps_years"
                type="number"
                min="0"
                max="20"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 3"
              />
            </div>
            <div>
              <label for="estimated_gap_cost" class="block text-sm font-medium text-gray-700 mb-2">
                Estimated Cost to Fill Gaps (£)
              </label>
              <input
                id="estimated_gap_cost"
                v-model.number="formData.estimated_gap_cost"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                placeholder="e.g., 2500.00"
              />
              <p class="text-xs text-gray-500 mt-1">
                Typical cost: ~£800-900 per year (2024/25)
              </p>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="formData.notes"
              rows="2"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              placeholder="Any additional notes about your State Pension..."
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
            Update State Pension
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatePensionForm',

  props: {
    statePension: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      formData: {
        forecast_weekly_amount: null,
        qualifying_years: null,
        state_pension_age: 67,
        forecast_date: null,
        has_ni_gaps: false,
        gaps_years: null,
        estimated_gap_cost: null,
        notes: '',
      },
    };
  },

  mounted() {
    if (this.statePension) {
      this.formData = {
        ...this.statePension,
        forecast_date: this.statePension.forecast_date || null,
      };
    }
  },

  methods: {
    handleSubmit() {
      // Basic validation
      if (!this.formData.forecast_weekly_amount || this.formData.forecast_weekly_amount < 0) {
        alert('Please enter a valid forecast weekly amount');
        return;
      }

      if (!this.formData.qualifying_years || this.formData.qualifying_years < 0) {
        alert('Please enter valid qualifying years');
        return;
      }

      if (!this.formData.state_pension_age || this.formData.state_pension_age < 60) {
        alert('Please enter a valid State Pension age');
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
</style>
