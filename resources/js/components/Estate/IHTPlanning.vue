<template>
  <div class="iht-planning-tab">
    <!-- Error State - No Profile -->
    <div v-if="error && !ihtData" class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-amber-800">IHT Profile Required</h3>
          <p class="mt-2 text-sm text-amber-700">{{ error }}</p>
          <p class="mt-2 text-sm text-amber-700">Please set up your IHT profile in the Estate module to see your IHT calculation.</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Calculating IHT liability...</p>
    </div>

    <!-- Spouse Exemption Notice -->
    <div v-if="ihtData?.spouse_exemption_applies && ihtData?.spouse_exemption > 0" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-green-800">Spouse Exemption Applied</h3>
          <p class="mt-2 text-sm text-green-700">
            <strong>{{ formatCurrency(ihtData.spouse_exemption) }}</strong> ({{ formatPercent((ihtData.spouse_exemption / ihtData.net_estate_value)) }}) of your estate is exempt from IHT due to unlimited spousal transfer on death.
            <span v-if="ihtData.death_scenario === 'user_only'">This calculation assumes only you pass away. Change to "Both Dying" scenario in the Will tab for simultaneous death planning.</span>
          </p>
        </div>
      </div>
    </div>

    <!-- IHT Summary -->
    <div v-else-if="ihtData" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-purple-50 rounded-lg p-6">
        <p class="text-sm text-purple-600 font-medium mb-2">Gross Estate</p>
        <p class="text-xs text-purple-500 mb-1">Net of liabilities</p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(ihtData?.net_estate_value || 0) }}</p>
      </div>
      <div class="bg-green-50 rounded-lg p-6">
        <p class="text-sm text-green-600 font-medium mb-2">{{ ihtData?.spouse_exemption_applies ? 'Spouse Exemption' : 'Total Allowances' }}</p>
        <p class="text-xs text-green-500 mb-1">{{ ihtData?.spouse_exemption_applies ? 'Unlimited transfer' : 'NRB + RNRB' }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(ihtData?.spouse_exemption_applies ? ihtData.spouse_exemption : ihtData?.total_allowance || 0) }}</p>
      </div>
      <div class="bg-red-50 rounded-lg p-6">
        <p class="text-sm text-red-600 font-medium mb-2">Total IHT Liability</p>
        <p class="text-xs text-red-500 mb-1">{{ ihtData?.death_scenario === 'both_simultaneous' ? 'Both dying' : 'If death occurs now' }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedIHTLiability }}</p>
      </div>
    </div>

    <!-- IHT Breakdown -->
    <div v-if="!loading" class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">IHT Calculation Breakdown (Death Now Scenario)</h3>

      <!-- Estate Calculation -->
      <div class="space-y-3 mb-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Estate Calculation</h4>

        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Total Estate Value</span>
          <span class="text-sm font-medium text-gray-900">{{ formatCurrency(ihtData?.gross_estate_value || 0) }}</span>
        </div>

        <div v-if="ihtData?.liabilities > 0" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: Liabilities</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.liabilities || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-gray-200 bg-purple-50">
          <span class="text-sm font-semibold text-purple-800">Gross Estate</span>
          <span class="text-sm font-bold text-purple-800">{{ formatCurrency(ihtData?.net_estate_value || 0) }}</span>
        </div>

        <div v-if="ihtData?.spouse_exemption_applies && ihtData?.spouse_exemption > 0" class="flex justify-between items-center py-2 border-b border-gray-200 bg-green-50">
          <span class="text-sm font-medium text-green-800">Less: Spouse Exemption ({{ formatPercent((ihtData.spouse_exemption / ihtData.net_estate_value)) }})</span>
          <span class="text-sm font-semibold text-green-800">-{{ formatCurrency(ihtData.spouse_exemption) }}</span>
        </div>

        <div v-if="ihtData?.spouse_exemption_applies && ihtData?.spouse_exemption > 0" class="flex justify-between items-center py-2 border-b border-gray-200 bg-blue-50">
          <span class="text-sm font-semibold text-blue-800">Estate Subject to IHT</span>
          <span class="text-sm font-bold text-blue-800">{{ formatCurrency(ihtData?.taxable_net_estate || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: NRB{{ ihtData?.nrb_from_spouse > 0 ? ' (inc. transferred £' + formatNumber(ihtData.nrb_from_spouse) + ')' : '' }}</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.nrb_available_for_estate || 325000) }}</span>
        </div>

        <div v-if="ihtData?.rnrb_eligible" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: RNRB</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.rnrb || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-3 bg-gray-50 rounded">
          <span class="text-base font-semibold text-gray-900">Taxable Estate</span>
          <span class="text-base font-bold text-gray-900">{{ formatCurrency(ihtData?.taxable_estate || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-3 bg-red-50 rounded">
          <span class="text-base font-semibold text-red-800">IHT Liability ({{ formatPercent(ihtData?.iht_rate || 0.4) }})</span>
          <span class="text-base font-bold text-red-800">{{ formatCurrency(ihtData?.estate_iht_liability || 0) }}</span>
        </div>
      </div>

      <!-- Gift Calculation -->
      <div v-if="hasGifts" class="space-y-3 pt-6 border-t border-gray-300">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Gift Liability (7-Year Rule with Taper Relief)</h4>

        <!-- PET Gifts -->
        <div v-if="hasPETGifts" class="mb-4">
          <p class="text-xs font-medium text-gray-600 mb-2">Potentially Exempt Transfers (PETs)</p>
          <div v-for="gift in petGifts" :key="gift.gift_id" class="mb-3 pl-4 py-2 border-l-2 border-amber-400 bg-amber-50">
            <div class="flex justify-between items-center mb-1">
              <div class="flex-1">
                <span class="text-sm font-medium text-gray-800">{{ gift.recipient }}</span>
                <span class="ml-2 text-xs text-gray-500">({{ formatDate(gift.gift_date) }})</span>
              </div>
              <span class="text-sm font-bold text-amber-800">{{ formatCurrency(gift.tax_liability) }}</span>
            </div>
            <div class="text-xs text-gray-600 space-y-1 mt-2">
              <div class="flex justify-between">
                <span>Gift value:</span>
                <span class="font-medium">{{ formatCurrency(gift.gift_value) }}</span>
              </div>
              <div class="flex justify-between text-green-700">
                <span>Less: NRB covered</span>
                <span class="font-medium">-{{ formatCurrency(gift.nrb_covered) }}</span>
              </div>
              <div class="flex justify-between border-t border-amber-200 pt-1">
                <span>Taxable amount:</span>
                <span class="font-medium">{{ formatCurrency(gift.taxable_amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Taper relief rate ({{ gift.years_ago }} years):</span>
                <span class="font-medium">{{ formatPercent(gift.taper_rate) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CLT Gifts -->
        <div v-if="hasCLTGifts" class="mb-4">
          <p class="text-xs font-medium text-gray-600 mb-2">Chargeable Lifetime Transfers (CLTs)</p>
          <div v-for="clt in cltGifts" :key="clt.gift_id" class="flex justify-between items-center py-2 pl-4 border-l-2 border-blue-400">
            <div class="flex-1">
              <span class="text-sm text-gray-700">{{ clt.recipient }} ({{ formatDate(clt.gift_date) }})</span>
              <span class="ml-2 text-xs text-gray-500">{{ clt.years_ago }} years ago</span>
            </div>
            <span class="text-sm font-medium text-blue-700">{{ formatCurrency(clt.tax_liability) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center py-3 bg-amber-50 rounded">
          <span class="text-base font-semibold text-amber-800">Total Gift IHT Liability</span>
          <span class="text-base font-bold text-amber-800">{{ formatCurrency(ihtData?.gift_iht_liability || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- Recommendations -->
    <div v-if="ihtData?.iht_liability > 0" class="bg-red-50 border-l-4 border-red-500 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
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
          <h3 class="text-sm font-medium text-red-800">IHT Mitigation Strategies</h3>
          <div class="mt-2 text-sm text-red-700">
            <p class="font-semibold mb-2">
              Your estate has a potential IHT liability of {{ formatCurrency(ihtData?.iht_liability || 0) }}. Consider these strategies:
            </p>
            <ul class="list-disc list-inside space-y-1">
              <li>Regular gifting using PET and annual exemptions (£3,000/year)</li>
              <li>Charitable giving (can reduce IHT rate from 40% to 36% if ≥10% to charity)</li>
              <li>Trust planning to remove assets from your estate</li>
              <li>Life insurance policies written in trust to cover IHT liability</li>
              <li v-if="!ihtData?.rnrb || ihtData.rnrb === 0">Consider leaving your main residence to direct descendants to claim RNRB (up to £175,000)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="bg-green-50 border-l-4 border-green-500 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-green-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-green-800">No IHT Liability</h3>
          <div class="mt-2 text-sm text-green-700">
            <p class="mb-2">
              Good news! Your estate is currently below the IHT threshold with {{ formatCurrency(ihtData?.total_allowance || 500000) }} in allowances available.
            </p>
            <p>
              Continue to monitor your estate value as asset prices change. Review your IHT position annually or after significant life events.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Trust Planning Summary -->
    <div v-if="ihtData?.trust_details && ihtData.trust_details.length > 0" class="bg-white shadow rounded-lg p-6 mt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Trust Planning Summary</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-purple-50 rounded-lg p-4">
          <p class="text-xs text-purple-600 font-medium">Total Trust Value</p>
          <p class="text-2xl font-bold text-purple-900 mt-1">{{ formatCurrency(totalTrustValue) }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
          <p class="text-xs text-green-600 font-medium">Value Outside Estate</p>
          <p class="text-2xl font-bold text-green-900 mt-1">{{ formatCurrency(trustValueOutsideEstate) }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4">
          <p class="text-xs text-blue-600 font-medium">IHT Efficiency</p>
          <p class="text-2xl font-bold text-blue-900 mt-1">{{ trustEfficiencyPercent }}%</p>
        </div>
      </div>

      <div class="space-y-3">
        <div v-for="trust in ihtData.trust_details" :key="trust.trust_id" class="border border-gray-200 rounded-lg p-3">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h4 class="text-sm font-semibold text-gray-900">{{ trust.trust_name }}</h4>
              <p class="text-xs text-gray-500">{{ getTrustTypeName(trust.trust_type) }}</p>
            </div>
            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(trust.current_value) }}</span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-500">Value in Estate:</span>
              <span class="font-medium ml-1" :class="trust.iht_value > 0 ? 'text-red-600' : 'text-green-600'">
                {{ formatCurrency(trust.iht_value) }}
              </span>
            </div>
            <div>
              <span class="text-gray-500">Outside Estate:</span>
              <span class="font-medium text-green-600 ml-1">{{ formatCurrency(trust.current_value - trust.iht_value) }}</span>
            </div>
          </div>

          <div v-if="trust.iht_value > 0" class="mt-2 pt-2 border-t border-gray-200">
            <p class="text-xs text-amber-700">
              <strong>Note:</strong> {{ getTrustIHTExplanation(trust.trust_type) }}
            </p>
          </div>
          <div v-else class="mt-2 pt-2 border-t border-gray-200">
            <p class="text-xs text-green-700">
              ✓ This trust's value is completely outside your estate for IHT purposes
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
  name: 'IHTPlanning',

  data() {
    return {
      ihtData: null,
      loading: false,
      error: null,
    };
  },

  computed: {
    ...mapState('estate', ['analysis', 'gifts']),
    ...mapGetters('estate', ['netWorthValue', 'ihtLiability', 'ihtExemptAssets']),

    formattedIHTLiability() {
      return this.formatCurrency(this.ihtData?.iht_liability || 0);
    },

    hasGifts() {
      return this.ihtData?.gifting_details &&
             (this.ihtData.gifting_details.pet_liability?.gift_count > 0 ||
              this.ihtData.gifting_details.clt_liability?.clt_count > 0);
    },

    hasTrusts() {
      return this.ihtData?.trust_details && this.ihtData.trust_details.length > 0;
    },

    activeTrustDetails() {
      if (!this.ihtData?.trust_details) return [];
      return this.ihtData.trust_details.filter(t => t.iht_value > 0);
    },

    totalTrustValue() {
      if (!this.ihtData?.trust_details) return 0;
      return this.ihtData.trust_details.reduce((sum, t) => {
        const value = parseFloat(t.current_value) || 0;
        return sum + value;
      }, 0);
    },

    trustValueOutsideEstate() {
      if (!this.ihtData?.trust_details) return 0;
      return this.ihtData.trust_details.reduce((sum, t) => {
        const currentValue = parseFloat(t.current_value) || 0;
        const ihtValue = parseFloat(t.iht_value) || 0;
        return sum + (currentValue - ihtValue);
      }, 0);
    },

    trustEfficiencyPercent() {
      const total = this.totalTrustValue;
      if (!total || total === 0) return 0;
      const outsideEstate = this.trustValueOutsideEstate;
      return Math.round((outsideEstate / total) * 100);
    },

    hasPETGifts() {
      return this.ihtData?.gifting_details?.pet_liability?.gift_count > 0;
    },

    hasCLTGifts() {
      return this.ihtData?.gifting_details?.clt_liability?.clt_count > 0;
    },

    petGifts() {
      return this.ihtData?.gifting_details?.pet_liability?.gifts || [];
    },

    cltGifts() {
      return this.ihtData?.gifting_details?.clt_liability?.clts || [];
    },
  },

  mounted() {
    this.loadIHTCalculation();
  },

  watch: {
    '$route'() {
      // Reload when navigating back to this tab
      this.loadIHTCalculation();
    },
  },

  methods: {
    ...mapActions('estate', ['calculateIHT']),

    async loadIHTCalculation() {
      this.loading = true;
      this.error = null;
      try {
        const response = await this.calculateIHT();
        this.ihtData = response.data;
      } catch (error) {
        console.error('Failed to load IHT calculation:', error);
        this.error = error.message || 'Failed to calculate IHT liability';
      } finally {
        this.loading = false;
      }
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatNumber(value) {
      return new Intl.NumberFormat('en-GB', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatPercent(value) {
      return `${(value * 100).toFixed(0)}%`;
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      });
    },

    getTaperReliefRate(yearsAgo) {
      if (yearsAgo < 3) return 40;
      if (yearsAgo < 4) return 32;
      if (yearsAgo < 5) return 24;
      if (yearsAgo < 6) return 16;
      if (yearsAgo < 7) return 8;
      return 0;
    },

    getTrustTypeName(type) {
      const names = {
        bare: 'Bare Trust',
        interest_in_possession: 'Interest in Possession',
        discretionary: 'Discretionary',
        accumulation_maintenance: 'A&M Trust',
        life_insurance: 'Life Insurance',
        discounted_gift: 'Discounted Gift',
        loan: 'Loan Trust',
        mixed: 'Mixed',
        settlor_interested: 'Settlor-Interested',
      };
      return names[type] || type;
    },

    getTrustIHTExplanation(type) {
      const explanations = {
        discounted_gift: 'For a Discounted Gift Trust, the retained income value (discount) counts in your estate.',
        loan: 'For a Loan Trust, the outstanding loan balance counts in your estate. Growth is outside.',
        interest_in_possession: 'For an Interest in Possession Trust, the full value counts in the life tenant\'s estate.',
        settlor_interested: 'For a Settlor-Interested Trust, the full value remains in your estate (reservation of benefit).',
      };
      return explanations[type] || 'This trust type has specific IHT treatment rules.';
    },
  },
};
</script>
