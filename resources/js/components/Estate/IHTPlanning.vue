<template>
  <div class="iht-planning-tab">
    <!-- IHT Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-purple-50 rounded-lg p-6">
        <p class="text-sm text-purple-600 font-medium mb-2">Taxable Estate</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedTaxableEstate }}</p>
      </div>
      <div class="bg-green-50 rounded-lg p-6">
        <p class="text-sm text-green-600 font-medium mb-2">Available Allowances</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedAllowances }}</p>
      </div>
      <div class="bg-red-50 rounded-lg p-6">
        <p class="text-sm text-red-600 font-medium mb-2">IHT Liability</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedIHTLiability }}</p>
      </div>
    </div>

    <!-- IHT Breakdown -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">IHT Calculation Breakdown</h3>
      <div class="space-y-3">
        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Total Estate Value</span>
          <span class="text-sm font-medium text-gray-900">{{ formattedNetWorth }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: Nil Rate Band (NRB)</span>
          <span class="text-sm font-medium text-green-600">-£325,000</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: Residence Nil Rate Band (RNRB)</span>
          <span class="text-sm font-medium text-green-600">-£175,000</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: IHT Exempt Assets</span>
          <span class="text-sm font-medium text-green-600">{{ formattedExemptAssets }}</span>
        </div>
        <div class="flex justify-between items-center py-3 bg-gray-50 rounded">
          <span class="text-base font-semibold text-gray-900">Taxable Estate</span>
          <span class="text-base font-bold text-gray-900">{{ formattedTaxableEstate }}</span>
        </div>
        <div class="flex justify-between items-center py-3 bg-red-50 rounded">
          <span class="text-base font-semibold text-red-800">IHT Liability (40%)</span>
          <span class="text-base font-bold text-red-800">{{ formattedIHTLiability }}</span>
        </div>
      </div>
    </div>

    <!-- Recommendations -->
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-amber-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-amber-800">IHT Mitigation Strategies</h3>
          <div class="mt-2 text-sm text-amber-700">
            <p v-if="ihtLiability > 0">
              Consider gifting strategies, trust planning, and charitable giving to reduce your IHT liability.
            </p>
            <p v-else>
              Your estate is currently below the IHT threshold. Continue to monitor as asset values change.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'IHTPlanning',

  computed: {
    ...mapState('estate', ['analysis']),
    ...mapGetters('estate', ['netWorthValue', 'ihtLiability', 'ihtExemptAssets']),

    taxableEstate() {
      // Simplified calculation
      const nrb = 325000;
      const rnrb = 175000;
      const exemptValue = this.ihtExemptAssets.reduce((sum, asset) => sum + parseFloat(asset.current_value || 0), 0);
      return Math.max(0, this.netWorthValue - nrb - rnrb - exemptValue);
    },

    allowances() {
      return 325000 + 175000;
    },

    formattedNetWorth() {
      return this.formatCurrency(this.netWorthValue);
    },

    formattedTaxableEstate() {
      return this.formatCurrency(this.taxableEstate);
    },

    formattedAllowances() {
      return this.formatCurrency(this.allowances);
    },

    formattedIHTLiability() {
      const liability = this.taxableEstate * 0.4;
      return this.formatCurrency(liability);
    },

    formattedExemptAssets() {
      const exemptValue = this.ihtExemptAssets.reduce((sum, asset) => sum + parseFloat(asset.current_value || 0), 0);
      return '-' + this.formatCurrency(exemptValue);
    },
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },
  },
};
</script>
