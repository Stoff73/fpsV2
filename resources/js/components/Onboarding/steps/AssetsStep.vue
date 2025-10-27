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

      <!-- Properties Tab -->
      <div v-show="activeTab === 'properties'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Properties are usually the largest component of an estate. Adding property details helps us calculate your potential Inheritance Tax liability.
        </p>

        <!-- Property Form Modal -->
      <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 bg-white">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          {{ editingIndex !== null ? 'Edit Property' : 'Add Property' }}
        </h4>

        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="property_type" class="label">
              Property Type <span class="text-red-500">*</span>
            </label>
            <select
              id="property_type"
              v-model="currentProperty.property_type"
              class="input-field"
              required
            >
              <option value="">Select property type</option>
              <option value="main_residence">Main Residence</option>
              <option value="second_home">Second Home</option>
              <option value="buy_to_let">Buy to Let</option>
              <option value="commercial">Commercial</option>
              <option value="land">Land</option>
            </select>
          </div>

          <div>
            <label for="address_line_1" class="label">
              Address <span class="text-red-500">*</span>
            </label>
            <input
              id="address_line_1"
              v-model="currentProperty.address_line_1"
              type="text"
              class="input-field"
              placeholder="Address line 1"
              required
            >
            <input
              v-model="currentProperty.address_line_2"
              type="text"
              class="input-field mt-2"
              placeholder="Address line 2 (optional)"
            >
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="city" class="label">
                City <span class="text-red-500">*</span>
              </label>
              <input
                id="city"
                v-model="currentProperty.city"
                type="text"
                class="input-field"
                placeholder="City"
                required
              >
            </div>

            <div>
              <label for="postcode" class="label">
                Postcode <span class="text-red-500">*</span>
              </label>
              <input
                id="postcode"
                v-model="currentProperty.postcode"
                type="text"
                class="input-field"
                placeholder="Postcode"
                required
              >
            </div>
          </div>

          <!-- Country Selector -->
          <div>
            <CountrySelector
              v-model="currentProperty.country"
              label="Country"
              :required="true"
              default-country="United Kingdom"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="current_value" class="label">
                Current Value <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="current_value"
                  v-model.number="currentProperty.current_value"
                  type="number"
                  min="0"
                  step="1000"
                  class="input-field pl-8"
                  placeholder="0"
                  required
                >
              </div>
            </div>

            <div>
              <label for="outstanding_mortgage" class="label">
                Outstanding Mortgage (if applicable)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="outstanding_mortgage"
                  v-model.number="currentProperty.outstanding_mortgage"
                  type="number"
                  min="0"
                  step="1000"
                  class="input-field pl-8"
                  placeholder="0"
                >
              </div>
            </div>
          </div>

          <div>
            <label for="ownership_type" class="label">
              Ownership Type <span class="text-red-500">*</span>
            </label>
            <select
              id="ownership_type"
              v-model="currentProperty.ownership_type"
              class="input-field"
              required
            >
              <option value="individual">Individual Owner</option>
              <option value="joint">Joint Owner</option>
              <option value="trust">Trust</option>
            </select>
            <p class="mt-1 text-body-sm text-gray-500">
              Joint ownership typically means owned with spouse or partner
            </p>
          </div>

          <!-- Rental Income (only for buy to let) -->
          <div v-if="currentProperty.property_type === 'buy_to_let'">
            <label for="monthly_rental_income" class="label">
              Monthly Rental Income
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
              <input
                id="monthly_rental_income"
                v-model.number="currentProperty.monthly_rental_income"
                type="number"
                min="0"
                step="100"
                class="input-field pl-8"
                placeholder="0"
              >
            </div>
            <p class="mt-1 text-body-sm text-gray-500">
              This will be included in your annual rental income
            </p>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button
            type="button"
            class="btn-primary"
            @click="saveProperty"
          >
            {{ editingIndex !== null ? 'Update Property' : 'Add Property' }}
          </button>
          <button
            type="button"
            class="btn-secondary"
            @click="cancelForm"
          >
            Cancel
          </button>
        </div>
      </div>

      <!-- Added Properties List -->
      <div v-if="properties.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Properties ({{ properties.length }})
        </h4>

        <div
          v-for="(property, index) in properties"
          :key="index"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h5 class="text-body font-medium text-gray-900 capitalize">
                  {{ property.property_type.replace(/_/g, ' ') }}
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
              <div class="mt-2 grid grid-cols-2 gap-2">
                <div>
                  <p class="text-body-sm text-gray-500">Value</p>
                  <p class="text-body font-medium text-gray-900">£{{ property.current_value.toLocaleString() }}</p>
                </div>
                <div v-if="property.outstanding_mortgage > 0">
                  <p class="text-body-sm text-gray-500">Mortgage</p>
                  <p class="text-body font-medium text-gray-900">£{{ property.outstanding_mortgage.toLocaleString() }}</p>
                </div>
                <div v-if="property.monthly_rental_income > 0">
                  <p class="text-body-sm text-gray-500">Monthly Rent</p>
                  <p class="text-body font-medium text-gray-900">£{{ property.monthly_rental_income.toLocaleString() }}</p>
                </div>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                type="button"
                class="text-primary-600 hover:text-primary-700 text-body-sm"
                @click="editProperty(index)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="removeProperty(index)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Property Button -->
      <div v-if="!showForm">
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showAddForm"
        >
          + Add Property
        </button>
      </div>

      <p v-if="properties.length === 0" class="text-body-sm text-gray-500 italic">
        You can skip this step and add properties later from your dashboard.
      </p>
      </div>

      <!-- Investments Tab -->
      <div v-show="activeTab === 'investments'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Investment accounts include ISAs, General Investment Accounts, and pensions. These form part of your overall wealth.
        </p>

        <!-- Investment Form -->
        <div v-if="showInvestmentForm" class="border border-gray-200 rounded-lg p-4 bg-white">
          <h4 class="text-body font-medium text-gray-900 mb-4">
            {{ editingInvestmentIndex !== null ? 'Edit Investment Account' : 'Add Investment Account' }}
          </h4>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <label for="investment_institution" class="label">
                Institution/Provider <span class="text-red-500">*</span>
              </label>
              <input
                id="investment_institution"
                v-model="currentInvestment.institution"
                type="text"
                class="input-field"
                placeholder="e.g., Vanguard, Hargreaves Lansdown"
                required
              >
            </div>

            <div>
              <label for="investment_account_type" class="label">
                Account Type <span class="text-red-500">*</span>
              </label>
              <select
                id="investment_account_type"
                v-model="currentInvestment.account_type"
                class="input-field"
                required
              >
                <option value="">Select account type</option>
                <option value="stocks_shares_isa">Stocks & Shares ISA</option>
                <option value="gia">General Investment Account</option>
                <option value="offshore_bond">Offshore Bond</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Country Selector -->
            <div>
              <CountrySelector
                v-model="currentInvestment.country"
                label="Country"
                :required="true"
                default-country="United Kingdom"
              />
            </div>

            <div>
              <label for="investment_current_value" class="label">
                Current Value <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="investment_current_value"
                  v-model.number="currentInvestment.current_value"
                  type="number"
                  min="0"
                  step="100"
                  class="input-field pl-8"
                  placeholder="0"
                  required
                >
              </div>
            </div>

            <!-- ISA Allowance Used (only for S&S ISA) -->
            <div v-if="currentInvestment.account_type === 'stocks_shares_isa'">
              <label for="investment_isa_allowance_used" class="label">
                ISA Allowance Used This Tax Year (2025/26)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="investment_isa_allowance_used"
                  v-model.number="currentInvestment.isa_allowance_used"
                  type="number"
                  min="0"
                  max="20000"
                  step="100"
                  class="input-field pl-8"
                  placeholder="0"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                How much have you contributed to this ISA in the current tax year? (Annual limit: £20,000)
              </p>
            </div>

            <div>
              <label for="investment_ownership_type" class="label">
                Ownership Type <span class="text-red-500">*</span>
              </label>
              <select
                id="investment_ownership_type"
                v-model="currentInvestment.ownership_type"
                class="input-field"
                required
              >
                <option value="individual">Individual Owner</option>
                <option value="joint">Joint Owner</option>
                <option value="trust">Trust</option>
              </select>
              <p class="mt-1 text-body-sm text-gray-500">
                ISAs must be individually owned
              </p>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button
              type="button"
              class="btn-primary"
              @click="saveInvestment"
            >
              {{ editingInvestmentIndex !== null ? 'Update Investment' : 'Add Investment' }}
            </button>
            <button
              type="button"
              class="btn-secondary"
              @click="cancelInvestmentForm"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Added Investments List -->
        <div v-if="investments.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            Investment Accounts ({{ investments.length }})
          </h4>

          <div
            v-for="(investment, index) in investments"
            :key="index"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ investment.institution }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-green-100 text-green-700 rounded capitalize">
                    {{ investment.account_type.replace(/_/g, ' ') }}
                  </span>
                  <span class="text-body-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded capitalize">
                    {{ investment.ownership_type }}
                  </span>
                </div>
                <div class="mt-2">
                  <p class="text-body-sm text-gray-500">Value</p>
                  <p class="text-body font-medium text-gray-900">£{{ investment.current_value.toLocaleString() }}</p>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="editInvestment(index)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="removeInvestment(index)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Investment Button -->
        <div v-if="!showInvestmentForm">
          <button
            type="button"
            class="btn-secondary w-full md:w-auto"
            @click="showAddInvestmentForm"
          >
            + Add Investment Account
          </button>
        </div>

        <p v-if="investments.length === 0" class="text-body-sm text-gray-500 italic">
          You can skip this step and add investments later from your dashboard.
        </p>
      </div>

      <!-- Cash Tab -->
      <div v-show="activeTab === 'cash'" class="space-y-4">
        <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
          Include all cash and bank accounts, including current accounts, Cash ISAs, easy access savings, and fixed-term deposits.
        </p>

        <!-- Cash Account Form -->
        <div v-if="showSavingsForm" class="border border-gray-200 rounded-lg p-4 bg-white">
          <h4 class="text-body font-medium text-gray-900 mb-4">
            {{ editingSavingsIndex !== null ? 'Edit Account' : 'Add Account' }}
          </h4>

          <div class="grid grid-cols-1 gap-4">
            <div>
              <label for="savings_institution" class="label">
                Bank/Institution <span class="text-red-500">*</span>
              </label>
              <input
                id="savings_institution"
                v-model="currentSavings.institution"
                type="text"
                class="input-field"
                placeholder="e.g., HSBC, Nationwide"
                required
              >
            </div>

            <div>
              <label for="savings_account_type" class="label">
                Account Type <span class="text-red-500">*</span>
              </label>
              <select
                id="savings_account_type"
                v-model="currentSavings.account_type"
                class="input-field"
                required
              >
                <option value="">Select account type</option>
                <option value="current_account">Current Account</option>
                <option value="cash_isa">Cash ISA</option>
                <option value="easy_access">Easy Access Savings</option>
                <option value="fixed_term">Fixed Term Savings</option>
                <option value="notice_account">Notice Account</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Country Selector -->
            <div>
              <CountrySelector
                v-model="currentSavings.country"
                label="Country"
                :required="true"
                default-country="United Kingdom"
              />
            </div>

            <div>
              <label for="savings_current_balance" class="label">
                Current Balance <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="savings_current_balance"
                  v-model.number="currentSavings.current_balance"
                  type="number"
                  min="0"
                  step="100"
                  class="input-field pl-8"
                  placeholder="0"
                  required
                >
              </div>
            </div>

            <div>
              <label for="savings_interest_rate" class="label">
                Interest Rate (%)
              </label>
              <input
                id="savings_interest_rate"
                v-model.number="currentSavings.interest_rate"
                type="number"
                min="0"
                max="100"
                step="0.01"
                class="input-field"
                placeholder="e.g., 4.5"
              >
            </div>

            <!-- ISA Allowance Used (only for Cash ISA) -->
            <div v-if="currentSavings.account_type === 'cash_isa'">
              <label for="savings_isa_allowance_used" class="label">
                ISA Allowance Used This Tax Year (2025/26)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">£</span>
                <input
                  id="savings_isa_allowance_used"
                  v-model.number="currentSavings.isa_allowance_used"
                  type="number"
                  min="0"
                  max="20000"
                  step="100"
                  class="input-field pl-8"
                  placeholder="0"
                >
              </div>
              <p class="mt-1 text-body-sm text-gray-500">
                How much have you contributed to this Cash ISA in the current tax year? (Annual limit: £20,000)
              </p>
            </div>

            <div>
              <label for="savings_ownership_type" class="label">
                Ownership Type <span class="text-red-500">*</span>
              </label>
              <select
                id="savings_ownership_type"
                v-model="currentSavings.ownership_type"
                class="input-field"
                required
              >
                <option value="individual">Individual Owner</option>
                <option value="joint">Joint Owner</option>
              </select>
              <p class="mt-1 text-body-sm text-gray-500">
                Cash ISAs must be individually owned
              </p>
            </div>
          </div>

          <div class="flex gap-3 mt-4">
            <button
              type="button"
              class="btn-primary"
              @click="saveSavings"
            >
              {{ editingSavingsIndex !== null ? 'Update Account' : 'Add Account' }}
            </button>
            <button
              type="button"
              class="btn-secondary"
              @click="cancelSavingsForm"
            >
              Cancel
            </button>
          </div>
        </div>

        <!-- Added Cash Accounts List -->
        <div v-if="savingsAccounts.length > 0" class="space-y-3">
          <h4 class="text-body font-medium text-gray-900">
            Accounts ({{ savingsAccounts.length }})
          </h4>

          <div
            v-for="(savings, index) in savingsAccounts"
            :key="index"
            class="border border-gray-200 rounded-lg p-4 bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <h5 class="text-body font-medium text-gray-900">
                    {{ savings.institution }}
                  </h5>
                  <span class="text-body-sm px-2 py-0.5 bg-purple-100 text-purple-700 rounded capitalize">
                    {{ savings.account_type.replace(/_/g, ' ') }}
                  </span>
                  <span class="text-body-sm px-2 py-0.5 bg-blue-100 text-blue-700 rounded capitalize">
                    {{ savings.ownership_type }}
                  </span>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                  <div>
                    <p class="text-body-sm text-gray-500">Balance</p>
                    <p class="text-body font-medium text-gray-900">£{{ savings.current_balance.toLocaleString() }}</p>
                  </div>
                  <div v-if="savings.interest_rate > 0">
                    <p class="text-body-sm text-gray-500">Interest Rate</p>
                    <p class="text-body font-medium text-gray-900">{{ savings.interest_rate }}%</p>
                  </div>
                </div>
              </div>
              <div class="flex gap-2 ml-4">
                <button
                  type="button"
                  class="text-primary-600 hover:text-primary-700 text-body-sm"
                  @click="editSavings(index)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="text-red-600 hover:text-red-700 text-body-sm"
                  @click="removeSavings(index)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Account Button -->
        <div v-if="!showSavingsForm">
          <button
            type="button"
            class="btn-secondary w-full md:w-auto"
            @click="showAddSavingsForm"
          >
            + Add Account
          </button>
        </div>

        <p v-if="savingsAccounts.length === 0" class="text-body-sm text-gray-500 italic">
          You can skip this step and add accounts later from your dashboard.
        </p>
      </div>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';
import CountrySelector from '@/components/Shared/CountrySelector.vue';

export default {
  name: 'AssetsStep',

  components: {
    OnboardingStep,
    CountrySelector,
  },

  emits: ['next', 'back', 'skip'],

  setup(_props, { emit }) {
    const store = useStore();

    const activeTab = ref('properties');
    const assetTabs = ref([
      { id: 'properties', name: 'Properties', count: 0 },
      { id: 'investments', name: 'Investments', count: 0 },
      { id: 'cash', name: 'Cash', count: 0 },
    ]);

    // Properties
    const properties = ref([]);
    const showForm = ref(false);
    const editingIndex = ref(null);
    const currentProperty = ref({
      property_type: '',
      address_line_1: '',
      address_line_2: '',
      city: '',
      postcode: '',
      country: 'United Kingdom',
      current_value: 0,
      outstanding_mortgage: 0,
      monthly_rental_income: 0,
      ownership_type: 'individual',
    });

    // Investments
    const investments = ref([]);
    const showInvestmentForm = ref(false);
    const editingInvestmentIndex = ref(null);
    const currentInvestment = ref({
      institution: '',
      account_type: '',
      country: 'United Kingdom',
      current_value: 0,
      isa_allowance_used: 0,
      ownership_type: 'individual',
    });

    // Cash (formerly Savings)
    const savingsAccounts = ref([]);
    const showSavingsForm = ref(false);
    const editingSavingsIndex = ref(null);
    const currentSavings = ref({
      institution: '',
      account_type: '',
      country: 'United Kingdom',
      current_balance: 0,
      interest_rate: 0,
      isa_allowance_used: 0,
      ownership_type: 'individual',
    });

    const loading = ref(false);
    const error = ref(null);

    const showAddForm = () => {
      showForm.value = true;
      editingIndex.value = null;
      resetCurrentProperty();
    };

    const resetCurrentProperty = () => {
      currentProperty.value = {
        property_type: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        postcode: '',
        country: 'United Kingdom',
        current_value: 0,
        outstanding_mortgage: 0,
        monthly_rental_income: 0,
        ownership_type: 'individual',
      };
    };

    const saveProperty = () => {
      // Validation
      if (
        !currentProperty.value.property_type ||
        !currentProperty.value.address_line_1 ||
        !currentProperty.value.city ||
        !currentProperty.value.postcode ||
        !currentProperty.value.current_value
      ) {
        error.value = 'Please fill in all required fields';
        return;
      }

      error.value = null;

      if (editingIndex.value !== null) {
        // Update existing property
        properties.value[editingIndex.value] = { ...currentProperty.value };
      } else {
        // Add new property
        properties.value.push({ ...currentProperty.value });
      }

      showForm.value = false;
      resetCurrentProperty();
      updateTabCounts();
    };

    const editProperty = (index) => {
      editingIndex.value = index;
      currentProperty.value = { ...properties.value[index] };
      showForm.value = true;
    };

    const removeProperty = (index) => {
      if (confirm('Are you sure you want to remove this property?')) {
        properties.value.splice(index, 1);
        updateTabCounts();
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingIndex.value = null;
      resetCurrentProperty();
      error.value = null;
    };

    // Investment methods
    const showAddInvestmentForm = () => {
      showInvestmentForm.value = true;
      editingInvestmentIndex.value = null;
      resetCurrentInvestment();
    };

    const resetCurrentInvestment = () => {
      currentInvestment.value = {
        institution: '',
        account_type: '',
        country: 'United Kingdom',
        current_value: 0,
        isa_allowance_used: 0,
        ownership_type: 'individual',
      };
    };

    const saveInvestment = () => {
      if (
        !currentInvestment.value.institution ||
        !currentInvestment.value.account_type ||
        !currentInvestment.value.current_value
      ) {
        error.value = 'Please fill in all required investment fields';
        return;
      }

      error.value = null;

      if (editingInvestmentIndex.value !== null) {
        investments.value[editingInvestmentIndex.value] = { ...currentInvestment.value };
      } else {
        investments.value.push({ ...currentInvestment.value });
      }

      showInvestmentForm.value = false;
      resetCurrentInvestment();
      updateTabCounts();
    };

    const editInvestment = (index) => {
      editingInvestmentIndex.value = index;
      currentInvestment.value = { ...investments.value[index] };
      showInvestmentForm.value = true;
    };

    const removeInvestment = (index) => {
      if (confirm('Are you sure you want to remove this investment account?')) {
        investments.value.splice(index, 1);
        updateTabCounts();
      }
    };

    const cancelInvestmentForm = () => {
      showInvestmentForm.value = false;
      editingInvestmentIndex.value = null;
      resetCurrentInvestment();
      error.value = null;
    };

    // Savings methods
    const showAddSavingsForm = () => {
      showSavingsForm.value = true;
      editingSavingsIndex.value = null;
      resetCurrentSavings();
    };

    const resetCurrentSavings = () => {
      currentSavings.value = {
        institution: '',
        account_type: '',
        country: 'United Kingdom',
        current_balance: 0,
        interest_rate: 0,
        isa_allowance_used: 0,
        ownership_type: 'individual',
      };
    };

    const saveSavings = () => {
      if (
        !currentSavings.value.institution ||
        !currentSavings.value.account_type ||
        !currentSavings.value.current_balance
      ) {
        error.value = 'Please fill in all required savings fields';
        return;
      }

      error.value = null;

      if (editingSavingsIndex.value !== null) {
        savingsAccounts.value[editingSavingsIndex.value] = { ...currentSavings.value };
      } else {
        savingsAccounts.value.push({ ...currentSavings.value });
      }

      showSavingsForm.value = false;
      resetCurrentSavings();
      updateTabCounts();
    };

    const editSavings = (index) => {
      editingSavingsIndex.value = index;
      currentSavings.value = { ...savingsAccounts.value[index] };
      showSavingsForm.value = true;
    };

    const removeSavings = (index) => {
      if (confirm('Are you sure you want to remove this savings account?')) {
        savingsAccounts.value.splice(index, 1);
        updateTabCounts();
      }
    };

    const cancelSavingsForm = () => {
      showSavingsForm.value = false;
      editingSavingsIndex.value = null;
      resetCurrentSavings();
      error.value = null;
    };

    // Update tab counts
    const updateTabCounts = () => {
      assetTabs.value[0].count = properties.value.length;
      assetTabs.value[1].count = investments.value.length;
      assetTabs.value[2].count = savingsAccounts.value.length;
    };

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'assets',
          data: {
            properties: properties.value,
            investments: investments.value,
            cash: savingsAccounts.value,
          },
        });

        emit('next');
      } catch (err) {
        error.value = err.message || 'Failed to save. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const handleBack = () => {
      emit('back');
    };

    const handleSkip = () => {
      emit('skip', 'assets');
    };

    onMounted(async () => {
      const existingData = await store.dispatch('onboarding/fetchStepData', 'assets');
      if (existingData) {
        if (existingData.properties && Array.isArray(existingData.properties)) {
          properties.value = existingData.properties;
        }
        if (existingData.investments && Array.isArray(existingData.investments)) {
          investments.value = existingData.investments;
        }
        // Support both 'cash' (new) and 'savings' (legacy)
        if (existingData.cash && Array.isArray(existingData.cash)) {
          savingsAccounts.value = existingData.cash;
        } else if (existingData.savings && Array.isArray(existingData.savings)) {
          savingsAccounts.value = existingData.savings;
        }
        updateTabCounts();
      }
    });

    return {
      activeTab,
      assetTabs,
      // Properties
      properties,
      showForm,
      editingIndex,
      currentProperty,
      showAddForm,
      saveProperty,
      editProperty,
      removeProperty,
      cancelForm,
      // Investments
      investments,
      showInvestmentForm,
      editingInvestmentIndex,
      currentInvestment,
      showAddInvestmentForm,
      saveInvestment,
      editInvestment,
      removeInvestment,
      cancelInvestmentForm,
      // Savings
      savingsAccounts,
      showSavingsForm,
      editingSavingsIndex,
      currentSavings,
      showAddSavingsForm,
      saveSavings,
      editSavings,
      removeSavings,
      cancelSavingsForm,
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
