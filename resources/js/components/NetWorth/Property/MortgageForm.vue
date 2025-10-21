<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-semibold text-gray-900">
            {{ isEditMode ? 'Edit Mortgage' : 'Add Mortgage' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit">
        <div class="px-6 py-4 space-y-4">
          <div>
            <label for="lender_name" class="block text-sm font-medium text-gray-700 mb-1">Lender Name <span class="text-red-500">*</span></label>
            <input
              id="lender_name"
              v-model="form.lender_name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label for="mortgage_account_number" class="block text-sm font-medium text-gray-700 mb-1">Mortgage Account Number</label>
            <input
              id="mortgage_account_number"
              v-model="form.mortgage_account_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label for="mortgage_type" class="block text-sm font-medium text-gray-700 mb-1">Mortgage Type <span class="text-red-500">*</span></label>
            <select
              id="mortgage_type"
              v-model="form.mortgage_type"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select mortgage type</option>
              <option value="repayment">Repayment</option>
              <option value="interest_only">Interest Only</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="original_loan_amount" class="block text-sm font-medium text-gray-700 mb-1">Original Loan Amount (£) <span class="text-red-500">*</span></label>
              <input
                id="original_loan_amount"
                v-model.number="form.original_loan_amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="outstanding_balance" class="block text-sm font-medium text-gray-700 mb-1">Outstanding Balance (£) <span class="text-red-500">*</span></label>
              <input
                id="outstanding_balance"
                v-model.number="form.outstanding_balance"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="interest_rate" class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%) <span class="text-red-500">*</span></label>
              <input
                id="interest_rate"
                v-model.number="form.interest_rate"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="rate_type" class="block text-sm font-medium text-gray-700 mb-1">Rate Type <span class="text-red-500">*</span></label>
              <select
                id="rate_type"
                v-model="form.rate_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select rate type</option>
                <option value="fixed">Fixed</option>
                <option value="variable">Variable</option>
                <option value="tracker">Tracker</option>
                <option value="discount">Discount</option>
              </select>
            </div>
          </div>

          <div v-if="form.rate_type === 'fixed'">
            <label for="rate_fix_end_date" class="block text-sm font-medium text-gray-700 mb-1">Rate Fix End Date</label>
            <input
              id="rate_fix_end_date"
              v-model="form.rate_fix_end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label for="monthly_payment" class="block text-sm font-medium text-gray-700 mb-1">
              Monthly Payment (£) <span class="text-red-500">*</span>
            </label>
            <div class="flex space-x-2">
              <input
                id="monthly_payment"
                v-model.number="form.monthly_payment"
                type="number"
                step="0.01"
                min="0"
                required
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                type="button"
                @click="calculatePayment"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors whitespace-nowrap"
              >
                Calculate
              </button>
            </div>
            <p class="text-sm text-gray-500 mt-1">Click "Calculate" to auto-calculate based on loan amount, interest rate, and term</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="maturity_date" class="block text-sm font-medium text-gray-700 mb-1">Maturity Date <span class="text-red-500">*</span></label>
              <input
                id="maturity_date"
                v-model="form.maturity_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div v-if="remainingTermMonths !== null" class="p-3 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-sm text-blue-700">
              <strong>Remaining Term:</strong> {{ remainingTermYears }} years ({{ remainingTermMonths }} months)
            </p>
          </div>

          <!-- Joint Ownership Section -->
          <div class="space-y-4 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900">Ownership</h4>

            <!-- Ownership Type -->
            <div>
              <label for="ownership_type" class="block text-sm font-medium text-gray-700 mb-1">
                Ownership Type <span class="text-red-500">*</span>
              </label>
              <select
                id="ownership_type"
                v-model="form.ownership_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="sole">Sole Owner</option>
                <option value="joint">Joint Owner</option>
              </select>
            </div>

            <!-- Joint Owner (if ownership_type is joint) -->
            <div v-if="form.ownership_type === 'joint'">
              <label for="joint_owner_id" class="block text-sm font-medium text-gray-700 mb-1">
                Joint Owner <span class="text-red-500">*</span>
              </label>
              <select
                id="joint_owner_id"
                v-model="form.joint_owner_id"
                :required="form.ownership_type === 'joint'"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select joint owner</option>
                <option v-if="spouse" :value="spouse.id">{{ spouse.name }} (Spouse)</option>
                <option v-if="!spouse" value="" disabled>No spouse linked - add spouse in Family Members</option>
              </select>
              <p class="text-sm text-gray-500 mt-1">
                Joint mortgages will appear in both your and your spouse's accounts.
              </p>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>

          <!-- Success Message -->
          <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-600">{{ successMessage }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-2 rounded-b-lg">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>

          <button
            type="submit"
            :disabled="submitting"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Saving...' : (isEditMode ? 'Update Mortgage' : 'Save Mortgage') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
  name: 'MortgageForm',

  props: {
    mortgage: {
      type: Object,
      default: null,
    },
    propertyId: {
      type: Number,
      required: true,
    },
  },

  data() {
    return {
      form: {
        lender_name: '',
        mortgage_account_number: '',
        mortgage_type: '',
        original_loan_amount: null,
        outstanding_balance: null,
        interest_rate: null,
        rate_type: '',
        rate_fix_end_date: '',
        monthly_payment: null,
        start_date: '',
        maturity_date: '',
        ownership_type: 'sole',
        joint_owner_id: null,
      },
      submitting: false,
      error: null,
      successMessage: null,
    };
  },

  computed: {
    isEditMode() {
      return this.mortgage !== null;
    },

    spouse() {
      return this.$store.getters['userProfile/spouse'];
    },

    remainingTermMonths() {
      if (!this.form.start_date || !this.form.maturity_date) {
        return null;
      }

      const start = new Date(this.form.start_date);
      const maturity = new Date(this.form.maturity_date);
      const diffTime = Math.abs(maturity - start);
      const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30));
      return diffMonths;
    },

    remainingTermYears() {
      if (this.remainingTermMonths === null) {
        return null;
      }
      return (this.remainingTermMonths / 12).toFixed(1);
    },
  },

  mounted() {
    if (this.mortgage) {
      this.populateForm();
    }
  },

  methods: {
    ...mapActions('netWorth', ['calculateMortgagePayment']),

    populateForm() {
      Object.keys(this.form).forEach(key => {
        if (this.mortgage[key] !== undefined) {
          this.form[key] = this.mortgage[key];
        }
      });
    },

    async calculatePayment() {
      if (!this.form.original_loan_amount || !this.form.interest_rate || !this.remainingTermMonths) {
        this.error = 'Please fill in loan amount, interest rate, start date, and maturity date first.';
        return;
      }

      try {
        const result = await this.calculateMortgagePayment({
          loan_amount: this.form.original_loan_amount,
          annual_interest_rate: this.form.interest_rate,
          term_months: this.remainingTermMonths,
          mortgage_type: this.form.mortgage_type || 'repayment',
        });

        this.form.monthly_payment = result.monthly_payment;
        this.successMessage = `Monthly payment calculated: £${result.monthly_payment.toFixed(2)}`;
        setTimeout(() => {
          this.successMessage = null;
        }, 3000);
      } catch (error) {
        this.error = 'Failed to calculate monthly payment.';
      }
    },

    validateForm() {
      if (!this.form.lender_name || !this.form.mortgage_type) {
        this.error = 'Please fill in all required fields.';
        return false;
      }

      if (!this.form.original_loan_amount || !this.form.outstanding_balance || !this.form.interest_rate) {
        this.error = 'Please fill in all financial fields.';
        return false;
      }

      if (!this.form.rate_type || !this.form.monthly_payment) {
        this.error = 'Please fill in rate type and monthly payment.';
        return false;
      }

      if (!this.form.start_date || !this.form.maturity_date) {
        this.error = 'Please fill in start date and maturity date.';
        return false;
      }

      if (new Date(this.form.maturity_date) <= new Date(this.form.start_date)) {
        this.error = 'Maturity date must be after start date.';
        return false;
      }

      this.error = null;
      return true;
    },

    handleSubmit() {
      if (!this.validateForm()) {
        return;
      }

      this.submitting = true;
      this.error = null;

      // Emit 'save' event (NOT 'submit' - see CLAUDE.md)
      this.$emit('save', this.form);
    },
  },
};
</script>
