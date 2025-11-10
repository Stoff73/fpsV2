<template>
  <AppLayout>
    <div class="container mx-auto px-4 py-8">
    <!-- Breadcrumbs -->
    <nav class="text-sm mb-6">
      <ol class="flex items-center space-x-2">
        <li><router-link to="/dashboard" class="text-blue-600 hover:underline">Dashboard</router-link></li>
        <li><span class="text-gray-400">/</span></li>
        <li><router-link to="/net-worth" class="text-blue-600 hover:underline">Net Worth</router-link></li>
        <li><span class="text-gray-400">/</span></li>
        <li class="text-gray-600">Property</li>
      </ol>
    </nav>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Loading property details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <p class="text-red-600">{{ error }}</p>
      <button
        @click="loadProperty"
        class="mt-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
      >
        Retry
      </button>
    </div>

    <!-- Property Content -->
    <div v-else-if="property" class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ propertyAddress }}</h1>
            <p class="text-lg text-gray-600 mt-1">{{ propertyTypeLabel }}</p>
          </div>
          <div class="flex space-x-2">
            <button
              @click="showEditModal = true"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Edit
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              Delete
            </button>
          </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Current Value</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(property.current_value) }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Equity</p>
            <p class="text-2xl font-bold text-green-600">{{ formatCurrency(property.equity || 0) }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600">Mortgage Balance</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(mortgageBalance) }}</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4" v-if="property.property_type === 'buy_to_let'">
            <p class="text-sm text-gray-600">Net Rental Yield</p>
            <p class="text-2xl font-bold text-blue-600">{{ property.net_rental_yield || 0 }}%</p>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-md">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="px-6 py-3 border-b-2 font-medium text-sm transition-colors"
              :class="
                activeTab === tab.id
                  ? 'border-blue-600 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              "
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <div class="p-6">
          <!-- Overview Tab -->
          <div v-show="activeTab === 'overview'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Property Details</h3>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Address:</dt>
                    <dd class="text-sm font-medium text-gray-900 text-right">{{ propertyAddress }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Postcode:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ property.postcode }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Property Type:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ propertyTypeLabel }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Purchase Date:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatDate(property.purchase_date) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Purchase Price:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.purchase_price) }}</dd>
                  </div>
                </dl>
              </div>

              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Ownership</h3>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Ownership Type:</dt>
                    <dd class="text-sm font-medium text-gray-900 capitalize">{{ property.ownership_type }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Ownership Percentage:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ property.ownership_percentage }}%</dd>
                  </div>
                </dl>
              </div>

              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Valuation</h3>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Current Value:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.current_value) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Valuation Date:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatDate(property.valuation_date) || 'Not set' }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Value Change:</dt>
                    <dd class="text-sm font-medium" :class="valueChange >= 0 ? 'text-green-600' : 'text-red-600'">
                      {{ formatCurrency(valueChange) }} ({{ valueChangePercent }}%)
                    </dd>
                  </div>
                </dl>
              </div>

              <div v-if="property.property_type === 'buy_to_let'">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Rental Income</h3>
                <dl class="space-y-2">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Monthly Rental Income:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.monthly_rental_income) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Annual Rental Income:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(property.annual_rental_income) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Occupancy Rate:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ property.occupancy_rate_percent || 100 }}%</dd>
                  </div>
                  <div class="flex justify-between" v-if="property.tenant_name">
                    <dt class="text-sm text-gray-600">Tenant:</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ property.tenant_name }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>

          <!-- Mortgage Tab -->
          <div v-show="activeTab === 'mortgage'" class="space-y-4">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-semibold text-gray-800">Mortgages</h3>
              <button
                @click="showMortgageModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
              >
                Add Mortgage
              </button>
            </div>

            <div v-if="mortgages.length === 0" class="text-center py-8 text-gray-500">
              <p>No mortgages found for this property.</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="mortgage in mortgages"
                :key="mortgage.id"
                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">{{ mortgage.lender_name }}</h4>
                    <p class="text-sm text-gray-600 mt-1">{{ mortgage.mortgage_type }} - {{ mortgage.rate_type }} {{ mortgage.interest_rate }}%</p>
                    <div class="grid grid-cols-2 gap-4 mt-3">
                      <div>
                        <p class="text-xs text-gray-500">Outstanding Balance</p>
                        <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(mortgage.outstanding_balance) }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500">Monthly Payment</p>
                        <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(mortgage.monthly_payment) }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="flex space-x-2 ml-4">
                    <button
                      @click="editMortgage(mortgage)"
                      class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteMortgageConfirm(mortgage.id)"
                      class="px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Financials Tab -->
          <div v-show="activeTab === 'financials'">
            <PropertyFinancials
              :property="property"
              :mortgages="mortgages"
              @update-costs="handleCostsUpdate"
            />
          </div>

          <!-- Taxes Tab -->
          <div v-show="activeTab === 'taxes'">
            <PropertyTaxCalculator :property="property" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <PropertyForm
      v-if="showEditModal"
      :property="property"
      @save="handlePropertyUpdate"
      @close="showEditModal = false"
    />

    <MortgageForm
      v-if="showMortgageModal"
      :property-id="propertyId"
      :mortgage="selectedMortgage"
      @save="handleMortgageSave"
      @close="closeMortgageModal"
    />

    <ConfirmationModal
      v-if="showDeleteConfirm"
      title="Delete Property"
      message="Are you sure you want to delete this property? This action cannot be undone."
      @confirm="handleDelete"
      @cancel="showDeleteConfirm = false"
    />

    <ConfirmationModal
      v-if="showDeleteMortgageConfirm"
      title="Delete Mortgage"
      message="Are you sure you want to delete this mortgage?"
      @confirm="handleMortgageDelete"
      @cancel="showDeleteMortgageConfirm = false"
    />
    </div>
  </AppLayout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import PropertyForm from './PropertyForm.vue';
import MortgageForm from './MortgageForm.vue';
import PropertyFinancials from './PropertyFinancials.vue';
import PropertyTaxCalculator from './PropertyTaxCalculator.vue';
import ConfirmationModal from '../../Common/ConfirmationModal.vue';

export default {
  name: 'PropertyDetail',

  components: {
    AppLayout,
    PropertyForm,
    MortgageForm,
    PropertyFinancials,
    PropertyTaxCalculator,
    ConfirmationModal,
  },

  data() {
    return {
      propertyId: parseInt(this.$route.params.id),
      activeTab: 'overview',
      tabs: [
        { id: 'overview', label: 'Overview' },
        { id: 'mortgage', label: 'Mortgage' },
        { id: 'financials', label: 'Financials' },
        { id: 'taxes', label: 'Taxes' },
      ],
      showEditModal: false,
      showMortgageModal: false,
      showDeleteConfirm: false,
      showDeleteMortgageConfirm: false,
      selectedMortgage: null,
      mortgageToDelete: null,
    };
  },

  computed: {
    ...mapState('netWorth', ['selectedProperty', 'mortgages', 'loading', 'error']),

    property() {
      return this.selectedProperty;
    },

    propertyAddress() {
      if (!this.property) return '';
      const parts = [
        this.property.address_line_1,
        this.property.address_line_2,
        this.property.city,
      ].filter(Boolean);
      return parts.join(', ');
    },

    propertyTypeLabel() {
      const types = {
        main_residence: 'Main Residence',
        secondary_residence: 'Secondary Residence',
        buy_to_let: 'Buy to Let',
      };
      return types[this.property?.property_type] || '';
    },

    mortgageBalance() {
      // If detailed mortgage records exist, sum them
      if (this.mortgages && this.mortgages.length > 0) {
        return this.mortgages.reduce((sum, m) => sum + (m.outstanding_balance || 0), 0);
      }
      // Otherwise, fall back to simple outstanding_mortgage field from property
      return this.property?.outstanding_mortgage || 0;
    },

    valueChange() {
      if (!this.property) return 0;
      return this.property.current_value - this.property.purchase_price;
    },

    valueChangePercent() {
      if (!this.property || this.property.purchase_price === 0) return '0.00';
      const percent = (this.valueChange / this.property.purchase_price) * 100;
      return percent.toFixed(2);
    },
  },

  mounted() {
    this.loadProperty();
  },

  methods: {
    ...mapActions('netWorth', [
      'fetchProperty',
      'fetchPropertyMortgages',
      'updateProperty',
      'deleteProperty',
      'createMortgage',
      'updateMortgage',
      'deleteMortgage',
    ]),

    async loadProperty() {
      try {
        await this.fetchProperty(this.propertyId);
        await this.fetchPropertyMortgages(this.propertyId);
      } catch (error) {
        console.error('Failed to load property:', error);
      }
    },

    async handlePropertyUpdate(data) {
      try {
        await this.updateProperty({ id: this.propertyId, data: data.property });
        this.showEditModal = false;
        // Refresh property data
        await this.loadProperty();
      } catch (error) {
        console.error('Failed to update property:', error);
      }
    },

    async handleCostsUpdate(costsData) {
      try {
        await this.updateProperty({ id: this.propertyId, data: costsData });
        // Refresh property data
        await this.loadProperty();
      } catch (error) {
        console.error('Failed to update costs:', error);
        throw error;
      }
    },

    confirmDelete() {
      this.showDeleteConfirm = true;
    },

    async handleDelete() {
      try {
        await this.deleteProperty(this.propertyId);
        this.showDeleteConfirm = false;
        // Navigate back to net worth page
        this.$router.push('/net-worth');
      } catch (error) {
        console.error('Failed to delete property:', error);
      }
    },

    editMortgage(mortgage) {
      this.selectedMortgage = mortgage;
      this.showMortgageModal = true;
    },

    closeMortgageModal() {
      this.showMortgageModal = false;
      this.selectedMortgage = null;
    },

    async handleMortgageSave(mortgageData) {
      try {
        if (this.selectedMortgage) {
          // Update existing mortgage
          await this.updateMortgage({
            id: this.selectedMortgage.id,
            data: mortgageData,
            propertyId: this.propertyId,
          });
        } else {
          // Create new mortgage
          await this.createMortgage({
            propertyId: this.propertyId,
            data: mortgageData,
          });
        }
        this.closeMortgageModal();
        // Refresh data
        await this.loadProperty();
      } catch (error) {
        console.error('Failed to save mortgage:', error);
      }
    },

    deleteMortgageConfirm(mortgageId) {
      this.mortgageToDelete = mortgageId;
      this.showDeleteMortgageConfirm = true;
    },

    async handleMortgageDelete() {
      try {
        await this.deleteMortgage({
          id: this.mortgageToDelete,
          propertyId: this.propertyId,
        });
        this.showDeleteMortgageConfirm = false;
        this.mortgageToDelete = null;
        // Refresh data
        await this.loadProperty();
      } catch (error) {
        console.error('Failed to delete mortgage:', error);
      }
    },

    formatCurrency(value) {
      if (value === null || value === undefined) return '£0';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
      });
    },
  },
};
</script>
