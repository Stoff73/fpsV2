<template>
  <div class="space-y-6">
    <h3 class="text-lg font-semibold text-gray-800">Property Financials</h3>

    <!-- Annual Costs Breakdown -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-700">Annual Costs Breakdown</h4>
        <button
          @click="showEditCostsModal = true"
          class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
        >
          Edit Costs
        </button>
      </div>

      <dl class="space-y-2">
        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Service Charge:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.annual_service_charge) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Ground Rent:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.annual_ground_rent) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Insurance:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.annual_insurance) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Maintenance Reserve:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.annual_maintenance_reserve) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Other Costs:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.other_annual_costs) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Mortgage Payments (Annual):</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(annualMortgagePayments) }}</dd>
        </div>

        <div class="flex justify-between py-3 border-t-2 border-gray-300 mt-2">
          <dt class="text-base font-semibold text-gray-700">Total Annual Costs:</dt>
          <dd class="text-base font-bold text-gray-900">{{ formatCurrency(totalAnnualCosts) }}</dd>
        </div>
      </dl>

      <!-- Costs Chart -->
      <div class="mt-6">
        <div class="space-y-2">
          <div
            v-for="cost in costBreakdown"
            :key="cost.label"
            class="flex items-center"
          >
            <div class="w-32 text-sm text-gray-600">{{ cost.label }}:</div>
            <div class="flex-1">
              <div class="bg-gray-200 rounded-full h-6 overflow-hidden">
                <div
                  class="bg-blue-600 h-full flex items-center justify-end pr-2"
                  :style="{ width: cost.percentage + '%' }"
                >
                  <span v-if="cost.percentage > 15" class="text-xs font-medium text-white">
                    {{ formatCurrency(cost.value) }}
                  </span>
                </div>
              </div>
            </div>
            <div class="w-16 text-right text-sm text-gray-600 ml-2">
              {{ cost.percentage.toFixed(1) }}%
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Buy to Let Financials -->
    <div v-if="property.property_type === 'buy_to_let'" class="bg-white border border-gray-200 rounded-lg p-6">
      <h4 class="text-md font-semibold text-gray-700 mb-4">Rental Income Analysis</h4>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg p-4">
          <p class="text-sm text-green-700">Annual Rental Income</p>
          <p class="text-2xl font-bold text-green-900">{{ formatCurrency(property.annual_rental_income) }}</p>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
          <p class="text-sm text-blue-700">Net Rental Income</p>
          <p class="text-2xl font-bold text-blue-900">{{ formatCurrency(netRentalIncome) }}</p>
          <p class="text-xs text-blue-600 mt-1">After costs</p>
        </div>

        <div class="bg-purple-50 rounded-lg p-4">
          <p class="text-sm text-purple-700">Net Rental Yield</p>
          <p class="text-2xl font-bold text-purple-900">{{ netRentalYield }}%</p>
        </div>
      </div>

      <dl class="space-y-2">
        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Monthly Rental Income:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.monthly_rental_income) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Occupancy Rate:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ property.occupancy_rate_percent || 100 }}%</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Actual Annual Income:</dt>
          <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(actualAnnualIncome) }}</dd>
        </div>

        <div class="flex justify-between py-2 border-b border-gray-100">
          <dt class="text-sm text-gray-600">Less: Annual Costs:</dt>
          <dd class="text-sm font-medium text-red-600">-{{ formatCurrency(totalAnnualCosts) }}</dd>
        </div>

        <div class="flex justify-between py-3 border-t-2 border-gray-300 mt-2">
          <dt class="text-base font-semibold text-gray-700">Net Annual Income:</dt>
          <dd class="text-base font-bold" :class="netRentalIncome >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ formatCurrency(netRentalIncome) }}
          </dd>
        </div>
      </dl>

      <div v-if="property.tenant_name" class="mt-4 p-4 bg-gray-50 rounded-md">
        <h5 class="text-sm font-semibold text-gray-700 mb-2">Tenancy Information</h5>
        <dl class="space-y-1 text-sm">
          <div class="flex justify-between">
            <dt class="text-gray-600">Tenant:</dt>
            <dd class="font-medium text-gray-900">{{ property.tenant_name }}</dd>
          </div>
          <div v-if="property.lease_start_date" class="flex justify-between">
            <dt class="text-gray-600">Lease Start:</dt>
            <dd class="font-medium text-gray-900">{{ formatDate(property.lease_start_date) }}</dd>
          </div>
          <div v-if="property.lease_end_date" class="flex justify-between">
            <dt class="text-gray-600">Lease End:</dt>
            <dd class="font-medium text-gray-900">{{ formatDate(property.lease_end_date) }}</dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- SDLT Paid -->
    <div v-if="property.sdlt_paid" class="bg-white border border-gray-200 rounded-lg p-6">
      <h4 class="text-md font-semibold text-gray-700 mb-4">Stamp Duty Land Tax</h4>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">SDLT Paid at Purchase:</span>
        <span class="text-lg font-bold text-gray-900">{{ formatCurrency(property.sdlt_paid) }}</span>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-lg p-6">
      <h4 class="text-md font-semibold text-gray-800 mb-4">Financial Summary</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-700">Total Investment:</p>
          <p class="text-xl font-bold text-gray-900">{{ formatCurrency(totalInvestment) }}</p>
          <p class="text-xs text-gray-600 mt-1">Purchase price + SDLT + improvements</p>
        </div>

        <div>
          <p class="text-sm text-gray-700">Current Equity:</p>
          <p class="text-xl font-bold text-green-600">{{ formatCurrency(property.equity || 0) }}</p>
          <p class="text-xs text-gray-600 mt-1">Current value - mortgage balance</p>
        </div>

        <div>
          <p class="text-sm text-gray-700">Value Appreciation:</p>
          <p class="text-xl font-bold" :class="valueChange >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ formatCurrency(valueChange) }}
          </p>
          <p class="text-xs text-gray-600 mt-1">{{ valueChangePercent }}% since purchase</p>
        </div>

        <div v-if="property.property_type === 'buy_to_let'">
          <p class="text-sm text-gray-700">Annual Cash Flow:</p>
          <p class="text-xl font-bold" :class="netRentalIncome >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ formatCurrency(netRentalIncome) }}
          </p>
          <p class="text-xs text-gray-600 mt-1">Rental income - costs</p>
        </div>
      </div>
    </div>

    <!-- Edit Costs Modal -->
    <div v-if="showEditCostsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
      <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
          <div class="flex items-center justify-between">
            <h3 class="text-2xl font-semibold text-gray-900">Edit Annual Costs</h3>
            <button
              @click="closeEditCostsModal"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Content -->
        <form @submit.prevent="handleSaveCosts">
          <div class="px-6 py-4 space-y-4">
            <div>
              <label for="annual_service_charge" class="block text-sm font-medium text-gray-700 mb-1">
                Annual Service Charge (£)
              </label>
              <input
                id="annual_service_charge"
                v-model.number="costsForm.annual_service_charge"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="annual_ground_rent" class="block text-sm font-medium text-gray-700 mb-1">
                Annual Ground Rent (£)
              </label>
              <input
                id="annual_ground_rent"
                v-model.number="costsForm.annual_ground_rent"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="annual_insurance" class="block text-sm font-medium text-gray-700 mb-1">
                Annual Insurance (£)
              </label>
              <input
                id="annual_insurance"
                v-model.number="costsForm.annual_insurance"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="annual_maintenance_reserve" class="block text-sm font-medium text-gray-700 mb-1">
                Annual Maintenance Reserve (£)
              </label>
              <input
                id="annual_maintenance_reserve"
                v-model.number="costsForm.annual_maintenance_reserve"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="other_annual_costs" class="block text-sm font-medium text-gray-700 mb-1">
                Other Annual Costs (£)
              </label>
              <input
                id="other_annual_costs"
                v-model.number="costsForm.other_annual_costs"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label for="sdlt_paid" class="block text-sm font-medium text-gray-700 mb-1">
                SDLT Paid (£)
              </label>
              <input
                id="sdlt_paid"
                v-model.number="costsForm.sdlt_paid"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Error Message -->
            <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-md">
              <p class="text-sm text-red-600">{{ error }}</p>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end space-x-2 rounded-b-lg">
            <button
              type="button"
              @click="closeEditCostsModal"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Saving...' : 'Save Costs' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PropertyFinancials',

  props: {
    property: {
      type: Object,
      required: true,
    },
    mortgages: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      showEditCostsModal: false,
      submitting: false,
      error: null,
      costsForm: {
        annual_service_charge: null,
        annual_ground_rent: null,
        annual_insurance: null,
        annual_maintenance_reserve: null,
        other_annual_costs: null,
        sdlt_paid: null,
      },
    };
  },

  computed: {
    annualMortgagePayments() {
      return this.mortgages.reduce((total, mortgage) => {
        return total + ((mortgage.monthly_payment || 0) * 12);
      }, 0);
    },

    totalAnnualCosts() {
      return (
        (this.property.annual_service_charge || 0) +
        (this.property.annual_ground_rent || 0) +
        (this.property.annual_insurance || 0) +
        (this.property.annual_maintenance_reserve || 0) +
        (this.property.other_annual_costs || 0) +
        this.annualMortgagePayments
      );
    },

    costBreakdown() {
      const costs = [
        { label: 'Mortgage Payments', value: this.annualMortgagePayments },
        { label: 'Service Charge', value: this.property.annual_service_charge || 0 },
        { label: 'Ground Rent', value: this.property.annual_ground_rent || 0 },
        { label: 'Insurance', value: this.property.annual_insurance || 0 },
        { label: 'Maintenance', value: this.property.annual_maintenance_reserve || 0 },
        { label: 'Other', value: this.property.other_annual_costs || 0 },
      ];

      const total = this.totalAnnualCosts || 1; // Avoid division by zero

      return costs
        .map(cost => ({
          ...cost,
          percentage: (cost.value / total) * 100,
        }))
        .filter(cost => cost.value > 0)
        .sort((a, b) => b.value - a.value);
    },

    actualAnnualIncome() {
      const income = this.property.annual_rental_income || 0;
      const occupancyRate = (this.property.occupancy_rate_percent || 100) / 100;
      return income * occupancyRate;
    },

    netRentalIncome() {
      return this.actualAnnualIncome - this.totalAnnualCosts;
    },

    netRentalYield() {
      const currentValue = this.property.current_value || 0;
      if (currentValue === 0) return '0.00';
      const yieldValue = (this.netRentalIncome / currentValue) * 100;
      return yieldValue.toFixed(2);
    },

    totalInvestment() {
      return (
        (this.property.purchase_price || 0) +
        (this.property.sdlt_paid || 0) +
        (this.property.improvement_costs || 0)
      );
    },

    valueChange() {
      return (this.property.current_value || 0) - (this.property.purchase_price || 0);
    },

    valueChangePercent() {
      const purchasePrice = this.property.purchase_price || 0;
      if (purchasePrice === 0) return '0.00';
      const percent = (this.valueChange / purchasePrice) * 100;
      return percent > 0 ? `+${percent.toFixed(2)}` : percent.toFixed(2);
    },
  },

  watch: {
    property: {
      immediate: true,
      handler(newProperty) {
        if (newProperty) {
          this.populateCostsForm();
        }
      },
    },
  },

  methods: {
    populateCostsForm() {
      this.costsForm.annual_service_charge = this.property.annual_service_charge || null;
      this.costsForm.annual_ground_rent = this.property.annual_ground_rent || null;
      this.costsForm.annual_insurance = this.property.annual_insurance || null;
      this.costsForm.annual_maintenance_reserve = this.property.annual_maintenance_reserve || null;
      this.costsForm.other_annual_costs = this.property.other_annual_costs || null;
      this.costsForm.sdlt_paid = this.property.sdlt_paid || null;
    },

    closeEditCostsModal() {
      this.showEditCostsModal = false;
      this.error = null;
      this.populateCostsForm(); // Reset form to current property values
    },

    async handleSaveCosts() {
      this.submitting = true;
      this.error = null;

      try {
        // Emit event to parent to handle the update
        this.$emit('update-costs', this.costsForm);
        this.showEditCostsModal = false;
      } catch (error) {
        console.error('Failed to save costs:', error);
        this.error = error.message || 'Failed to save costs. Please try again.';
      } finally {
        this.submitting = false;
      }
    },

    formatCurrency(value) {
      if (value === null || value === undefined) return '£0';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      });
    },
  },
};
</script>
