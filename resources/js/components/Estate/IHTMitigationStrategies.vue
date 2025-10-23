<template>
  <div class="bg-white rounded-lg border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
      IHT Mitigation Strategies
      <span class="text-sm font-normal text-gray-500">(Prioritized by Effectiveness)</span>
    </h3>

    <!-- No IHT liability message -->
    <div v-if="ihtLiability === 0" class="bg-green-50 border-l-4 border-green-500 p-4">
      <p class="text-sm text-green-700">
        ✓ No IHT liability projected - no mitigation strategies needed
      </p>
    </div>

    <!-- Strategies accordion -->
    <div v-else class="space-y-3">
      <div
        v-for="(strategy, index) in strategies"
        :key="index"
        class="border rounded-lg overflow-hidden"
        :class="getStrategyBorderClass(strategy.priority)"
      >
        <!-- Strategy Header (clickable) -->
        <div
          class="p-4 cursor-pointer hover:bg-gray-50 transition"
          @click="toggleStrategy(index)"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-1">
                <!-- Priority Badge -->
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                  :class="getPriorityBadgeClass(strategy.priority)"
                >
                  Priority {{ strategy.priority }}
                </span>

                <!-- Effectiveness Badge -->
                <span
                  v-if="strategy.effectiveness"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                >
                  {{ strategy.effectiveness }} Effectiveness
                </span>
              </div>

              <h4 class="text-base font-semibold text-gray-900">
                {{ strategy.strategy_name }}
              </h4>

              <p class="text-sm text-gray-600 mt-1">
                {{ strategy.description }}
              </p>

              <div class="mt-2 flex items-center space-x-4 text-sm">
                <div v-if="strategy.iht_saved" class="text-green-600 font-medium">
                  IHT Saved: {{ formatCurrency(strategy.iht_saved) }}
                </div>
                <div v-if="strategy.implementation_complexity" class="text-gray-500">
                  Complexity: {{ strategy.implementation_complexity }}
                </div>
              </div>
            </div>

            <!-- Expand icon -->
            <svg
              class="h-5 w-5 text-gray-400 transition-transform ml-4 flex-shrink-0"
              :class="{ 'transform rotate-180': expandedStrategies[index] }"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>

        <!-- Strategy Details (expandable) -->
        <div v-show="expandedStrategies[index]" class="px-4 pb-4 bg-gray-50">
          <!-- Specific Actions for gifting strategy (array of objects) -->
          <div v-if="strategy.specific_actions && Array.isArray(strategy.specific_actions) && typeof strategy.specific_actions[0] === 'object'" class="space-y-3">
            <h5 class="text-sm font-medium text-gray-900 mb-2">Implementation Plan:</h5>

            <div
              v-for="(action, actionIndex) in strategy.specific_actions"
              :key="actionIndex"
              class="bg-white rounded p-3 border border-gray-200"
            >
              <div class="flex justify-between items-start mb-2">
                <h6 class="text-sm font-semibold text-gray-900">{{ action.action }}</h6>
                <span v-if="action.iht_saved" class="text-sm font-medium text-green-600">
                  Saves {{ formatCurrency(action.iht_saved) }}
                </span>
              </div>

              <p v-if="action.amount" class="text-xs text-gray-600 mb-2">
                Total to gift: {{ formatCurrency(action.amount) }}
              </p>

              <!-- Steps -->
              <ul v-if="action.steps" class="space-y-1">
                <li
                  v-for="(step, stepIndex) in action.steps"
                  :key="stepIndex"
                  class="text-xs text-gray-700 flex items-start"
                >
                  <span class="text-blue-600 mr-2">→</span>
                  <span>{{ step }}</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Specific Actions for other strategies (simple list) -->
          <div v-else-if="strategy.specific_actions && Array.isArray(strategy.specific_actions)" class="mt-3">
            <h5 class="text-sm font-medium text-gray-900 mb-2">Implementation Steps:</h5>
            <ul class="space-y-2">
              <li
                v-for="(step, stepIndex) in strategy.specific_actions"
                :key="stepIndex"
                class="text-sm text-gray-700 flex items-start"
              >
                <span class="text-blue-600 mr-2 mt-1">→</span>
                <span>{{ step }}</span>
              </li>
            </ul>
          </div>

          <!-- Cover needed (for life insurance strategy) -->
          <div v-if="strategy.cover_needed" class="mt-3 bg-blue-50 rounded p-3">
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <p class="text-blue-600 font-medium">Cover Required</p>
                <p class="text-lg font-bold text-blue-900">{{ formatCurrency(strategy.cover_needed) }}</p>
              </div>
              <div>
                <p class="text-blue-600 font-medium">Annual Premium</p>
                <p class="text-lg font-bold text-blue-900">{{ formatCurrency(strategy.estimated_annual_premium) }}</p>
              </div>
            </div>
          </div>

          <!-- Charitable giving details -->
          <div v-if="strategy.charitable_amount_required" class="mt-3 bg-purple-50 rounded p-3">
            <p class="text-sm text-purple-700">
              <strong>Required charitable bequest:</strong> {{ formatCurrency(strategy.charitable_amount_required) }} (10% of estate)
            </p>
            <p class="text-xs text-purple-600 mt-1">
              This reduces IHT rate from 40% to 36%, saving {{ formatCurrency(strategy.iht_saved) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Total potential savings -->
    <div v-if="ihtLiability > 0 && strategies.length > 0" class="mt-6 pt-6 border-t border-gray-200">
      <div class="bg-green-50 rounded-lg p-4">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-sm text-green-600 font-medium">Total Potential IHT Savings</p>
            <p class="text-xs text-green-500 mt-1">By implementing all recommended strategies</p>
          </div>
          <p class="text-2xl font-bold text-green-900">
            {{ formatCurrency(totalSavings) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IHTMitigationStrategies',

  props: {
    strategies: {
      type: Array,
      required: true,
    },
    ihtLiability: {
      type: Number,
      required: true,
    },
  },

  data() {
    return {
      expandedStrategies: {},
    };
  },

  computed: {
    totalSavings() {
      return this.strategies.reduce((sum, strategy) => sum + (strategy.iht_saved || 0), 0);
    },
  },

  methods: {
    toggleStrategy(index) {
      this.$set(this.expandedStrategies, index, !this.expandedStrategies[index]);
    },

    getStrategyBorderClass(priority) {
      const classes = {
        1: 'border-green-300',
        2: 'border-blue-300',
        3: 'border-amber-300',
        4: 'border-gray-300',
      };
      return classes[priority] || 'border-gray-300';
    },

    getPriorityBadgeClass(priority) {
      const classes = {
        1: 'bg-green-100 text-green-800',
        2: 'bg-blue-100 text-blue-800',
        3: 'bg-amber-100 text-amber-800',
        4: 'bg-gray-100 text-gray-800',
      };
      return classes[priority] || 'bg-gray-100 text-gray-800';
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value || 0);
    },
  },
};
</script>
