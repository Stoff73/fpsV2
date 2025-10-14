<template>
  <div class="holdings">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Holdings</h2>
        <p class="text-gray-600">Manage your investment holdings and view detailed performance</p>
      </div>
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

    <!-- Holdings Table -->
    <HoldingsTable
      :holdings="allHoldings"
      :loading="loading"
      @add-holding="openAddModal"
      @edit-holding="openEditModal"
      @delete-holding="confirmDelete"
    />

    <!-- Holding Form Modal -->
    <HoldingForm
      :show="showModal"
      :holding="selectedHolding"
      :accounts="accounts"
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
                <h3 class="text-lg font-medium text-gray-900">Delete Holding</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete <strong>{{ holdingToDelete?.security_name }}</strong>? This action cannot be undone.
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
              @click="deleteHolding"
              :disabled="deleting"
              class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ deleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';
import HoldingsTable from './HoldingsTable.vue';
import HoldingForm from './HoldingForm.vue';

export default {
  name: 'Holdings',

  components: {
    HoldingsTable,
    HoldingForm,
  },

  data() {
    return {
      showModal: false,
      selectedHolding: null,
      showDeleteModal: false,
      holdingToDelete: null,
      error: null,
      successMessage: null,
      deleting: false,
    };
  },

  computed: {
    ...mapGetters('investment', [
      'allHoldings',
      'accounts',
    ]),

    loading() {
      return this.$store.state.investment.loading;
    },
  },

  methods: {
    ...mapActions('investment', [
      'createHolding',
      'updateHolding',
      'deleteHoldingAction',
      'fetchInvestmentData',
      'analyzeInvestment',
    ]),

    openAddModal() {
      this.selectedHolding = null;
      this.showModal = true;
      this.clearMessages();
    },

    openEditModal(holding) {
      this.selectedHolding = holding;
      this.showModal = true;
      this.clearMessages();
    },

    closeModal() {
      this.showModal = false;
      this.selectedHolding = null;
    },

    async handleSubmit(formData) {
      this.clearMessages();

      try {
        if (formData.id) {
          // Update existing holding
          await this.updateHolding({ id: formData.id, data: formData });
          this.successMessage = 'Holding updated successfully';
        } else {
          // Create new holding
          await this.createHolding(formData);
          this.successMessage = 'Holding added successfully';
        }

        // Refresh data and analysis
        await this.fetchInvestmentData();
        await this.analyzeInvestment();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error saving holding:', error);
        this.error = error.response?.data?.message || 'Failed to save holding. Please try again.';
      }
    },

    confirmDelete(holding) {
      this.holdingToDelete = holding;
      this.showDeleteModal = true;
      this.clearMessages();
    },

    async deleteHolding() {
      if (!this.holdingToDelete) return;

      this.deleting = true;
      this.clearMessages();

      try {
        await this.deleteHoldingAction(this.holdingToDelete.id);
        this.successMessage = `${this.holdingToDelete.security_name} deleted successfully`;
        this.showDeleteModal = false;
        this.holdingToDelete = null;

        // Refresh data and analysis
        await this.fetchInvestmentData();
        await this.analyzeInvestment();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error deleting holding:', error);
        this.error = error.response?.data?.message || 'Failed to delete holding. Please try again.';
        this.showDeleteModal = false;
      } finally {
        this.deleting = false;
      }
    },

    clearMessages() {
      this.error = null;
      this.successMessage = null;
    },
  },
};
</script>
