<template>
  <div class="assets-liabilities-tab">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-green-50 rounded-lg p-6">
        <p class="text-sm text-green-600 font-medium mb-2">Total Assets</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedTotalAssets }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ assets.length }} items</p>
      </div>
      <div class="bg-red-50 rounded-lg p-6">
        <p class="text-sm text-red-600 font-medium mb-2">Total Liabilities</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedTotalLiabilities }}</p>
        <p class="text-sm text-gray-600 mt-1">{{ liabilities.length }} items</p>
      </div>
      <div class="bg-blue-50 rounded-lg p-6">
        <p class="text-sm text-blue-600 font-medium mb-2">Net Worth</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedNetWorth }}</p>
      </div>
    </div>

    <!-- Assets Section -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Assets</h3>
        <button
          @click="showAssetForm = true"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
        >
          <svg
            class="-ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd"
            />
          </svg>
          Add Asset
        </button>
      </div>
      <div v-if="assets.length === 0" class="px-6 py-8 text-center text-gray-500">
        No assets recorded yet
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Asset Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Current Value
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                IHT Status
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="asset in assets" :key="asset.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ asset.asset_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ asset.asset_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(asset.current_value) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    asset.is_iht_exempt ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ asset.is_iht_exempt ? 'Exempt' : 'Taxable' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button
                  @click="editAsset(asset)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Edit
                </button>
                <button
                  @click="deleteAssetConfirm(asset.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Liabilities Section -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Liabilities</h3>
        <button
          @click="showLiabilityForm = true"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
        >
          <svg
            class="-ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd"
            />
          </svg>
          Add Liability
        </button>
      </div>
      <div v-if="liabilities.length === 0" class="px-6 py-8 text-center text-gray-500">
        No liabilities recorded yet
      </div>
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Liability Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Balance
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Monthly Payment
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="liability in liabilities" :key="liability.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ liability.liability_type }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ liability.liability_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(liability.current_balance) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatCurrency(liability.monthly_payment) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <button
                  @click="editLiability(liability)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  Edit
                </button>
                <button
                  @click="deleteLiabilityConfirm(liability.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals (placeholders) -->
    <div v-if="showAssetForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Asset</h3>
        <p class="text-sm text-gray-600 mb-4">Asset form will be implemented here</p>
        <button
          @click="showAssetForm = false"
          class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
        >
          Close
        </button>
      </div>
    </div>

    <div v-if="showLiabilityForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Liability</h3>
        <p class="text-sm text-gray-600 mb-4">Liability form will be implemented here</p>
        <button
          @click="showLiabilityForm = false"
          class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
  name: 'AssetsLiabilities',

  data() {
    return {
      showAssetForm: false,
      showLiabilityForm: false,
    };
  },

  computed: {
    ...mapState('estate', ['assets', 'liabilities']),
    ...mapGetters('estate', ['totalAssets', 'totalLiabilities', 'netWorthValue']),

    formattedTotalAssets() {
      return this.formatCurrency(this.totalAssets);
    },

    formattedTotalLiabilities() {
      return this.formatCurrency(this.totalLiabilities);
    },

    formattedNetWorth() {
      return this.formatCurrency(this.netWorthValue);
    },
  },

  methods: {
    ...mapActions('estate', ['deleteAsset', 'deleteLiability']),

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    editAsset(asset) {
      // Placeholder
      console.log('Edit asset:', asset);
    },

    async deleteAssetConfirm(id) {
      if (confirm('Are you sure you want to delete this asset?')) {
        try {
          await this.deleteAsset(id);
        } catch (error) {
          console.error('Failed to delete asset:', error);
        }
      }
    },

    editLiability(liability) {
      // Placeholder
      console.log('Edit liability:', liability);
    },

    async deleteLiabilityConfirm(id) {
      if (confirm('Are you sure you want to delete this liability?')) {
        try {
          await this.deleteLiability(id);
        } catch (error) {
          console.error('Failed to delete liability:', error);
        }
      }
    },
  },
};
</script>
