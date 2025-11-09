<template>
  <div class="surviving-spouse-iht-planning">
    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-start">
        <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="currentColour" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div>
          <h4 class="text-sm font-medium text-blue-900">Surviving Spouse IHT Planning</h4>
          <p class="text-sm text-blue-700 mt-1">
            This tool calculates your estimated IHT liability as a surviving spouse, projecting your estate value to your expected date of death using actuarial life tables and including any transferable NRB from your deceased spouse.
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-start">
        <svg class="h-5 w-5 text-red-400 mt-0.5 mr-3" fill="currentColour" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <p class="text-sm text-red-700">{{ error }}</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-centre py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Calculating surviving spouse IHT projection...</p>
    </div>

    <!-- Main Content -->
    <div v-else-if="analysis && analysis.success">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Current Estate Value -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <h4 class="text-sm font-medium text-gray-500 mb-1">Current Estate Value</h4>
          <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(analysis.current_estate_value) }}</p>
          <p class="text-xs text-gray-500 mt-1">Value as of today</p>
        </div>

        <!-- Projected Estate Value -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <h4 class="text-sm font-medium text-gray-500 mb-1">Projected Estate Value at Death</h4>
          <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(analysis.projected_estate_value) }}</p>
          <p class="text-xs text-gray-500 mt-1">
            In {{ analysis.years_until_death }} years (age {{ analysis.survivor_estimated_age_at_death }})
          </p>
        </div>

        <!-- IHT Liability -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
          <h4 class="text-sm font-medium text-gray-500 mb-1">Estimated IHT Liability</h4>
          <p class="text-2xl font-bold" :class="analysis.iht_calculation.iht_liability > 0 ? 'text-red-600' : 'text-green-600'">
            {{ formatCurrency(analysis.iht_calculation.iht_liability) }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ (analysis.iht_calculation.effective_rate || 0).toFixed(1) }}% effective rate
          </p>
        </div>
      </div>

      <!-- Life Expectancy Card -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Life Expectancy Projection</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <p class="text-sm text-gray-600 mb-2">Current Age</p>
            <p class="text-xl font-bold text-gray-900">{{ analysis.survivor_current_age }} years</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-2">Estimated Age at Death</p>
            <p class="text-xl font-bold text-gray-900">{{ analysis.survivor_estimated_age_at_death }} years</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-2">Years Until Expected Death</p>
            <p class="text-xl font-bold text-blue-600">{{ analysis.years_until_death }} years</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-2">Estimated Date of Death</p>
            <p class="text-xl font-bold text-gray-900">{{ formatDate(analysis.estimated_death_date) }}</p>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-4 italic">
          Based on UK ONS National Life Tables (2020-2022 data)
        </p>
      </div>

      <!-- NRB Transfer Details -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Nil Rate Band (NRB) Transfer</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-centre pb-3 border-b border-gray-200">
            <span class="text-sm text-gray-600">Your Own NRB</span>
            <span class="text-sm font-semibold text-gray-900">
              {{ formatCurrency(analysis.nrb_transfer_details.survivor_own_nrb) }}
            </span>
          </div>
          <div class="flex justify-between items-centre pb-3 border-b border-gray-200">
            <span class="text-sm text-gray-600">
              Transferred from {{ analysis.deceased_spouse_name }}
              <span class="text-xs text-gray-500">({{ analysis.nrb_transfer_details.transferred_percentage.toFixed(0) }}% of spouse's NRB)</span>
            </span>
            <span class="text-sm font-semibold text-green-600">
              +{{ formatCurrency(analysis.nrb_transfer_details.transferred_nrb_from_deceased) }}
            </span>
          </div>
          <div class="flex justify-between items-centre pt-2">
            <span class="text-base font-medium text-gray-900">Total NRB Available</span>
            <span class="text-lg font-bold text-blue-600">
              {{ formatCurrency(analysis.nrb_transfer_details.survivor_total_nrb) }}
            </span>
          </div>
        </div>

        <!-- Show spouse's NRB usage details -->
        <div v-if="analysis.nrb_transfer_details.deceased_nrb_details" class="mt-6 bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-700 mb-3">{{ analysis.deceased_spouse_name }}'s NRB Usage</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Original NRB:</span>
              <span class="font-medium">{{ formatCurrency(analysis.nrb_transfer_details.deceased_nrb_details.spouse_original_nrb) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Used by Gifts:</span>
              <span class="font-medium text-red-600">
                -{{ formatCurrency(analysis.nrb_transfer_details.deceased_nrb_details.spouse_own_nrb_used) }}
              </span>
            </div>
            <div v-if="analysis.nrb_transfer_details.deceased_nrb_details.gift_count > 0" class="text-xs text-gray-500 mt-2">
              {{ analysis.nrb_transfer_details.deceased_nrb_details.gift_count }} gift(s) within 7 years of death
            </div>
          </div>
        </div>
      </div>

      <!-- Asset Growth Projection -->
      <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Asset Growth Projection</h3>
        <p class="text-sm text-gray-600 mb-4">
          Projected growth: <strong class="text-green-600">{{ formatCurrency(analysis.projected_growth) }}</strong>
          over {{ analysis.years_until_death }} years
        </p>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asset</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Current Value</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Growth Rate</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Future Value</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Growth</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(asset, index) in analysis.asset_projections" :key="index">
                <td class="px-4 py-3 text-sm text-gray-900">{{ asset.asset_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  <span class="inline-flex items-centre px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ asset.asset_type }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-right text-gray-900">{{ formatCurrency(asset.current_value) }}</td>
                <td class="px-4 py-3 text-sm text-right text-gray-600">{{ (asset.growth_rate * 100).toFixed(1) }}%</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-blue-600">{{ formatCurrency(asset.future_value) }}</td>
                <td class="px-4 py-3 text-sm text-right text-green-600">+{{ formatCurrency(asset.growth_amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- IHT Calculation Breakdown -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">IHT Calculation Breakdown</h3>
        <div class="space-y-3">
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">Projected Gross Estate</span>
            <span class="text-sm font-semibold">{{ formatCurrency(analysis.iht_calculation.gross_estate_value) }}</span>
          </div>
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">Less: Liabilities</span>
            <span class="text-sm font-semibold text-red-600">-{{ formatCurrency(analysis.iht_calculation.liabilities) }}</span>
          </div>
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm font-medium text-gray-900">Net Estate Value</span>
            <span class="text-sm font-bold">{{ formatCurrency(analysis.iht_calculation.net_estate_value) }}</span>
          </div>
          <div v-if="analysis.iht_calculation.spouse_exemption_applies" class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">Less: Spouse Exemption</span>
            <span class="text-sm font-semibold text-green-600">-{{ formatCurrency(analysis.iht_calculation.spouse_exemption) }}</span>
          </div>
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">Nil Rate Band (NRB)</span>
            <span class="text-sm font-semibold text-green-600">-{{ formatCurrency(analysis.iht_calculation.nrb_available_for_estate) }}</span>
          </div>
          <div v-if="analysis.iht_calculation.rnrb_eligible" class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">Residence NRB (RNRB)</span>
            <span class="text-sm font-semibold text-green-600">-{{ formatCurrency(analysis.iht_calculation.rnrb) }}</span>
          </div>
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm font-medium text-gray-900">Taxable Estate</span>
            <span class="text-sm font-bold">{{ formatCurrency(analysis.iht_calculation.taxable_estate) }}</span>
          </div>
          <div class="flex justify-between items-centre pb-2 border-b border-gray-200">
            <span class="text-sm text-gray-600">IHT Rate</span>
            <span class="text-sm font-semibold">{{ (analysis.iht_calculation.iht_rate * 100).toFixed(0) }}%</span>
          </div>
          <div class="flex justify-between items-centre pt-2">
            <span class="text-base font-bold text-gray-900">Total IHT Liability</span>
            <span class="text-xl font-bold text-red-600">{{ formatCurrency(analysis.iht_calculation.iht_liability) }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-6 flex justify-end space-x-3">
        <button
          @click="recalculate"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Refresh Calculation
        </button>
      </div>
    </div>

    <!-- No Spouse Message -->
    <div v-else-if="!loading && !analysis" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-centre">
      <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColour" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-yellow-800">Surviving Spouse IHT Planning Not Available</h3>
      <p class="mt-1 text-sm text-yellow-600">
        You must be married or widowed with a linked spouse account, and have your date of birth and gender set in your profile to use this feature.
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SurvivingSpouseIHTPlanning',

  data() {
    return {
      loading: false,
      error: null,
      analysis: null,
    };
  },

  mounted() {
    this.calculateSurvivingSpouseIHT();
  },

  methods: {
    async calculateSurvivingSpouseIHT() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/estate/calculate-surviving-spouse-iht');

        if (response.data.success) {
          this.analysis = response.data.data;
        } else {
          this.error = response.data.message || 'Failed to calculate surviving spouse IHT';
          this.analysis = null;
        }
      } catch (err) {
        if (err.response && err.response.data && err.response.data.message) {
          this.error = err.response.data.message;
        } else {
          this.error = 'An error occurred while calculating surviving spouse IHT';
        }
        this.analysis = null;
        console.error('Surviving Spouse IHT Calculation Error:', err);
      } finally {
        this.loading = false;
      }
    },

    recalculate() {
      this.calculateSurvivingSpouseIHT();
    },

    formatCurrency(value) {
      if (value === null || value === undefined) return '£0.00';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value);
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return new Intl.DateTimeFormat('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      }).format(date);
    },
  },
};
</script>

<style scoped>
.surviving-spouse-iht-planning {
  /* Component-specific styles */
}
</style>
