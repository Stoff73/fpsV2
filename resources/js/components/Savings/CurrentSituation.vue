<template>
  <div class="current-situation">
    <!-- Total Savings Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-600 mb-2">Total Savings</h3>
        <p class="text-3xl font-bold text-gray-900">
          {{ formatCurrency(totalSavings) }}
        </p>
      </div>

      <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-600 mb-2">Emergency Fund Runway</h3>
        <p class="text-3xl font-bold" :class="runwayColour">
          {{ emergencyFundRunway.toFixed(1) }} months
        </p>
      </div>

      <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-600 mb-2">Number of Accounts</h3>
        <p class="text-3xl font-bold text-gray-900">
          {{ accounts.length }}
        </p>
      </div>
    </div>

    <!-- ISA Allowance Tracker -->
    <div class="mb-8">
      <ISAAllowanceTracker />
    </div>

    <!-- Account List -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Accounts</h3>
      <div v-if="accounts.length > 0" class="space-y-4">
        <div
          v-for="account in accounts"
          :key="account.id"
          class="flex justify-between items-center p-4 bg-gray-50 rounded-lg"
        >
          <div>
            <h4 class="font-semibold text-gray-900">{{ account.institution }}</h4>
            <p class="text-sm text-gray-600">{{ formatAccountType(account.account_type) }}</p>
            <div class="flex gap-2 mt-1">
              <span v-if="account.is_emergency_fund" class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded">
                Emergency Fund
              </span>
              <span v-if="account.is_isa" class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                ISA
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(account.current_balance) }}</p>
            <p class="text-sm text-gray-600">{{ account.interest_rate }}% APY</p>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        <p>No savings accounts added yet.</p>
        <button
          @click="$router.push({ path: '/savings', query: { tab: 'details' } })"
          class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Add Your First Account
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import ISAAllowanceTracker from './ISAAllowanceTracker.vue';

export default {
  name: 'CurrentSituation',

  components: {
    ISAAllowanceTracker,
  },

  computed: {
    ...mapState('savings', ['accounts']),
    ...mapGetters('savings', ['totalSavings', 'emergencyFundRunway']),

    runwayColour() {
      if (this.emergencyFundRunway >= 6) return 'text-green-600';
      if (this.emergencyFundRunway >= 3) return 'text-amber-600';
      return 'text-red-600';
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

    formatAccountType(type) {
      const types = {
        savings_account: 'Savings Account',
        current_account: 'Current Account',
        easy_access: 'Easy Access',
        notice: 'Notice Account',
        fixed: 'Fixed Term',
      };
      return types[type] || type;
    },
  },
};
</script>
