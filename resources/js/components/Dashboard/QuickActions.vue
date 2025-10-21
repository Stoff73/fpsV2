<template>
  <div class="quick-actions bg-white rounded-lg shadow-md p-6 relative z-10">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <!-- Add Savings Goal -->
      <button
        @click="navigateTo('/savings')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-blue-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Add Savings Goal</p>
            <p class="text-xs text-gray-600">Set new target</p>
          </div>
        </div>
      </button>

      <!-- Record Gift -->
      <button
        @click="navigateTo('/estate')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-amber-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z"
                clip-rule="evenodd"
              />
              <path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Record Gift</p>
            <p class="text-xs text-gray-600">Track PET/CLT</p>
          </div>
        </div>
      </button>

      <!-- Update Pension -->
      <button
        @click="navigateTo('/retirement')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-purple-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Update Pension</p>
            <p class="text-xs text-gray-600">Adjust contribution</p>
          </div>
        </div>
      </button>

      <!-- Add Investment Holding -->
      <button
        @click="navigateTo('/investment')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-green-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Add Holding</p>
            <p class="text-xs text-gray-600">Update portfolio</p>
          </div>
        </div>
      </button>

      <!-- Check IHT Liability -->
      <button
        @click="navigateTo('/estate')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-red-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Check IHT</p>
            <p class="text-xs text-gray-600">View liability</p>
          </div>
        </div>
      </button>

      <!-- Update Protection -->
      <button
        @click="navigateTo('/protection')"
        class="quick-action-btn group"
      >
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-indigo-600"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="text-left">
            <p class="text-sm font-semibold text-gray-800">Update Protection</p>
            <p class="text-xs text-gray-600">Review coverage</p>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuickActions',

  data() {
    return {
      actions: [
        {
          title: 'Add Savings Goal',
          description: 'Set new target',
          icon: 'plus-circle',
          color: 'blue',
          link: '/savings',
        },
        {
          title: 'Record Gift',
          description: 'Track PET/CLT',
          icon: 'gift',
          color: 'amber',
          link: '/estate',
        },
        {
          title: 'Update Pension Contribution',
          description: 'Adjust contribution',
          icon: 'cash',
          color: 'purple',
          link: '/retirement',
        },
        {
          title: 'Add Investment Holding',
          description: 'Update portfolio',
          icon: 'chart-bar',
          color: 'green',
          link: '/investment',
        },
        {
          title: 'Check IHT Liability',
          description: 'View liability',
          icon: 'document-download',
          color: 'red',
          link: '/estate',
        },
        {
          title: 'Update Protection Coverage',
          description: 'Review coverage',
          icon: 'shield-check',
          color: 'indigo',
          link: '/protection',
        },
      ],
    };
  },

  methods: {
    navigateTo(path) {
      this.$router.push(path);
    },

    handleAction(path) {
      this.navigateTo(path);
    },
  },
};
</script>

<style scoped>
.quick-actions {
  min-width: 280px;
  max-width: 100%;
}

.quick-action-btn {
  @apply w-full p-4 text-left bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-gray-300 transition-all duration-200;
}

@media (min-width: 640px) {
  .quick-actions {
    min-width: 400px;
  }
}

@media (min-width: 1024px) {
  .quick-actions {
    min-width: 600px;
  }
}
</style>
