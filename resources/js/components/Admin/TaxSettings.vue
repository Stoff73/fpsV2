<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">Tax Settings</h2>
        <p class="text-sm text-gray-600 mt-1">
          Manage UK tax configurations and view calculation formulas
        </p>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="error"
      class="rounded-md bg-red-50 border border-red-200 p-4"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Content -->
    <div v-else-if="!error" class="space-y-6">
      <!-- Current Active Configuration -->
      <div class="card bg-blue-50 border-blue-200">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-blue-900">Active Tax Configuration</h3>
            <div v-if="currentConfig" class="mt-2 text-sm text-blue-800">
              <p><strong>Tax Year:</strong> {{ currentConfig.tax_year }}</p>
              <p><strong>Effective From:</strong> {{ formatDate(currentConfig.effective_from) }}</p>
              <p><strong>Effective To:</strong> {{ formatDate(currentConfig.effective_to) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8 overflow-x-auto">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="space-y-6">
        <!-- Current Rates Tab -->
        <div v-if="activeTab === 'current-rates' && currentConfig">
          <!-- Income Tax -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Income Tax ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-700">Personal Allowance:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.income_tax.personal_allowance) }}</span>
                </div>
                <div class="mt-4">
                  <h4 class="font-medium text-gray-900 mb-2">Tax Bands:</h4>
                  <div class="space-y-2">
                    <div v-for="(band, index) in currentConfig.income_tax.bands" :key="index" class="flex justify-between bg-gray-50 p-3 rounded">
                      <div>
                        <span class="font-medium text-gray-900">{{ band.name }}</span>
                        <span class="text-sm text-gray-600 ml-2">
                          ({{ formatCurrency(band.lower_limit) }} - {{ formatCurrency(band.upper_limit) }})
                        </span>
                      </div>
                      <span class="font-semibold text-primary-600">{{ band.rate }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- National Insurance -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">National Insurance ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Class 1 (Employee) -->
                <div>
                  <h4 class="font-medium text-gray-900 mb-3">Class 1 (Employee)</h4>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-gray-700">Primary Threshold:</span>
                      <span class="font-medium">£{{ formatNumber(currentConfig.national_insurance.class_1.employee.primary_threshold) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-700">Upper Earnings Limit:</span>
                      <span class="font-medium">£{{ formatNumber(currentConfig.national_insurance.class_1.employee.upper_earnings_limit) }}</span>
                    </div>
                    <div class="flex justify-between bg-gray-50 p-2 rounded">
                      <span class="text-gray-700">Main Rate:</span>
                      <span class="font-semibold text-primary-600">{{ (currentConfig.national_insurance.class_1.employee.main_rate * 100).toFixed(2) }}%</span>
                    </div>
                    <div class="flex justify-between bg-gray-50 p-2 rounded">
                      <span class="text-gray-700">Additional Rate:</span>
                      <span class="font-semibold text-primary-600">{{ (currentConfig.national_insurance.class_1.employee.additional_rate * 100).toFixed(2) }}%</span>
                    </div>
                    <div class="flex justify-between mt-4 pt-2 border-t">
                      <span class="text-gray-700 font-medium">Employer Rate:</span>
                      <span class="font-semibold text-gray-900">{{ (currentConfig.national_insurance.class_1.employer.rate * 100).toFixed(2) }}%</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-700">Employer Threshold:</span>
                      <span class="font-medium">£{{ formatNumber(currentConfig.national_insurance.class_1.employer.secondary_threshold) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Class 4 (Self-Employed) -->
                <div>
                  <h4 class="font-medium text-gray-900 mb-3">Class 4 (Self-Employed)</h4>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-gray-700">Lower Profits Limit:</span>
                      <span class="font-medium">£{{ formatNumber(currentConfig.national_insurance.class_4.lower_profits_limit) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-700">Upper Profits Limit:</span>
                      <span class="font-medium">£{{ formatNumber(currentConfig.national_insurance.class_4.upper_profits_limit) }}</span>
                    </div>
                    <div class="flex justify-between bg-gray-50 p-2 rounded">
                      <span class="text-gray-700">Main Rate:</span>
                      <span class="font-semibold text-primary-600">{{ (currentConfig.national_insurance.class_4.main_rate * 100).toFixed(2) }}%</span>
                    </div>
                    <div class="flex justify-between bg-gray-50 p-2 rounded">
                      <span class="text-gray-700">Additional Rate:</span>
                      <span class="font-semibold text-primary-600">{{ (currentConfig.national_insurance.class_4.additional_rate * 100).toFixed(2) }}%</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inheritance Tax -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Inheritance Tax ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                  <span class="text-gray-700">Nil Rate Band (NRB):</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.inheritance_tax.nil_rate_band) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Residence NRB:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.inheritance_tax.residence_nil_rate_band) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">RNRB Taper Threshold:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.inheritance_tax.rnrb_taper_threshold) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Standard Rate:</span>
                  <span class="font-semibold text-primary-600">{{ (currentConfig.inheritance_tax.standard_rate * 100).toFixed(2) }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Reduced Rate (Charity):</span>
                  <span class="font-semibold text-primary-600">{{ (currentConfig.inheritance_tax.reduced_rate_charity * 100).toFixed(2) }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Charity Threshold:</span>
                  <span class="font-medium">10%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Capital Gains Tax -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Capital Gains Tax ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                  <span class="text-gray-700">Annual Exempt Amount:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.capital_gains_tax.annual_exempt_amount) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Basic Rate:</span>
                  <span class="font-semibold text-primary-600">{{ currentConfig.capital_gains_tax.basic_rate }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Higher Rate:</span>
                  <span class="font-semibold text-primary-600">{{ currentConfig.capital_gains_tax.higher_rate }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Property Basic Rate:</span>
                  <span class="font-semibold text-primary-600">{{ currentConfig.capital_gains_tax.residential_property_basic_rate }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Property Higher Rate:</span>
                  <span class="font-semibold text-primary-600">{{ currentConfig.capital_gains_tax.residential_property_higher_rate }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pensions -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Pension Allowances ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                  <span class="text-gray-700">Annual Allowance:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.pension.annual_allowance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">MPAA:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.pension.money_purchase_annual_allowance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Lifetime Allowance:</span>
                  <span class="font-medium">{{ currentConfig.pension.lifetime_allowance_abolished ? 'Abolished' : '£' + formatNumber(currentConfig.pension.lifetime_allowance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Taper Threshold Income:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.pension.tapered_annual_allowance.threshold_income) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Taper Adjusted Income:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.pension.tapered_annual_allowance.adjusted_income) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">Minimum Tapered AA:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.pension.tapered_annual_allowance.minimum_allowance) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ISA -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">ISA Allowances ({{ currentConfig.tax_year }})</h3>
            </div>
            <div class="px-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between">
                  <span class="text-gray-700">Annual Allowance:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.isa.annual_allowance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">LISA Allowance:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.isa.lifetime_isa.annual_allowance) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">LISA Bonus Rate:</span>
                  <span class="font-semibold text-primary-600">{{ (currentConfig.isa.lifetime_isa.government_bonus_rate * 100).toFixed(2) }}%</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-700">JISA Allowance:</span>
                  <span class="font-medium">£{{ formatNumber(currentConfig.isa.junior_isa.annual_allowance) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Calculations Tab -->
        <div v-if="activeTab === 'calculations'">
          <!-- Income Tax Calculation -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Income Tax Calculation</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Formula:</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.income_tax?.formula }}
                  </code>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Bands:</h4>
                  <ul class="space-y-1 text-sm text-gray-700">
                    <li v-for="(band, index) in calculations.income_tax?.bands" :key="index">
                      • {{ band.name }}: {{ band.range }} at {{ band.rate }}
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Example:</h4>
                  <p class="text-sm text-gray-700">{{ calculations.income_tax?.example }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- National Insurance Calculation -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">National Insurance Calculation</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-4">
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Class 1 (Employee):</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.national_insurance?.class1_formula }}
                  </code>
                  <p class="text-sm text-gray-700 mt-2">{{ calculations.national_insurance?.class1_example }}</p>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Class 4 (Self-Employed):</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.national_insurance?.class4_formula }}
                  </code>
                  <p class="text-sm text-gray-700 mt-2">{{ calculations.national_insurance?.class4_example }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Inheritance Tax Calculation -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Inheritance Tax Calculation</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Formula:</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.inheritance_tax?.formula }}
                  </code>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Available Reliefs:</h4>
                  <ul class="space-y-1 text-sm text-gray-700">
                    <li v-for="(relief, index) in calculations.inheritance_tax?.reliefs" :key="index">
                      • {{ relief }}
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Example:</h4>
                  <p class="text-sm text-gray-700">{{ calculations.inheritance_tax?.example }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Capital Gains Tax Calculation -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Capital Gains Tax Calculation</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Formula:</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.capital_gains_tax?.formula }}
                  </code>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Rates:</h4>
                  <ul class="space-y-1 text-sm text-gray-700">
                    <li v-for="(rate, index) in calculations.capital_gains_tax?.rates" :key="index">
                      • {{ rate }}
                    </li>
                  </ul>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Example:</h4>
                  <p class="text-sm text-gray-700">{{ calculations.capital_gains_tax?.example }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Pension Tax Relief Calculation -->
          <div class="card">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Pension Tax Relief Calculation</h3>
            </div>
            <div class="px-6 py-4">
              <div class="space-y-3">
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Formula:</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.pension_tax_relief?.formula }}
                  </code>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Annual Allowance Taper:</h4>
                  <code class="block bg-gray-50 p-3 rounded text-sm">
                    {{ calculations.pension_tax_relief?.taper_formula }}
                  </code>
                </div>
                <div>
                  <h4 class="font-medium text-gray-900 mb-2">Example:</h4>
                  <p class="text-sm text-gray-700">{{ calculations.pension_tax_relief?.example }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import taxSettingsService from '../../services/taxSettingsService';

export default {
  name: 'TaxSettings',

  data() {
    return {
      loading: true,
      error: null,
      currentConfig: null,
      calculations: {},
      activeTab: 'current-rates',
      tabs: [
        { id: 'current-rates', label: 'Current Rates' },
        { id: 'calculations', label: 'Calculation Formulas' },
      ],
    };
  },

  mounted() {
    this.loadData();
  },

  methods: {
    async loadData() {
      this.loading = true;
      this.error = null;

      try {
        const [configResponse, calculationsResponse] = await Promise.all([
          taxSettingsService.getCurrent(),
          taxSettingsService.getCalculations(),
        ]);

        if (configResponse.data.success) {
          this.currentConfig = configResponse.data.data;
        } else {
          this.error = configResponse.data.message || 'Failed to load tax configuration';
          return;
        }

        if (calculationsResponse.data.success) {
          this.calculations = calculationsResponse.data.data;
        } else {
          this.error = calculationsResponse.data.message || 'Failed to load tax calculations';
          return;
        }
      } catch (error) {
        console.error('Failed to load tax settings:', error);
        this.error = error.response?.data?.message || error.message || 'Failed to load tax settings. Please check your connection and try again.';
      } finally {
        this.loading = false;
      }
    },

    formatNumber(value) {
      if (!value && value !== 0) return 'N/A';
      return value.toLocaleString('en-GB');
    },

    formatCurrency(value) {
      if (!value && value !== 0) return 'N/A';
      if (value === 'unlimited' || value === 'Unlimited') return 'Unlimited';
      return '£' + value.toLocaleString('en-GB');
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
    },
  },
};
</script>
