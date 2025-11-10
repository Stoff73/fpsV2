<template>
  <div class="what-if-scenarios-tab">
    <!-- Header -->
    <div class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">What-If Scenarios</h3>
      <p class="text-sm text-gray-600">
        Model different scenarios to understand their impact on your IHT liability
      </p>
    </div>

    <!-- Scenario Selector -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
      <h4 class="text-md font-semibold text-gray-900 mb-4">Select Scenario Type</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button
          @click="selectScenario('property_change')"
          :class="[
            'p-4 border-2 rounded-lg text-left transition-colors',
            selectedScenario === 'property_change'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-blue-300',
          ]"
        >
          <h5 class="text-sm font-semibold text-gray-900 mb-1">Property Value Change</h5>
          <p class="text-xs text-gray-600">Model impact of property market changes on IHT</p>
        </button>

        <button
          @click="selectScenario('gifting')"
          :class="[
            'p-4 border-2 rounded-lg text-left transition-colors',
            selectedScenario === 'gifting'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-blue-300',
          ]"
        >
          <h5 class="text-sm font-semibold text-gray-900 mb-1">Gifting Strategy</h5>
          <p class="text-xs text-gray-600">See how regular gifting reduces IHT liability</p>
        </button>

        <button
          @click="selectScenario('charitable')"
          :class="[
            'p-4 border-2 rounded-lg text-left transition-colors',
            selectedScenario === 'charitable'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-blue-300',
          ]"
        >
          <h5 class="text-sm font-semibold text-gray-900 mb-1">Charitable Giving</h5>
          <p class="text-xs text-gray-600">Model 10%+ charity gift to reduce IHT rate to 36%</p>
        </button>

        <button
          @click="selectScenario('spouse_death')"
          :class="[
            'p-4 border-2 rounded-lg text-left transition-colors',
            selectedScenario === 'spouse_death'
              ? 'border-blue-500 bg-blue-50'
              : 'border-gray-200 hover:border-blue-300',
          ]"
        >
          <h5 class="text-sm font-semibold text-gray-900 mb-1">First Death Planning</h5>
          <p class="text-xs text-gray-600">Plan for spouse death and allowance transfer</p>
        </button>
      </div>
    </div>

    <!-- Scenario Configuration -->
    <div v-if="selectedScenario" class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
      <h4 class="text-md font-semibold text-gray-900 mb-4">Configure Scenario</h4>

      <!-- Property Value Change Scenario -->
      <div v-if="selectedScenario === 'property_change'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Property Value Change (%)
          </label>
          <input
            v-model.number="scenarioParams.property_change_percent"
            type="number"
            step="5"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="e.g., -20 for 20% decrease, 30 for 30% increase"
          />
        </div>
      </div>

      <!-- Gifting Strategy Scenario -->
      <div v-if="selectedScenario === 'gifting'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Annual Gift Amount (£)
          </label>
          <input
            v-model.number="scenarioParams.annual_gift_amount"
            type="number"
            step="1000"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="e.g., 10000"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Years of Gifting
          </label>
          <input
            v-model.number="scenarioParams.gifting_years"
            type="number"
            min="1"
            max="20"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="e.g., 7"
          />
        </div>
      </div>

      <!-- Charitable Giving Scenario -->
      <div v-if="selectedScenario === 'charitable'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Charitable Bequest Amount (£)
          </label>
          <input
            v-model.number="scenarioParams.charitable_amount"
            type="number"
            step="1000"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="Must be 10%+ of net estate to reduce IHT to 36%"
          />
        </div>
      </div>

      <!-- Spouse Death Scenario -->
      <div v-if="selectedScenario === 'spouse_death'" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Transfer Full NRB/RNRB to Spouse?
          </label>
          <select
            v-model="scenarioParams.transfer_allowances"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
          >
            <option :value="true">Yes - Transfer full allowances</option>
            <option :value="false">No - Use allowances on first death</option>
          </select>
        </div>
      </div>

      <button
        @click="runScenario"
        :disabled="loading"
        class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400"
      >
        <span v-if="loading">Running Scenario...</span>
        <span v-else>Run Scenario</span>
      </button>
    </div>

    <!-- Scenario Results -->
    <div v-if="scenarioResults" class="bg-white rounded-lg border border-gray-200 p-6">
      <h4 class="text-md font-semibold text-gray-900 mb-4">Scenario Results</h4>

      <!-- Comparison Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Metric
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Current
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Scenario
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Difference
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                Estate Value
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(scenarioResults.current.estate_value) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(scenarioResults.scenario.estate_value) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getDifferenceColour(scenarioResults.scenario.estate_value - scenarioResults.current.estate_value)">
                {{ formatDifference(scenarioResults.scenario.estate_value - scenarioResults.current.estate_value) }}
              </td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                IHT Liability
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(scenarioResults.current.iht_liability) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(scenarioResults.scenario.iht_liability) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold" :class="getIHTDifferenceColour(scenarioResults.scenario.iht_liability - scenarioResults.current.iht_liability)">
                {{ formatDifference(scenarioResults.scenario.iht_liability - scenarioResults.current.iht_liability) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Insights -->
      <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg
              class="h-5 w-5 text-blue-400"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Scenario Insights</h3>
            <div class="mt-2 text-sm text-blue-700">
              <p>{{ scenarioResults.insight }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'WhatIfScenarios',

  data() {
    return {
      selectedScenario: null,
      scenarioParams: {
        property_change_percent: 0,
        annual_gift_amount: 0,
        gifting_years: 7,
        charitable_amount: 0,
        transfer_allowances: true,
      },
      scenarioResults: null,
      loading: false,
    };
  },

  computed: {
    ...mapState('estate', ['assets', 'liabilities']),
    ...mapGetters('estate', ['netWorthValue', 'ihtLiability']),
  },

  methods: {
    selectScenario(type) {
      this.selectedScenario = type;
      this.scenarioResults = null;
    },

    async runScenario() {
      this.loading = true;
      try {
        // Simulate API call - in production, this would call the backend
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Mock results
        const currentIHT = this.calculateSimpleIHT(this.netWorthValue);
        let scenarioValue = this.netWorthValue;

        if (this.selectedScenario === 'property_change') {
          const propertyAssets = this.assets.filter(a => a.asset_type === 'Property');
          const propertyValue = propertyAssets.reduce((sum, a) => sum + parseFloat(a.current_value || 0), 0);
          const change = propertyValue * (this.scenarioParams.property_change_percent / 100);
          scenarioValue = this.netWorthValue + change;
        } else if (this.selectedScenario === 'gifting') {
          const totalGifts = this.scenarioParams.annual_gift_amount * this.scenarioParams.gifting_years;
          scenarioValue = this.netWorthValue - totalGifts;
        } else if (this.selectedScenario === 'charitable') {
          scenarioValue = this.netWorthValue - this.scenarioParams.charitable_amount;
        }

        const scenarioIHT = this.calculateSimpleIHT(scenarioValue);
        const ihtSaving = currentIHT - scenarioIHT;

        this.scenarioResults = {
          current: {
            estate_value: this.netWorthValue,
            iht_liability: currentIHT,
          },
          scenario: {
            estate_value: scenarioValue,
            iht_liability: scenarioIHT,
          },
          insight: ihtSaving > 0
            ? `This scenario could save you ${this.formatCurrency(ihtSaving)} in IHT liability.`
            : 'This scenario does not significantly impact your IHT position.',
        };
      } catch (error) {
        console.error('Failed to run scenario:', error);
      } finally {
        this.loading = false;
      }
    },

    calculateSimpleIHT(estateValue) {
      const nrb = 325000;
      const rnrb = 175000;
      const allowances = nrb + rnrb;
      const taxableEstate = Math.max(0, estateValue - allowances);
      return taxableEstate * 0.4;
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDifference(value) {
      const formatted = this.formatCurrency(Math.abs(value));
      return value >= 0 ? `+${formatted}` : `-${formatted}`;
    },

    getDifferenceColour(value) {
      return value >= 0 ? 'text-green-600' : 'text-red-600';
    },

    getIHTDifferenceColour(value) {
      // For IHT, reduction is good (green), increase is bad (red)
      return value <= 0 ? 'text-green-600' : 'text-red-600';
    },
  },
};
</script>
