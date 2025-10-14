<template>
  <div class="emergency-fund">
    <!-- Emergency Fund Gauge -->
    <div class="mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">
          Emergency Fund Status
        </h3>
        <EmergencyFundGauge
          :runway-months="emergencyFundRunway"
          :target-months="targetMonths"
        />
        <p class="text-center text-sm text-gray-600 mt-4">
          {{ statusMessage }}
        </p>
      </div>
    </div>

    <!-- Monthly Expenditure & Target -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Monthly Expenditure Breakdown -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Expenditure</h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Housing</span>
            <span class="font-semibold">{{ formatCurrency(expenditure.housing) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Food & Groceries</span>
            <span class="font-semibold">{{ formatCurrency(expenditure.food) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Utilities & Bills</span>
            <span class="font-semibold">{{ formatCurrency(expenditure.utilities) }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Other Essentials</span>
            <span class="font-semibold">{{ formatCurrency(expenditure.other) }}</span>
          </div>
          <div class="pt-3 border-t flex justify-between items-center">
            <span class="font-semibold text-gray-900">Total Monthly</span>
            <span class="text-lg font-bold text-gray-900">{{ formatCurrency(monthlyTotal) }}</span>
          </div>
        </div>
      </div>

      <!-- Target vs Actual -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Target vs Actual</h3>
        <div class="space-y-4">
          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600">Target Fund</span>
              <span class="text-sm font-semibold">{{ formatCurrency(targetAmount) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="h-2 rounded-full bg-blue-600"
                style="width: 100%"
              ></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between mb-1">
              <span class="text-sm text-gray-600">Current Fund</span>
              <span class="text-sm font-semibold">{{ formatCurrency(currentAmount) }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all"
                :class="currentAmountBarColor"
                :style="{ width: currentAmountPercentage + '%' }"
              ></div>
            </div>
          </div>

          <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm font-medium text-blue-900">
              <span v-if="shortfall > 0">
                Top up needed: {{ formatCurrency(shortfall) }}
              </span>
              <span v-else>
                Emergency fund target achieved!
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Adjust Target -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Adjust Target</h3>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Target Months of Expenses
        </label>
        <input
          v-model.number="targetMonths"
          type="range"
          min="3"
          max="12"
          step="1"
          class="w-full"
        />
        <div class="flex justify-between text-sm text-gray-600 mt-1">
          <span>3 months</span>
          <span class="font-semibold text-gray-900">{{ targetMonths }} months</span>
          <span>12 months</span>
        </div>
      </div>
      <div class="p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-700">
          With {{ targetMonths }} months of expenses, your target emergency fund would be
          <span class="font-semibold">{{ formatCurrency(targetAmount) }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import EmergencyFundGauge from './EmergencyFundGauge.vue';

export default {
  name: 'EmergencyFund',

  components: {
    EmergencyFundGauge,
  },

  data() {
    return {
      targetMonths: 6,
    };
  },

  computed: {
    ...mapState('savings', ['expenditureProfile', 'analysis', 'accounts']),
    ...mapGetters('savings', ['emergencyFundRunway', 'monthlyExpenditure', 'totalSavings']),

    expenditure() {
      if (!this.expenditureProfile) {
        return {
          housing: 0,
          food: 0,
          utilities: 0,
          other: 0,
        };
      }

      return {
        housing: this.expenditureProfile.housing || 0,
        food: this.expenditureProfile.food_groceries || 0,
        utilities: this.expenditureProfile.utilities_bills || 0,
        other: this.expenditureProfile.other_essentials || 0,
      };
    },

    monthlyTotal() {
      return Object.values(this.expenditure).reduce((sum, val) => sum + val, 0);
    },

    targetAmount() {
      return this.monthlyTotal * this.targetMonths;
    },

    currentAmount() {
      return this.totalSavings;
    },

    shortfall() {
      return Math.max(0, this.targetAmount - this.currentAmount);
    },

    currentAmountPercentage() {
      if (this.targetAmount === 0) return 0;
      return Math.min((this.currentAmount / this.targetAmount) * 100, 100);
    },

    currentAmountBarColor() {
      if (this.currentAmountPercentage >= 100) return 'bg-green-600';
      if (this.currentAmountPercentage >= 50) return 'bg-amber-600';
      return 'bg-red-600';
    },

    statusMessage() {
      if (this.emergencyFundRunway >= 6) {
        return 'Excellent! Your emergency fund exceeds the recommended 6-month target.';
      } else if (this.emergencyFundRunway >= 3) {
        return 'Good progress. Consider building up to 6 months of expenses.';
      } else {
        return 'Priority: Build your emergency fund to at least 3-6 months of expenses.';
      }
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

<style scoped>
/* Range slider styling */
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  height: 6px;
  border-radius: 3px;
  background: #E5E7EB;
  outline: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #3B82F6;
  cursor: pointer;
}

input[type="range"]::-moz-range-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #3B82F6;
  cursor: pointer;
  border: none;
}
</style>
