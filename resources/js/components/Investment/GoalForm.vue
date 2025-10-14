<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeModal">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ isEditMode ? 'Edit Investment Goal' : 'Add New Investment Goal' }}
            </h3>
            <button
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm">
          <div class="bg-white px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- Goal Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Goal Name <span class="text-red-500">*</span>
              </label>
              <input
                id="name"
                v-model="formData.name"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.name }"
                placeholder="e.g., Retirement Fund, House Deposit, Children's Education"
                required
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                id="description"
                v-model="formData.description"
                rows="2"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Brief description of this goal (optional)"
              ></textarea>
            </div>

            <!-- Target Amount and Date -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="target_amount" class="block text-sm font-medium text-gray-700 mb-1">
                  Target Amount (£) <span class="text-red-500">*</span>
                </label>
                <input
                  id="target_amount"
                  v-model.number="formData.target_amount"
                  type="number"
                  step="1000"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.target_amount }"
                  placeholder="e.g., 500000"
                  required
                />
                <p v-if="errors.target_amount" class="mt-1 text-sm text-red-600">{{ errors.target_amount }}</p>
              </div>
              <div>
                <label for="target_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Target Date <span class="text-red-500">*</span>
                </label>
                <input
                  id="target_date"
                  v-model="formData.target_date"
                  type="date"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.target_date }"
                  :min="minDate"
                  required
                />
                <p v-if="errors.target_date" class="mt-1 text-sm text-red-600">{{ errors.target_date }}</p>
              </div>
            </div>

            <!-- Monthly Contribution and Initial Value -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="monthly_contribution" class="block text-sm font-medium text-gray-700 mb-1">
                  Monthly Contribution (£)
                </label>
                <input
                  id="monthly_contribution"
                  v-model.number="formData.monthly_contribution"
                  type="number"
                  step="10"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., 500"
                />
                <p class="mt-1 text-xs text-gray-500">How much you plan to contribute each month</p>
              </div>
              <div>
                <label for="initial_value" class="block text-sm font-medium text-gray-700 mb-1">
                  Initial Value (£)
                </label>
                <input
                  id="initial_value"
                  v-model.number="formData.initial_value"
                  type="number"
                  step="100"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., 10000"
                />
                <p class="mt-1 text-xs text-gray-500">Current amount already saved for this goal</p>
              </div>
            </div>

            <!-- Expected Return and Risk Level -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="expected_return" class="block text-sm font-medium text-gray-700 mb-1">
                  Expected Annual Return (%)
                </label>
                <input
                  id="expected_return"
                  v-model.number="formData.expected_return"
                  type="number"
                  step="0.1"
                  min="0"
                  max="20"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., 7.0"
                />
                <p class="mt-1 text-xs text-gray-500">Assumed average annual return (default: 7%)</p>
              </div>
              <div>
                <label for="risk_level" class="block text-sm font-medium text-gray-700 mb-1">
                  Risk Level
                </label>
                <select
                  id="risk_level"
                  v-model="formData.risk_level"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Select risk level</option>
                  <option value="conservative">Conservative</option>
                  <option value="moderate">Moderate</option>
                  <option value="balanced">Balanced</option>
                  <option value="growth">Growth</option>
                  <option value="aggressive">Aggressive</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Investment risk tolerance for this goal</p>
              </div>
            </div>

            <!-- Goal Type -->
            <div>
              <label for="goal_type" class="block text-sm font-medium text-gray-700 mb-1">
                Goal Type
              </label>
              <select
                id="goal_type"
                v-model="formData.goal_type"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select goal type</option>
                <option value="retirement">Retirement</option>
                <option value="property">Property Purchase</option>
                <option value="education">Education</option>
                <option value="emergency">Emergency Fund</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Priority -->
            <div>
              <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                Priority
              </label>
              <select
                id="priority"
                v-model="formData.priority"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
              </select>
            </div>

            <!-- Calculation Preview -->
            <div v-if="formData.target_amount && formData.target_date" class="bg-blue-50 border border-blue-200 rounded-md p-4">
              <h4 class="text-sm font-semibold text-blue-900 mb-2">Quick Estimate</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-blue-700">Time Horizon:</span>
                  <span class="ml-2 font-medium text-blue-900">{{ timeHorizon }}</span>
                </div>
                <div>
                  <span class="text-blue-700">Required Monthly:</span>
                  <span class="ml-2 font-medium text-blue-900">{{ formatCurrency(requiredMonthly) }}</span>
                </div>
              </div>
              <p class="text-xs text-blue-700 mt-2">
                * Based on {{ formData.expected_return || 7 }}% annual return, starting from {{ formatCurrency(formData.initial_value || 0) }}
              </p>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Saving...' : (isEditMode ? 'Update Goal' : 'Create Goal') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GoalForm',

  props: {
    show: {
      type: Boolean,
      required: true,
    },
    goal: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      formData: {
        name: '',
        description: '',
        target_amount: null,
        target_date: '',
        monthly_contribution: null,
        initial_value: null,
        expected_return: 7.0,
        risk_level: 'moderate',
        goal_type: '',
        priority: 'medium',
      },
      errors: {},
      submitting: false,
    };
  },

  computed: {
    isEditMode() {
      return !!this.goal;
    },

    minDate() {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      return tomorrow.toISOString().split('T')[0];
    },

    timeHorizon() {
      if (!this.formData.target_date) return 'N/A';
      const target = new Date(this.formData.target_date);
      const now = new Date();
      const diffTime = target - now;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays < 365) {
        const months = Math.round(diffDays / 30);
        return `${months} ${months === 1 ? 'month' : 'months'}`;
      }
      const years = Math.floor(diffDays / 365);
      const months = Math.round((diffDays % 365) / 30);
      if (months === 0) return `${years} ${years === 1 ? 'year' : 'years'}`;
      return `${years}y ${months}m`;
    },

    requiredMonthly() {
      if (!this.formData.target_amount || !this.formData.target_date) return 0;

      const target = this.formData.target_amount;
      const initial = this.formData.initial_value || 0;
      const rate = (this.formData.expected_return || 7) / 100 / 12; // Monthly rate
      const targetDate = new Date(this.formData.target_date);
      const now = new Date();
      const months = Math.max(1, Math.round((targetDate - now) / (1000 * 60 * 60 * 24 * 30)));

      // Future Value of Initial Investment
      const fvInitial = initial * Math.pow(1 + rate, months);

      // Required Future Value from contributions
      const fvRequired = target - fvInitial;

      if (fvRequired <= 0) return 0;

      // PMT formula: FV = PMT * [((1 + r)^n - 1) / r]
      const monthlyPayment = fvRequired / (((Math.pow(1 + rate, months) - 1) / rate));

      return Math.max(0, monthlyPayment);
    },
  },

  watch: {
    goal: {
      immediate: true,
      handler(newGoal) {
        if (newGoal) {
          this.formData = { ...newGoal };
        } else {
          this.resetForm();
        }
      },
    },
    show(newVal) {
      if (!newVal) {
        this.errors = {};
      }
    },
  },

  methods: {
    async submitForm() {
      this.errors = {};
      this.submitting = true;

      try {
        // Client-side validation
        if (!this.validateForm()) {
          this.submitting = false;
          return;
        }

        this.$emit('submit', { ...this.formData });
        this.closeModal();
      } catch (error) {
        console.error('Form submission error:', error);
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
        }
      } finally {
        this.submitting = false;
      }
    },

    validateForm() {
      let isValid = true;

      if (!this.formData.name || this.formData.name.trim().length === 0) {
        this.errors.name = 'Goal name is required';
        isValid = false;
      }

      if (!this.formData.target_amount || this.formData.target_amount <= 0) {
        this.errors.target_amount = 'Target amount must be greater than 0';
        isValid = false;
      }

      if (!this.formData.target_date) {
        this.errors.target_date = 'Target date is required';
        isValid = false;
      } else {
        const targetDate = new Date(this.formData.target_date);
        const today = new Date();
        if (targetDate <= today) {
          this.errors.target_date = 'Target date must be in the future';
          isValid = false;
        }
      }

      return isValid;
    },

    closeModal() {
      this.$emit('close');
      this.resetForm();
    },

    resetForm() {
      this.formData = {
        name: '',
        description: '',
        target_amount: null,
        target_date: '',
        monthly_contribution: null,
        initial_value: null,
        expected_return: 7.0,
        risk_level: 'moderate',
        goal_type: '',
        priority: 'medium',
      };
      this.errors = {};
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value || 0);
    },
  },
};
</script>
