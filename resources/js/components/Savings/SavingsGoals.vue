<template>
  <div class="savings-goals">
    <!-- Header with Add Button -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h3 class="text-lg font-semibold text-gray-900">Your Savings Goals</h3>
        <p class="text-sm text-gray-600 mt-1">
          {{ goalsOnTrack.length }} of {{ goals.length }} goals on track
        </p>
      </div>
      <button
        @click="showAddGoalModal = true"
        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          />
        </svg>
        Add Goal
      </button>
    </div>

    <!-- Goals List -->
    <div v-if="goals.length > 0" class="space-y-4">
      <div
        v-for="goal in goals"
        :key="goal.id"
        class="bg-white rounded-lg border border-gray-200 p-6"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <h4 class="text-lg font-semibold text-gray-900">{{ goal.goal_name }}</h4>
            <p class="text-sm text-gray-600">Target: {{ formatDate(goal.target_date) }}</p>
          </div>
          <span
            class="px-3 py-1 text-xs font-semibold rounded-full"
            :class="getStatusBadge(goal)"
          >
            {{ getStatusLabel(goal) }}
          </span>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
          <div class="flex justify-between text-sm mb-1">
            <span class="text-gray-600">Progress</span>
            <span class="font-semibold">{{ getProgressPercent(goal) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3">
            <div
              class="h-3 rounded-full transition-all"
              :class="getProgressBarColor(goal)"
              :style="{ width: getProgressPercent(goal) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Amount Info -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <p class="text-sm text-gray-600">Saved</p>
            <p class="text-lg font-bold text-gray-900">
              {{ formatCurrency(goal.current_saved) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Target</p>
            <p class="text-lg font-bold text-gray-900">
              {{ formatCurrency(goal.target_amount) }}
            </p>
          </div>
        </div>

        <!-- Required Monthly Savings -->
        <div class="p-3 bg-blue-50 rounded-lg mb-4">
          <p class="text-sm text-gray-700">
            <span class="font-medium">Required monthly savings:</span>
            {{ formatCurrency(getRequiredMonthlySavings(goal)) }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
          >
            Update Progress
          </button>
          <button
            class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200"
          >
            Edit
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-white rounded-lg border border-gray-200">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No savings goals yet</h3>
      <p class="mt-1 text-sm text-gray-500">
        Get started by creating your first savings goal.
      </p>
      <button
        @click="showAddGoalModal = true"
        class="mt-4 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
      >
        Create Goal
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
  name: 'SavingsGoals',

  data() {
    return {
      showAddGoalModal: false,
    };
  },

  computed: {
    ...mapState('savings', ['goals']),
    ...mapGetters('savings', ['goalsOnTrack']),
  },

  methods: {
    getProgressPercent(goal) {
      return Math.min(Math.round((goal.current_saved / goal.target_amount) * 100), 100);
    },

    getStatusLabel(goal) {
      const progress = this.getProgressPercent(goal);
      const now = new Date();
      const targetDate = new Date(goal.target_date);
      const monthsRemaining = (targetDate - now) / (1000 * 60 * 60 * 24 * 30);

      if (progress >= 100) return 'Completed';
      if (monthsRemaining < 0) return 'Overdue';

      const required = this.getRequiredMonthlySavings(goal);
      if (required <= 0) return 'On Track';
      if (required > 1000) return 'Off Track';
      return 'On Track';
    },

    getStatusBadge(goal) {
      const status = this.getStatusLabel(goal);
      if (status === 'Completed') return 'bg-green-100 text-green-800';
      if (status === 'On Track') return 'bg-blue-100 text-blue-800';
      if (status === 'Off Track') return 'bg-red-100 text-red-800';
      if (status === 'Overdue') return 'bg-red-100 text-red-800';
      return 'bg-gray-100 text-gray-800';
    },

    getProgressBarColor(goal) {
      const status = this.getStatusLabel(goal);
      if (status === 'Completed') return 'bg-green-600';
      if (status === 'On Track') return 'bg-blue-600';
      return 'bg-red-600';
    },

    getRequiredMonthlySavings(goal) {
      const remaining = goal.target_amount - goal.current_saved;
      const now = new Date();
      const targetDate = new Date(goal.target_date);
      const monthsRemaining = Math.max((targetDate - now) / (1000 * 60 * 60 * 24 * 30), 1);

      return Math.max(0, remaining / monthsRemaining);
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'long',
      });
    },
  },
};
</script>
