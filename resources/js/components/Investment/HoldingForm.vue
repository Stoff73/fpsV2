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
              {{ isEditMode ? 'Edit Holding' : 'Add New Holding' }}
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
            <!-- Account Selection -->
            <div>
              <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">
                Account <span class="text-red-500">*</span>
              </label>
              <select
                id="account_id"
                v-model="formData.account_id"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.account_id }"
                required
              >
                <option value="">Select an account</option>
                <option v-for="account in accounts" :key="account.id" :value="account.id">
                  {{ account.account_type }} - {{ account.provider }}
                </option>
              </select>
              <p v-if="errors.account_id" class="mt-1 text-sm text-red-600">{{ errors.account_id }}</p>
            </div>

            <!-- Security Name -->
            <div>
              <label for="security_name" class="block text-sm font-medium text-gray-700 mb-1">
                Security Name <span class="text-red-500">*</span>
              </label>
              <input
                id="security_name"
                v-model="formData.security_name"
                type="text"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.security_name }"
                placeholder="e.g., Vanguard FTSE All-World"
                required
              />
              <p v-if="errors.security_name" class="mt-1 text-sm text-red-600">{{ errors.security_name }}</p>
            </div>

            <!-- Ticker and ISIN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="ticker" class="block text-sm font-medium text-gray-700 mb-1">
                  Ticker
                </label>
                <input
                  id="ticker"
                  v-model="formData.ticker"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., VWRL"
                />
              </div>
              <div>
                <label for="isin" class="block text-sm font-medium text-gray-700 mb-1">
                  ISIN
                </label>
                <input
                  id="isin"
                  v-model="formData.isin"
                  type="text"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="e.g., IE00B3RBWM25"
                />
              </div>
            </div>

            <!-- Asset Type -->
            <div>
              <label for="asset_type" class="block text-sm font-medium text-gray-700 mb-1">
                Asset Type <span class="text-red-500">*</span>
              </label>
              <select
                id="asset_type"
                v-model="formData.asset_type"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{ 'border-red-500': errors.asset_type }"
                required
              >
                <option value="">Select asset type</option>
                <option value="uk_equity">UK Equity</option>
                <option value="us_equity">US Equity</option>
                <option value="international_equity">International Equity</option>
                <option value="bond">Bond</option>
                <option value="cash">Cash</option>
                <option value="alternative">Alternative</option>
                <option value="property">Property</option>
              </select>
              <p v-if="errors.asset_type" class="mt-1 text-sm text-red-600">{{ errors.asset_type }}</p>
            </div>

            <!-- Quantity and Purchase Price -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                  Quantity <span class="text-red-500">*</span>
                </label>
                <input
                  id="quantity"
                  v-model.number="formData.quantity"
                  type="number"
                  step="0.0001"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.quantity }"
                  required
                />
                <p v-if="errors.quantity" class="mt-1 text-sm text-red-600">{{ errors.quantity }}</p>
              </div>
              <div>
                <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-1">
                  Purchase Price (£) <span class="text-red-500">*</span>
                </label>
                <input
                  id="purchase_price"
                  v-model.number="formData.purchase_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.purchase_price }"
                  required
                />
                <p v-if="errors.purchase_price" class="mt-1 text-sm text-red-600">{{ errors.purchase_price }}</p>
              </div>
            </div>

            <!-- Purchase Date and Current Price -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Purchase Date <span class="text-red-500">*</span>
                </label>
                <input
                  id="purchase_date"
                  v-model="formData.purchase_date"
                  type="date"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.purchase_date }"
                  :max="today"
                  required
                />
                <p v-if="errors.purchase_date" class="mt-1 text-sm text-red-600">{{ errors.purchase_date }}</p>
              </div>
              <div>
                <label for="current_price" class="block text-sm font-medium text-gray-700 mb-1">
                  Current Price (£) <span class="text-red-500">*</span>
                </label>
                <input
                  id="current_price"
                  v-model.number="formData.current_price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': errors.current_price }"
                  required
                />
                <p v-if="errors.current_price" class="mt-1 text-sm text-red-600">{{ errors.current_price }}</p>
              </div>
            </div>

            <!-- OCF Percent -->
            <div>
              <label for="ocf_percent" class="block text-sm font-medium text-gray-700 mb-1">
                Ongoing Charge Figure (OCF) %
              </label>
              <input
                id="ocf_percent"
                v-model.number="formData.ocf_percent"
                type="number"
                step="0.01"
                min="0"
                max="10"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="e.g., 0.22"
              />
              <p class="mt-1 text-xs text-gray-500">Annual management fee as a percentage</p>
            </div>

            <!-- Calculated Fields Display -->
            <div v-if="formData.quantity && formData.current_price" class="bg-blue-50 border border-blue-200 rounded-md p-4">
              <h4 class="text-sm font-semibold text-blue-900 mb-2">Calculated Values</h4>
              <div class="grid grid-cols-2 gap-2 text-sm">
                <div>
                  <span class="text-blue-700">Current Value:</span>
                  <span class="ml-2 font-medium text-blue-900">{{ formatCurrency(currentValue) }}</span>
                </div>
                <div v-if="formData.purchase_price">
                  <span class="text-blue-700">Return:</span>
                  <span class="ml-2 font-medium" :class="returnClass">{{ formatReturn(returnPercent) }}</span>
                </div>
              </div>
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
              {{ submitting ? 'Saving...' : (isEditMode ? 'Update Holding' : 'Add Holding') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HoldingForm',

  props: {
    show: {
      type: Boolean,
      required: true,
    },
    holding: {
      type: Object,
      default: null,
    },
    accounts: {
      type: Array,
      required: true,
    },
  },

  data() {
    return {
      formData: {
        account_id: '',
        security_name: '',
        ticker: '',
        isin: '',
        asset_type: '',
        quantity: null,
        purchase_price: null,
        purchase_date: '',
        current_price: null,
        ocf_percent: null,
      },
      errors: {},
      submitting: false,
    };
  },

  computed: {
    isEditMode() {
      return !!this.holding;
    },

    today() {
      return new Date().toISOString().split('T')[0];
    },

    currentValue() {
      return (this.formData.quantity || 0) * (this.formData.current_price || 0);
    },

    returnPercent() {
      if (!this.formData.purchase_price || !this.formData.current_price) return 0;
      return ((this.formData.current_price - this.formData.purchase_price) / this.formData.purchase_price) * 100;
    },

    returnClass() {
      if (this.returnPercent > 0) return 'text-green-600';
      if (this.returnPercent < 0) return 'text-red-600';
      return 'text-gray-600';
    },
  },

  watch: {
    holding: {
      immediate: true,
      handler(newHolding) {
        if (newHolding) {
          this.formData = { ...newHolding };
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

      if (!this.formData.account_id) {
        this.errors.account_id = 'Account is required';
        isValid = false;
      }

      if (!this.formData.security_name || this.formData.security_name.trim().length === 0) {
        this.errors.security_name = 'Security name is required';
        isValid = false;
      }

      if (!this.formData.asset_type) {
        this.errors.asset_type = 'Asset type is required';
        isValid = false;
      }

      if (!this.formData.quantity || this.formData.quantity <= 0) {
        this.errors.quantity = 'Quantity must be greater than 0';
        isValid = false;
      }

      if (!this.formData.purchase_price || this.formData.purchase_price <= 0) {
        this.errors.purchase_price = 'Purchase price must be greater than 0';
        isValid = false;
      }

      if (!this.formData.purchase_date) {
        this.errors.purchase_date = 'Purchase date is required';
        isValid = false;
      }

      if (!this.formData.current_price || this.formData.current_price <= 0) {
        this.errors.current_price = 'Current price must be greater than 0';
        isValid = false;
      }

      return isValid;
    },

    closeModal() {
      this.$emit('close');
      this.resetForm();
    },

    resetForm() {
      this.formData = {
        account_id: '',
        security_name: '',
        ticker: '',
        isin: '',
        asset_type: '',
        quantity: null,
        purchase_price: null,
        purchase_date: '',
        current_price: null,
        ocf_percent: null,
      };
      this.errors = {};
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value || 0);
    },

    formatReturn(value) {
      const sign = value >= 0 ? '+' : '';
      return `${sign}${value.toFixed(2)}%`;
    },
  },
};
</script>
