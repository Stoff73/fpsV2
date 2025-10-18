<template>
  <div>
    <div v-if="data" class="space-y-6">
      <!-- As Of Date -->
      <div class="card p-4 bg-gray-50">
        <p class="text-body-sm text-gray-600">
          As of: {{ formatDate(data.as_of_date) }}
        </p>
      </div>

      <!-- Assets Section -->
      <div class="card p-6">
        <h3 class="text-h5 font-semibold text-success-700 mb-4">Assets</h3>
        <div class="space-y-2">
          <div
            v-for="(item, index) in data.assets"
            :key="index"
            class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0"
          >
            <span class="text-body-base text-gray-700">{{ item.line_item }}</span>
            <span class="text-body-base font-medium text-gray-900">
              {{ formatCurrency(item.amount) }}
            </span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t-2 border-gray-300 flex justify-between items-center">
          <span class="text-body-base font-semibold text-gray-900">Total Assets</span>
          <span class="text-h5 font-bold text-success-700">
            {{ formatCurrency(data.total_assets) }}
          </span>
        </div>
      </div>

      <!-- Liabilities Section -->
      <div class="card p-6">
        <h3 class="text-h5 font-semibold text-error-700 mb-4">Liabilities</h3>
        <div class="space-y-2">
          <div
            v-for="(item, index) in data.liabilities"
            :key="index"
            class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0"
          >
            <span class="text-body-base text-gray-700">{{ item.line_item }}</span>
            <span class="text-body-base font-medium text-gray-900">
              {{ formatCurrency(item.amount) }}
            </span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t-2 border-gray-300 flex justify-between items-center">
          <span class="text-body-base font-semibold text-gray-900">Total Liabilities</span>
          <span class="text-h5 font-bold text-error-700">
            {{ formatCurrency(data.total_liabilities) }}
          </span>
        </div>
      </div>

      <!-- Equity (Net Worth) -->
      <div class="card p-6 bg-gradient-to-r from-primary-50 to-primary-100">
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-h5 font-semibold text-gray-900">Equity (Net Worth)</h3>
            <p class="text-body-sm text-gray-600 mt-1">Total Assets minus Total Liabilities</p>
          </div>
          <div>
            <p
              class="text-h2 font-display font-bold"
              :class="data.equity >= 0 ? 'text-success-700' : 'text-error-700'"
            >
              {{ formatCurrency(data.equity) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card p-8 text-center">
      <p class="text-body-base text-gray-500">
        No data available. Click "Calculate" to generate your Balance Sheet.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BalanceSheetView',

  props: {
    data: {
      type: Object,
      default: null,
    },
  },

  setup() {
    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount || 0);
    };

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    };

    return {
      formatCurrency,
      formatDate,
    };
  },
};
</script>
