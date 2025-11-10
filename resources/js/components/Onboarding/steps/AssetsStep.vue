<template>
  <OnboardingStep
    title="Assets & Wealth"
    description="Add your properties, investments, and savings accounts"
    :can-go-back="true"
    :can-skip="true"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
    @skip="handleSkip"
  >
    <div class="space-y-6">
      <!-- Tabs for different asset types -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Asset types">
          <button
            v-for="tab in assetTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              activeTab === tab.id
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.name }}
            <span v-if="tab.count > 0" class="ml-2 py-0.5 px-2 rounded-full text-xs bg-gray-100">
              {{ tab.count }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Retirement Tab -->
      <div v-show="activeTab === 'retirement'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Pensions are often one of the largest components of retirement planning. Add your DC pensions, DB pensions, and State Pension forecast to get a complete retirement picture.
        </p>

        <!-- DC Pensions -->
        <div v-if="pensions.dc.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            DC Pensions ({{ pensions.dc.length }})
          </h4>
          <div
            v-for="pension in pensions.dc"
            :key="pension.id"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ pension.provider }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-orange-100 text-orange-700 rounded">
                    DC Pension
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Current Value</p>
                  <p class="text-body font-medium text-gray-900">£{{ pension.current_fund_value?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="openPensionForm('dc', pension)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="deletePension('dc', pension.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- DB Pensions -->
        <div v-if="pensions.db.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            DB Pensions ({{ pensions.db.length }})
          </h4>
          <div
            v-for="pension in pensions.db"
            :key="pension.id"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ pension.scheme_name }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded">
                    DB Pension
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Annual Pension</p>
                  <p class="text-body font-medium text-gray-900">£{{ pension.accrued_annual_pension?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="openPensionForm('db', pension)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="deletePension('db', pension.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- State Pension -->
        <div v-if="pensions.state" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            State Pension
          </h4>
          <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    UK State Pension
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-green-100 text-green-700 rounded">
                    State Pension
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Annual Forecast</p>
                  <p class="text-body font-medium text-gray-900">£{{ pensions.state.state_pension_forecast_annual?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="openPensionForm('state', pensions.state)"
                >
                  Edit
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Pension Buttons -->
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="btn-secondary"
            @click="openPensionForm('dc')"
          >
            + Add DC Pension
          </button>
          <button
            type="button"
            class="btn-secondary"
            @click="openPensionForm('db')"
          >
            + Add DB Pension
          </button>
          <button
            type="button"
            class="btn-secondary"
            @click="openPensionForm('state')"
          >
            + Add State Pension
          </button>
        </div>

        <p v-if="pensions.dc.length === 0 && pensions.db.length === 0 && !pensions.state" class="text-body-sm text-gray-500 italic">
          You can skip this step and add pensions later from your dashboard.
        </p>
      </div>

      <!-- Properties Tab -->
      <div v-show="activeTab === 'properties'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Properties are usually the largest component of an estate. Adding property details helps us calculate your potential Inheritance Tax liability.
        </p>

        <!-- Added Properties List -->
        <div v-if="properties.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            Properties ({{ properties.length }})
          </h4>

          <div
            v-for="property in properties"
            :key="property.id"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900 capitalize">
                    {{ property.property_type?.replace(/_/g, ' ') }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded capitalize">
                    {{ property.ownership_type }}
                  </span>
                </div>
                <p class="text-body-sm text-gray-600">
                  {{ property.address_line_1 }}{{ property.address_line_2 ? ', ' + property.address_line_2 : '' }}
                </p>
                <p class="text-body-sm text-gray-600">
                  {{ property.city }}, {{ property.postcode }}
                </p>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Value</p>
                  <p class="text-body font-medium text-gray-900">£{{ property.current_value?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="editProperty(property)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="deleteProperty(property.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Property Button -->
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showPropertyForm = true"
        >
          + Add Property
        </button>

        <p v-if="properties.length === 0" class="text-body-sm text-gray-500 italic">
          You can skip this step and add properties later from your dashboard.
        </p>
      </div>

      <!-- Investments Tab -->
      <div v-show="activeTab === 'investments'" class="space-y-4">
        <!-- Feature Status Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="text-body-sm text-blue-800">
                <strong>Investment Module - Coming Soon</strong>
              </p>
              <p class="text-body-sm text-blue-700 mt-1">
                While the comprehensive Investment Planning module is currently in development, you can record your investment accounts and holdings here. This information will be used in your Estate Planning calculations and Inheritance Tax liability assessments. Full portfolio analysis, performance tracking, and investment recommendations will be available in upcoming releases.
              </p>
            </div>
          </div>
        </div>

        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Investment accounts include ISAs, General Investment Accounts, and bonds. These form part of your overall wealth.
        </p>

        <!-- Added Investments List -->
        <div v-if="investments.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            Investment Accounts ({{ investments.length }})
          </h4>

          <div
            v-for="investment in investments"
            :key="investment.id"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ investment.provider }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-green-100 text-green-700 rounded capitalize">
                    {{ investment.account_type?.replace(/_/g, ' ') }}
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Value</p>
                  <p class="text-body font-medium text-gray-900">£{{ investment.current_value?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="editInvestment(investment)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="deleteInvestment(investment.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Investment Button -->
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showInvestmentForm = true"
        >
          + Add Investment Account
        </button>

        <p v-if="investments.length === 0" class="text-body-sm text-gray-500 italic">
          You can skip this step and add investments later from your dashboard.
        </p>
      </div>

      <!-- Cash Tab -->
      <div v-show="activeTab === 'cash'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Include all cash and bank accounts, including current accounts, Cash ISAs, easy access savings, and fixed-term deposits.
        </p>

        <!-- Added Cash Accounts List -->
        <div v-if="savingsAccounts.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            Accounts ({{ savingsAccounts.length }})
          </h4>

          <div
            v-for="savings in savingsAccounts"
            :key="savings.id"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ savings.institution }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-purple-100 text-purple-700 rounded capitalize">
                    {{ savings.account_type?.replace(/_/g, ' ') }}
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Balance</p>
                  <p class="text-body font-medium text-gray-900">£{{ savings.current_balance?.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="editSavings(savings)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="deleteSavings(savings.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Account Button -->
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showSavingsForm = true"
        >
          + Add Account
        </button>

        <p v-if="savingsAccounts.length === 0" class="text-body-sm text-gray-500 italic">
          You can skip this step and add accounts later from your dashboard.
        </p>
      </div>
    </div>

    <!-- Property Form Modal -->
    <PropertyForm
      v-if="showPropertyForm"
      :property="editingProperty"
      @close="closePropertyForm"
      @save="handlePropertySaved"
    />

    <!-- Investment Account Form Modal -->
    <AccountForm
      v-if="showInvestmentForm"
      :show="showInvestmentForm"
      :account="editingInvestment"
      @close="closeInvestmentForm"
      @save="handleInvestmentSaved"
    />

    <!-- Savings Account Form Modal -->
    <SaveAccountModal
      v-if="showSavingsForm"
      :account="editingSavings"
      @close="closeSavingsForm"
      @save="handleSavingsSaved"
    />

    <!-- Pension Form Modals -->
    <DCPensionForm
      v-if="showPensionForm && pensionFormType === 'dc'"
      :pension="editingPension"
      @close="closePensionForm"
      @save="handlePensionSaved"
    />

    <DBPensionForm
      v-if="showPensionForm && pensionFormType === 'db'"
      :pension="editingPension"
      @close="closePensionForm"
      @save="handlePensionSaved"
    />

    <StatePensionForm
      v-if="showPensionForm && pensionFormType === 'state'"
      :pension="editingPension"
      @close="closePensionForm"
      @save="handlePensionSaved"
    />
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import OnboardingStep from '../OnboardingStep.vue';
import PropertyForm from '@/components/NetWorth/Property/PropertyForm.vue';
import AccountForm from '@/components/Investment/AccountForm.vue';
import SaveAccountModal from '@/components/Savings/SaveAccountModal.vue';
import DCPensionForm from '@/components/Retirement/DCPensionForm.vue';
import DBPensionForm from '@/components/Retirement/DBPensionForm.vue';
import StatePensionForm from '@/components/Retirement/StatePensionForm.vue';
import propertyService from '@/services/propertyService';
import investmentService from '@/services/investmentService';
import savingsService from '@/services/savingsService';
import retirementService from '@/services/retirementService';

export default {
  name: 'AssetsStep',

  components: {
    OnboardingStep,
    PropertyForm,
    AccountForm,
    SaveAccountModal,
    DCPensionForm,
    DBPensionForm,
    StatePensionForm,
  },

  emits: ['next', 'back', 'skip'],

  setup(_props, { emit }) {
    const activeTab = ref('retirement');

    // Properties state
    const properties = ref([]);
    const showPropertyForm = ref(false);
    const editingProperty = ref(null);

    // Investments state
    const investments = ref([]);
    const showInvestmentForm = ref(false);
    const editingInvestment = ref(null);

    // Savings state
    const savingsAccounts = ref([]);
    const showSavingsForm = ref(false);
    const editingSavings = ref(null);

    const loading = ref(false);
    const error = ref(null);

    // Pensions state
    const pensions = ref({ dc: [], db: [], state: null });
    const showPensionForm = ref(false);
    const pensionFormType = ref(null); // 'dc', 'db', or 'state'
    const editingPension = ref(null);

    // Tab counts
    const assetTabs = computed(() => [
      { id: 'retirement', name: 'Retirement', count: pensions.value.dc.length + pensions.value.db.length + (pensions.value.state ? 1 : 0) },
      { id: 'properties', name: 'Properties', count: properties.value.length },
      { id: 'investments', name: 'Investments', count: investments.value.length },
      { id: 'cash', name: 'Cash', count: savingsAccounts.value.length },
    ]);

    // Load existing data
    onMounted(async () => {
      await Promise.all([
        loadPensions(),
        loadProperties(),
        loadInvestments(),
        loadSavingsAccounts(),
      ]);
    });

    // Pensions methods
    async function loadPensions() {
      try {
        const response = await retirementService.getRetirementData();
        pensions.value = {
          dc: response.data?.dc_pensions || [],
          db: response.data?.db_pensions || [],
          state: response.data?.state_pension || null,
        };
      } catch (err) {
        console.error('Failed to load pensions', err);
      }
    }

    function openPensionForm(type, pension = null) {
      pensionFormType.value = type;
      editingPension.value = pension;
      showPensionForm.value = true;
    }

    async function deletePension(type, id) {
      const confirmMessage = `Are you sure you want to delete this ${type === 'dc' ? 'DC' : 'DB'} pension?`;
      if (confirm(confirmMessage)) {
        try {
          if (type === 'dc') {
            await retirementService.deleteDCPension(id);
          } else if (type === 'db') {
            await retirementService.deleteDBPension(id);
          }
          await loadPensions();
        } catch (err) {
          error.value = 'Failed to delete pension';
        }
      }
    }

    function closePensionForm() {
      showPensionForm.value = false;
      pensionFormType.value = null;
      editingPension.value = null;
    }

    async function handlePensionSaved(data) {
      try {
        if (pensionFormType.value === 'dc') {
          if (editingPension.value) {
            await retirementService.updateDCPension(editingPension.value.id, data);
          } else {
            await retirementService.createDCPension(data);
          }
        } else if (pensionFormType.value === 'db') {
          if (editingPension.value) {
            await retirementService.updateDBPension(editingPension.value.id, data);
          } else {
            await retirementService.createDBPension(data);
          }
        } else if (pensionFormType.value === 'state') {
          await retirementService.updateStatePension(data);
        }

        closePensionForm();
        await loadPensions();
      } catch (err) {
        console.error('Failed to save pension:', err);
        error.value = 'Failed to save pension. Please try again.';
      }
    }

    // Properties methods
    async function loadProperties() {
      try {
        const response = await propertyService.getProperties();
        // propertyService already returns response.data, so response is the properties array
        properties.value = Array.isArray(response) ? response : [];
      } catch (err) {
        console.error('Failed to load properties', err);
      }
    }

    function editProperty(property) {
      editingProperty.value = property;
      showPropertyForm.value = true;
    }

    async function deleteProperty(id) {
      if (confirm('Are you sure you want to delete this property?')) {
        try {
          await propertyService.deleteProperty(id);
          await loadProperties();
        } catch (err) {
          error.value = 'Failed to delete property';
        }
      }
    }

    function closePropertyForm() {
      showPropertyForm.value = false;
      editingProperty.value = null;
    }

    async function handlePropertySaved(data) {
      try {
        // Save property first
        const propertyResponse = editingProperty.value
          ? await propertyService.updateProperty(editingProperty.value.id, data.property)
          : await propertyService.createProperty(data.property);

        // Get property ID from response (API returns property directly, not wrapped in data)
        const propertyId = editingProperty.value?.id || propertyResponse.data?.id || propertyResponse.id;

        // If mortgage data provided and property was saved successfully, save/update mortgage
        if (data.mortgage && propertyId) {
          console.log('Mortgage data being sent:', JSON.stringify(data.mortgage, null, 2));

          // Check if property already has a mortgage (when editing)
          const existingMortgage = editingProperty.value?.mortgages?.[0];

          if (existingMortgage) {
            // Update existing mortgage
            await propertyService.updatePropertyMortgage(propertyId, existingMortgage.id, data.mortgage);
          } else {
            // Create new mortgage
            try {
              await propertyService.createPropertyMortgage(propertyId, data.mortgage);
            } catch (error) {
              console.error('Validation errors:', error.response?.data?.errors);
              throw error;
            }
          }
        }

        closePropertyForm();
        await loadProperties();
      } catch (err) {
        console.error('Failed to save property/mortgage:', err);
        error.value = 'Failed to save property. Please try again.';
      }
    }

    // Investments methods
    async function loadInvestments() {
      try {
        const response = await investmentService.getInvestmentData();
        investments.value = response.data?.accounts || [];
      } catch (err) {
        console.error('Failed to load investments', err);
      }
    }

    function editInvestment(investment) {
      editingInvestment.value = investment;
      showInvestmentForm.value = true;
    }

    async function deleteInvestment(id) {
      if (confirm('Are you sure you want to delete this investment account?')) {
        try {
          await investmentService.deleteAccount(id);
          await loadInvestments();
        } catch (err) {
          error.value = 'Failed to delete investment account';
        }
      }
    }

    function closeInvestmentForm() {
      showInvestmentForm.value = false;
      editingInvestment.value = null;
    }

    async function handleInvestmentSaved(data) {
      try {
        // Save investment account
        if (editingInvestment.value) {
          await investmentService.updateAccount(editingInvestment.value.id, data);
        } else {
          await investmentService.createAccount(data);
        }

        closeInvestmentForm();
        await loadInvestments();
      } catch (err) {
        console.error('Failed to save investment account:', err);
        error.value = 'Failed to save investment account. Please try again.';
      }
    }

    // Savings methods
    async function loadSavingsAccounts() {
      try {
        const response = await savingsService.getSavingsData();
        savingsAccounts.value = response.data?.accounts || [];
      } catch (err) {
        console.error('Failed to load savings accounts', err);
      }
    }

    function editSavings(savings) {
      editingSavings.value = savings;
      showSavingsForm.value = true;
    }

    async function deleteSavings(id) {
      if (confirm('Are you sure you want to delete this savings account?')) {
        try {
          await savingsService.deleteAccount(id);
          await loadSavingsAccounts();
        } catch (err) {
          error.value = 'Failed to delete savings account';
        }
      }
    }

    function closeSavingsForm() {
      showSavingsForm.value = false;
      editingSavings.value = null;
    }

    async function handleSavingsSaved(data) {
      try {
        // Save savings account
        if (editingSavings.value) {
          await savingsService.updateAccount(editingSavings.value.id, data);
        } else {
          await savingsService.createAccount(data);
        }

        closeSavingsForm();
        await loadSavingsAccounts();
      } catch (err) {
        console.error('Failed to save savings account:', err);
        error.value = 'Failed to save savings account. Please try again.';
      }
    }

    // Navigation
    function handleNext() {
      emit('next');
    }

    function handleBack() {
      emit('back');
    }

    function handleSkip() {
      emit('skip', 'assets');
    }

    return {
      activeTab,
      assetTabs,
      // Pensions
      pensions,
      showPensionForm,
      pensionFormType,
      editingPension,
      openPensionForm,
      deletePension,
      closePensionForm,
      handlePensionSaved,
      // Properties
      properties,
      showPropertyForm,
      editingProperty,
      editProperty,
      deleteProperty,
      closePropertyForm,
      handlePropertySaved,
      // Investments
      investments,
      showInvestmentForm,
      editingInvestment,
      editInvestment,
      deleteInvestment,
      closeInvestmentForm,
      handleInvestmentSaved,
      // Savings
      savingsAccounts,
      showSavingsForm,
      editingSavings,
      editSavings,
      deleteSavings,
      closeSavingsForm,
      handleSavingsSaved,
      // Common
      loading,
      error,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
