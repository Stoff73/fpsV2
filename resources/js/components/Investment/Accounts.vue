<template>
  <div class="accounts">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Investment Accounts</h2>
        <p class="text-gray-600">Manage your investment accounts and view holdings</p>
      </div>
      <button
        @click="openAddModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2"
      >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Account
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

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Accounts Grid -->
    <div v-else-if="accounts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <AccountCard
        v-for="account in accounts"
        :key="account.id"
        :account="account"
        @edit="openEditModal"
        @delete="confirmDelete"
        @view-holdings="viewHoldings"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12 bg-white border border-gray-200 rounded-lg">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No accounts yet</h3>
      <p class="text-gray-600 mb-4">Get started by adding your first investment account</p>
      <button
        @click="openAddModal"
        class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
      >
        Add Your First Account
      </button>
    </div>

    <!-- Account Form Modal -->
    <AccountForm
      :show="showModal"
      :account="selectedAccount"
      @save="handleSubmit"
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
                <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to delete <strong>{{ accountToDelete?.provider }}</strong>?
                  </p>
                  <p v-if="accountToDelete?.holdings?.length > 0" class="text-sm text-red-600 mt-2">
                    Warning: This account has {{ accountToDelete.holdings.length }} holding(s). All holdings will be deleted as well.
                  </p>
                  <p class="text-sm text-gray-500 mt-2">
                    This action cannot be undone.
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
              @click="deleteAccount"
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
import AccountCard from './AccountCard.vue';
import AccountForm from './AccountForm.vue';

export default {
  name: 'Accounts',

  components: {
    AccountCard,
    AccountForm,
  },

  data() {
    return {
      showModal: false,
      selectedAccount: null,
      showDeleteModal: false,
      accountToDelete: null,
      error: null,
      successMessage: null,
      deleting: false,
    };
  },

  computed: {
    ...mapGetters('investment', [
      'accounts',
    ]),

    loading() {
      return this.$store.state.investment.loading;
    },
  },

  methods: {
    ...mapActions('investment', [
      'createAccount',
      'updateAccount',
      'deleteAccount',
      'fetchInvestmentData',
      'analyseInvestment',
    ]),

    openAddModal() {
      this.selectedAccount = null;
      this.showModal = true;
      this.clearMessages();
    },

    openEditModal(account) {
      this.selectedAccount = account;
      this.showModal = true;
      this.clearMessages();
    },

    closeModal() {
      this.showModal = false;
      this.selectedAccount = null;
    },

    async handleSubmit(formData) {
      this.clearMessages();

      try {
        if (formData.id) {
          // Update existing account
          await this.updateAccount({ id: formData.id, accountData: formData });
          this.successMessage = 'Account updated successfully';
        } else {
          // Create new account
          await this.createAccount(formData);
          this.successMessage = 'Account added successfully';
        }

        // Close modal on success
        this.closeModal();

        // Refresh data and analysis
        await this.fetchInvestmentData();
        await this.analyseInvestment();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error saving account:', error);
        this.error = error.response?.data?.message || 'Failed to save account. Please try again.';
      }
    },

    confirmDelete(account) {
      this.accountToDelete = account;
      this.showDeleteModal = true;
      this.clearMessages();
    },

    async deleteAccount() {
      if (!this.accountToDelete) return;

      this.deleting = true;
      this.clearMessages();

      try {
        await this.$store.dispatch('investment/deleteAccount', this.accountToDelete.id);
        this.successMessage = `${this.accountToDelete.provider} deleted successfully`;
        this.showDeleteModal = false;
        this.accountToDelete = null;

        // Refresh data and analysis
        await this.fetchInvestmentData();
        await this.analyseInvestment();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error deleting account:', error);
        this.error = error.response?.data?.message || 'Failed to delete account. Please try again.';
        this.showDeleteModal = false;
      } finally {
        this.deleting = false;
      }
    },

    viewHoldings(account) {
      // Emit event to parent dashboard to switch to holdings tab
      this.$emit('view-holdings', account);
    },

    clearMessages() {
      this.error = null;
      this.successMessage = null;
    },
  },
};
</script>
