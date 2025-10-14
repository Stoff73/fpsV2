<template>
  <div class="goals">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Investment Goals</h2>
        <p class="text-gray-600">Set goals and run Monte Carlo simulations to assess probability of success</p>
      </div>
      <button
        @click="openAddModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
      >
        + Add Goal
      </button>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-red-800">{{ error }}</p>
      </div>
    </div>

    <!-- Success Alert -->
    <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <svg class="h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-sm text-green-800">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Goals Grid -->
    <div v-if="goals && goals.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <GoalCard
        v-for="goal in goals"
        :key="goal.id"
        :goal="goal"
        :current-value="totalPortfolioValue"
        :monte-carlo-result="getMonteCarloResult(goal.id)"
        :running-monte-carlo="runningMonteCarlo === goal.id"
        @edit="openEditModal"
        @delete="confirmDelete"
        @run-monte-carlo="runMonteCarlo"
        @view-chart="viewMonteCarloChart"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white border border-gray-200 rounded-lg p-12 text-center">
      <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Investment Goals Yet</h3>
      <p class="text-gray-500 mb-4">Create your first goal to start tracking your investment progress</p>
      <button
        @click="openAddModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
      >
        + Create First Goal
      </button>
    </div>

    <!-- Goal Form Modal -->
    <GoalForm
      :show="showModal"
      :goal="selectedGoal"
      @submit="handleSubmit"
      @close="closeModal"
    />

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showDeleteModal = false">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-6 py-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg font-medium text-gray-900">Delete Goal</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete <strong>{{ goalToDelete?.name }}</strong>? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="deleteGoal"
              :disabled="deleting"
              class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Monte Carlo Results Modal -->
    <MonteCarloResults
      :show="showMonteCarloModal"
      :results="selectedMonteCarloResult"
      :goal-name="selectedGoalForChart?.name"
      :target-amount="selectedGoalForChart?.target_amount"
      :loading="monteCarloLoading"
      :error="monteCarloError"
      @close="closeMonteCarloModal"
    />
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import GoalCard from './GoalCard.vue';
import GoalForm from './GoalForm.vue';
import MonteCarloResults from './MonteCarloResults.vue';
import { pollMonteCarloJob } from '@/utils/poller';
import investmentService from '@/services/investmentService';

export default {
  name: 'Goals',

  components: {
    GoalCard,
    GoalForm,
    MonteCarloResults,
  },

  data() {
    return {
      showModal: false,
      selectedGoal: null,
      showDeleteModal: false,
      goalToDelete: null,
      error: null,
      successMessage: null,
      deleting: false,
      runningMonteCarlo: null,
      showMonteCarloModal: false,
      selectedMonteCarloResult: null,
      selectedGoalForChart: null,
      monteCarloLoading: false,
      monteCarloError: null,
    };
  },

  computed: {
    ...mapGetters('investment', [
      'goals',
      'totalPortfolioValue',
    ]),

    monteCarloResults() {
      return this.$store.state.investment.monteCarloResults || {};
    },
  },

  methods: {
    ...mapActions('investment', [
      'createGoal',
      'updateGoal',
      'deleteGoalAction',
      'fetchInvestmentData',
    ]),

    openAddModal() {
      this.selectedGoal = null;
      this.showModal = true;
      this.clearMessages();
    },

    openEditModal(goal) {
      this.selectedGoal = goal;
      this.showModal = true;
      this.clearMessages();
    },

    closeModal() {
      this.showModal = false;
      this.selectedGoal = null;
    },

    async handleSubmit(formData) {
      this.clearMessages();

      try {
        if (formData.id) {
          // Update existing goal
          await this.updateGoal({ id: formData.id, data: formData });
          this.successMessage = 'Goal updated successfully';
        } else {
          // Create new goal
          await this.createGoal(formData);
          this.successMessage = 'Goal created successfully';
        }

        // Refresh data
        await this.fetchInvestmentData();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error saving goal:', error);
        this.error = error.response?.data?.message || 'Failed to save goal. Please try again.';
      }
    },

    confirmDelete(goal) {
      this.goalToDelete = goal;
      this.showDeleteModal = true;
      this.clearMessages();
    },

    async deleteGoal() {
      if (!this.goalToDelete) return;

      this.deleting = true;
      this.clearMessages();

      try {
        await this.deleteGoalAction(this.goalToDelete.id);
        this.successMessage = `${this.goalToDelete.name} deleted successfully`;
        this.showDeleteModal = false;
        this.goalToDelete = null;

        // Refresh data
        await this.fetchInvestmentData();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error deleting goal:', error);
        this.error = error.response?.data?.message || 'Failed to delete goal. Please try again.';
        this.showDeleteModal = false;
      } finally {
        this.deleting = false;
      }
    },

    async runMonteCarlo(goal) {
      this.runningMonteCarlo = goal.id;
      this.clearMessages();

      try {
        // Start Monte Carlo simulation
        const response = await investmentService.startMonteCarlo({
          goal_id: goal.id,
          initial_value: goal.initial_value || this.totalPortfolioValue,
          monthly_contribution: goal.monthly_contribution || 0,
          time_horizon_years: this.calculateYears(goal.target_date),
          expected_return: goal.expected_return || 7.0,
          volatility: this.getVolatilityForRisk(goal.risk_level),
          iterations: 1000,
        });

        const jobId = response.job_id;

        // Poll for results
        const results = await pollMonteCarloJob(
          () => investmentService.getMonteCarloResults(jobId),
          {
            onProgress: (attempt) => {
              console.log(`Polling attempt ${attempt} for job ${jobId}`);
            },
          }
        );

        // Store results in Vuex
        this.$store.commit('investment/SET_MONTE_CARLO_RESULT', {
          goalId: goal.id,
          result: results.results,
        });

        this.successMessage = 'Monte Carlo simulation completed successfully';

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error running Monte Carlo:', error);
        this.error = error.message || 'Failed to run Monte Carlo simulation. Please try again.';
      } finally {
        this.runningMonteCarlo = null;
      }
    },

    viewMonteCarloChart(goal) {
      const result = this.getMonteCarloResult(goal.id);
      if (!result) {
        this.error = 'No Monte Carlo results available for this goal';
        return;
      }

      this.selectedMonteCarloResult = result;
      this.selectedGoalForChart = goal;
      this.showMonteCarloModal = true;
    },

    closeMonteCarloModal() {
      this.showMonteCarloModal = false;
      this.selectedMonteCarloResult = null;
      this.selectedGoalForChart = null;
      this.monteCarloError = null;
    },

    getMonteCarloResult(goalId) {
      return this.monteCarloResults[goalId] || null;
    },

    calculateYears(targetDate) {
      const target = new Date(targetDate);
      const now = new Date();
      const diffTime = target - now;
      const diffYears = diffTime / (1000 * 60 * 60 * 24 * 365);
      return Math.max(1, Math.round(diffYears));
    },

    getVolatilityForRisk(riskLevel) {
      const volatilityMap = {
        conservative: 8,
        moderate: 12,
        balanced: 15,
        growth: 18,
        aggressive: 22,
      };
      return volatilityMap[riskLevel] || 15;
    },

    clearMessages() {
      this.error = null;
      this.successMessage = null;
    },
  },
};
</script>
