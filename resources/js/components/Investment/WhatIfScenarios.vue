<template>
  <div class="what-if-scenarios">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">What-If Scenarios</h2>
    <p class="text-gray-600 mb-6">Explore different market and contribution scenarios to see potential outcomes</p>

    <!-- Scenario Builder -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Scenario Builder</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Current Inputs -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Monthly Contribution</label>
            <div class="flex items-center">
              <span class="text-gray-500 mr-2">£</span>
              <input
                v-model.number="currentContribution"
                type="number"
                step="50"
                min="0"
                class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Current Portfolio Value</label>
            <div class="flex items-center">
              <span class="text-gray-500 mr-2">£</span>
              <input
                v-model.number="currentValue"
                type="number"
                step="1000"
                min="0"
                class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Time Horizon (years)</label>
            <input
              v-model.number="timeHorizon"
              type="number"
              min="1"
              max="40"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>

        <!-- Scenario Selection -->
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Market Scenario</label>
            <select
              v-model="selectedMarketScenario"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="optimistic">Optimistic (9% annual return)</option>
              <option value="base">Base Case (7% annual return)</option>
              <option value="conservative">Conservative (5% annual return)</option>
              <option value="crash">Market Crash (-20% then recover)</option>
              <option value="stagnation">Stagnation (3% annual return)</option>
              <option value="high_inflation">High Inflation (10% return, 6% inflation)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contribution Change</label>
            <select
              v-model="contributionChange"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="0">No Change</option>
              <option value="50">Increase by £50/month</option>
              <option value="100">Increase by £100/month</option>
              <option value="200">Increase by £200/month</option>
              <option value="500">Increase by £500/month</option>
              <option value="-50">Decrease by £50/month</option>
              <option value="-100">Decrease by £100/month</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fee Scenario</label>
            <select
              v-model="feeScenario"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="current">Current Fees</option>
              <option value="low">Low Cost (0.2% platform + 0.1% fund)</option>
              <option value="medium">Medium Cost (0.45% platform + 0.5% fund)</option>
              <option value="high">High Cost (0.75% platform + 1.0% fund)</option>
            </select>
          </div>
        </div>
      </div>

      <div class="mt-6 flex justify-end">
        <button
          @click="runScenario"
          :disabled="loading"
          class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ loading ? 'Running...' : 'Run Scenario' }}
        </button>
      </div>
    </div>

    <!-- Results -->
    <div v-if="scenarioResult" class="space-y-6">
      <!-- Comparison Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h4 class="text-sm font-medium text-gray-600 mb-2">Scenario Outcome</h4>
          <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(scenarioResult.final_value) }}</p>
          <p class="text-sm text-gray-500 mt-1">After {{ timeHorizon }} years</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h4 class="text-sm font-medium text-gray-600 mb-2">vs. Base Case</h4>
          <p class="text-3xl font-bold" :class="differenceClass">
            {{ formatCurrency(Math.abs(scenarioResult.difference_from_base)) }}
          </p>
          <p class="text-sm" :class="differenceClass">
            {{ scenarioResult.difference_from_base >= 0 ? '+' : '-' }}{{ Math.abs(scenarioResult.percent_difference).toFixed(1) }}%
          </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h4 class="text-sm font-medium text-gray-600 mb-2">Total Contributions</h4>
          <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(scenarioResult.total_contributions) }}</p>
          <p class="text-sm text-gray-500 mt-1">Over {{ timeHorizon }} years</p>
        </div>
      </div>

      <!-- Scenario Details -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Scenario Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Market Scenario:</span>
              <span class="font-medium text-gray-900">{{ formatScenarioName(selectedMarketScenario) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Annual Return:</span>
              <span class="font-medium text-gray-900">{{ scenarioResult.annual_return }}%</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Monthly Contribution:</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(currentContribution + contributionChange) }}</span>
            </div>
          </div>
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Total Fees:</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(scenarioResult.total_fees) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Investment Growth:</span>
              <span class="font-medium text-gray-900">{{ formatCurrency(scenarioResult.total_growth) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Effective Return:</span>
              <span class="font-medium text-gray-900">{{ scenarioResult.effective_return?.toFixed(2) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Interpretation -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-semibold text-blue-900 mb-2">Analysis</h4>
        <p class="text-sm text-blue-800">{{ scenarioResult.interpretation }}</p>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
      </svg>
      <p class="text-gray-600">Configure a scenario above and click "Run Scenario" to see results</p>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import investmentService from '@/services/investmentService';

export default {
  name: 'WhatIfScenarios',

  data() {
    return {
      currentContribution: 500,
      currentValue: 0,
      timeHorizon: 10,
      selectedMarketScenario: 'base',
      contributionChange: 0,
      feeScenario: 'current',
      loading: false,
      scenarioResult: null,
    };
  },

  computed: {
    ...mapGetters('investment', ['totalPortfolioValue']),

    differenceClass() {
      if (!this.scenarioResult) return '';
      return this.scenarioResult.difference_from_base >= 0 ? 'text-green-600' : 'text-red-600';
    },
  },

  mounted() {
    this.currentValue = this.totalPortfolioValue;
  },

  methods: {
    async runScenario() {
      this.loading = true;

      try {
        const response = await investmentService.runScenario({
          current_value: this.currentValue,
          monthly_contribution: this.currentContribution + this.contributionChange,
          time_horizon_years: this.timeHorizon,
          market_scenario: this.selectedMarketScenario,
          fee_scenario: this.feeScenario,
        });

        this.scenarioResult = response.scenario;
      } catch (error) {
        console.error('Error running scenario:', error);
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
      }).format(value || 0);
    },

    formatScenarioName(scenario) {
      const names = {
        optimistic: 'Optimistic',
        base: 'Base Case',
        conservative: 'Conservative',
        crash: 'Market Crash',
        stagnation: 'Stagnation',
        high_inflation: 'High Inflation',
      };
      return names[scenario] || scenario;
    },
  },
};
</script>
