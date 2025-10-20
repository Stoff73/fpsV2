<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
        <div class="flex items-center justify-between">
          <h3 class="text-2xl font-semibold text-gray-900">
            {{ isEditMode ? 'Edit Property' : 'Add Property' }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Progress Indicator -->
        <div class="mt-4">
          <div class="flex items-center justify-between">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="flex-1 flex flex-col items-center"
            >
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors"
                :class="
                  currentStep === index + 1
                    ? 'bg-blue-600 border-blue-600 text-white'
                    : currentStep > index + 1
                    ? 'bg-green-600 border-green-600 text-white'
                    : 'bg-white border-gray-300 text-gray-400'
                "
              >
                {{ index + 1 }}
              </div>
              <span class="text-xs mt-1 text-center" :class="currentStep === index + 1 ? 'text-blue-600 font-semibold' : 'text-gray-500'">
                {{ step }}
              </span>
              <div
                v-if="index < steps.length - 1"
                class="absolute h-0.5 w-full top-5 left-1/2 -z-10"
                :class="currentStep > index + 1 ? 'bg-green-600' : 'bg-gray-300'"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit">
        <div class="px-6 py-4">
          <!-- Step 1: Basic Information -->
          <div v-show="currentStep === 1" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h4>

            <div>
              <label for="property_type" class="block text-sm font-medium text-gray-700 mb-1">Property Type <span class="text-red-500">*</span></label>
              <select
                id="property_type"
                v-model="form.property_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select property type</option>
                <option value="main_residence">Main Residence</option>
                <option value="second_home">Second Home</option>
                <option value="buy_to_let">Buy to Let</option>
                <option value="commercial">Commercial</option>
                <option value="land">Land</option>
              </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">Address Line 1 <span class="text-red-500">*</span></label>
                <input
                  id="address_line_1"
                  v-model="form.address_line_1"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                <input
                  id="address_line_2"
                  v-model="form.address_line_2"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City <span class="text-red-500">*</span></label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="county" class="block text-sm font-medium text-gray-700 mb-1">County</label>
                <input
                  id="county"
                  v-model="form.county"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="postcode" class="block text-sm font-medium text-gray-700 mb-1">Postcode <span class="text-red-500">*</span></label>
                <input
                  id="postcode"
                  v-model="form.postcode"
                  type="text"
                  required
                  pattern="^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                  placeholder="SW1A 1AA"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="purchase_date" class="block text-sm font-medium text-gray-700 mb-1">Purchase Date <span class="text-red-500">*</span></label>
                <input
                  id="purchase_date"
                  v-model="form.purchase_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-1">Purchase Price (£) <span class="text-red-500">*</span></label>
                <input
                  id="purchase_price"
                  v-model.number="form.purchase_price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="current_value" class="block text-sm font-medium text-gray-700 mb-1">Current Value (£) <span class="text-red-500">*</span></label>
                <input
                  id="current_value"
                  v-model.number="form.current_value"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="valuation_date" class="block text-sm font-medium text-gray-700 mb-1">Valuation Date</label>
                <input
                  id="valuation_date"
                  v-model="form.valuation_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Step 2: Ownership -->
          <div v-show="currentStep === 2" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Ownership</h4>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ownership Type <span class="text-red-500">*</span></label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.ownership_type"
                    value="sole"
                    required
                    class="mr-2"
                  />
                  <span>Sole Ownership</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.ownership_type"
                    value="joint"
                    required
                    class="mr-2"
                  />
                  <span>Joint Ownership</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="radio"
                    v-model="form.ownership_type"
                    value="trust"
                    required
                    class="mr-2"
                  />
                  <span>Held in Trust</span>
                </label>
              </div>
            </div>

            <div>
              <label for="ownership_percentage" class="block text-sm font-medium text-gray-700 mb-1">
                Ownership Percentage (%) <span class="text-red-500">*</span>
              </label>
              <input
                id="ownership_percentage"
                v-model.number="form.ownership_percentage"
                type="number"
                step="0.01"
                min="0"
                max="100"
                required
                :disabled="form.ownership_type === 'sole'"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100"
              />
              <p class="text-sm text-gray-500 mt-1">For sole ownership, this is automatically set to 100%</p>
            </div>

            <div v-if="form.ownership_type === 'joint'">
              <label for="household_id" class="block text-sm font-medium text-gray-700 mb-1">Household (Joint Owner)</label>
              <select
                id="household_id"
                v-model="form.household_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select household</option>
                <!-- Households would be loaded dynamically -->
              </select>
            </div>

            <div v-if="form.ownership_type === 'trust'">
              <label for="trust_id" class="block text-sm font-medium text-gray-700 mb-1">Trust</label>
              <select
                id="trust_id"
                v-model="form.trust_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select trust</option>
                <!-- Trusts would be loaded dynamically -->
              </select>
            </div>
          </div>

          <!-- Step 3: Mortgage (Optional) -->
          <div v-show="currentStep === 3" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Mortgage (Optional)</h4>

            <div>
              <label class="flex items-center">
                <input
                  type="checkbox"
                  v-model="hasMortgage"
                  class="mr-2"
                />
                <span class="text-sm font-medium text-gray-700">This property has a mortgage</span>
              </label>
            </div>

            <div v-if="hasMortgage" class="space-y-4 mt-4 p-4 bg-gray-50 rounded-md">
              <p class="text-sm text-gray-600 italic">Note: You can add detailed mortgage information after creating the property.</p>

              <div>
                <label for="mortgage_lender" class="block text-sm font-medium text-gray-700 mb-1">Lender Name</label>
                <input
                  id="mortgage_lender"
                  v-model="mortgageForm.lender_name"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="mortgage_balance" class="block text-sm font-medium text-gray-700 mb-1">Outstanding Balance (£)</label>
                <input
                  id="mortgage_balance"
                  v-model.number="mortgageForm.outstanding_balance"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Step 4: Costs -->
          <div v-show="currentStep === 4" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Annual Costs</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="annual_service_charge" class="block text-sm font-medium text-gray-700 mb-1">Annual Service Charge (£)</label>
                <input
                  id="annual_service_charge"
                  v-model.number="form.annual_service_charge"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="annual_ground_rent" class="block text-sm font-medium text-gray-700 mb-1">Annual Ground Rent (£)</label>
                <input
                  id="annual_ground_rent"
                  v-model.number="form.annual_ground_rent"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="annual_insurance" class="block text-sm font-medium text-gray-700 mb-1">Annual Insurance (£)</label>
                <input
                  id="annual_insurance"
                  v-model.number="form.annual_insurance"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="annual_maintenance_reserve" class="block text-sm font-medium text-gray-700 mb-1">Annual Maintenance Reserve (£)</label>
                <input
                  id="annual_maintenance_reserve"
                  v-model.number="form.annual_maintenance_reserve"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="other_annual_costs" class="block text-sm font-medium text-gray-700 mb-1">Other Annual Costs (£)</label>
                <input
                  id="other_annual_costs"
                  v-model.number="form.other_annual_costs"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="sdlt_paid" class="block text-sm font-medium text-gray-700 mb-1">SDLT Paid (£)</label>
                <input
                  id="sdlt_paid"
                  v-model.number="form.sdlt_paid"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Step 5: BTL Details (Conditional) -->
          <div v-show="currentStep === 5" class="space-y-4">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Buy to Let Details</h4>

            <div v-if="form.property_type === 'buy_to_let'" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="monthly_rental_income" class="block text-sm font-medium text-gray-700 mb-1">Monthly Rental Income (£)</label>
                  <input
                    id="monthly_rental_income"
                    v-model.number="form.monthly_rental_income"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label for="annual_rental_income" class="block text-sm font-medium text-gray-700 mb-1">Annual Rental Income (£)</label>
                  <input
                    id="annual_rental_income"
                    v-model.number="form.annual_rental_income"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label for="occupancy_rate_percent" class="block text-sm font-medium text-gray-700 mb-1">Occupancy Rate (%)</label>
                  <input
                    id="occupancy_rate_percent"
                    v-model.number="form.occupancy_rate_percent"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label for="tenant_name" class="block text-sm font-medium text-gray-700 mb-1">Tenant Name</label>
                  <input
                    id="tenant_name"
                    v-model="form.tenant_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label for="lease_start_date" class="block text-sm font-medium text-gray-700 mb-1">Lease Start Date</label>
                  <input
                    id="lease_start_date"
                    v-model="form.lease_start_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label for="lease_end_date" class="block text-sm font-medium text-gray-700 mb-1">Lease End Date</label>
                  <input
                    id="lease_end_date"
                    v-model="form.lease_end_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500">
              <p>This section is only applicable for Buy to Let properties.</p>
              <p class="text-sm mt-2">Click "Save Property" to complete.</p>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-between rounded-b-lg">
          <button
            type="button"
            @click="previousStep"
            v-show="currentStep > 1"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
          >
            Previous
          </button>

          <div class="flex space-x-2 ml-auto">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>

            <button
              v-if="currentStep < 5"
              type="button"
              @click="nextStep"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Next
            </button>

            <button
              v-else
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ submitting ? 'Saving...' : 'Save Property' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PropertyForm',

  props: {
    property: {
      type: Object,
      default: null,
    },
  },

  data() {
    return {
      currentStep: 1,
      steps: ['Basic Info', 'Ownership', 'Mortgage', 'Costs', 'BTL Details'],
      hasMortgage: false,
      form: {
        property_type: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        county: '',
        postcode: '',
        purchase_date: '',
        purchase_price: null,
        current_value: null,
        valuation_date: '',
        ownership_type: 'sole',
        ownership_percentage: 100,
        household_id: null,
        trust_id: null,
        annual_service_charge: null,
        annual_ground_rent: null,
        annual_insurance: null,
        annual_maintenance_reserve: null,
        other_annual_costs: null,
        sdlt_paid: null,
        monthly_rental_income: null,
        annual_rental_income: null,
        occupancy_rate_percent: null,
        tenant_name: '',
        lease_start_date: '',
        lease_end_date: '',
      },
      mortgageForm: {
        lender_name: '',
        outstanding_balance: null,
      },
      submitting: false,
      error: null,
    };
  },

  computed: {
    isEditMode() {
      return this.property !== null;
    },
  },

  watch: {
    'form.ownership_type'(newVal) {
      if (newVal === 'sole') {
        this.form.ownership_percentage = 100;
      }
    },
  },

  mounted() {
    if (this.property) {
      this.populateForm();
    }
  },

  methods: {
    populateForm() {
      // Direct top-level fields
      this.form.property_type = this.property.property_type || '';
      this.form.ownership_type = this.property.ownership_type || 'sole';
      this.form.ownership_percentage = this.property.ownership_percentage || 100;
      this.form.household_id = this.property.household_id || null;
      this.form.trust_id = this.property.trust_id || null;
      this.form.current_value = this.property.current_value || null;
      this.form.purchase_price = this.property.purchase_price || null;

      // Address fields (may be nested or top-level)
      this.form.address_line_1 = this.property.address_line_1 || this.property.address?.line_1 || '';
      this.form.address_line_2 = this.property.address_line_2 || this.property.address?.line_2 || '';
      this.form.city = this.property.city || this.property.address?.city || '';
      this.form.county = this.property.county || this.property.address?.county || '';
      this.form.postcode = this.property.postcode || this.property.address?.postcode || '';

      // Valuation fields (may be nested or top-level)
      this.form.purchase_date = this.property.purchase_date || this.property.valuation?.purchase_date || '';
      this.form.valuation_date = this.property.valuation_date || this.property.valuation?.valuation_date || '';

      // Cost fields (may be nested or top-level)
      this.form.annual_service_charge = this.property.annual_service_charge || this.property.costs?.annual_service_charge || null;
      this.form.annual_ground_rent = this.property.annual_ground_rent || this.property.costs?.annual_ground_rent || null;
      this.form.annual_insurance = this.property.annual_insurance || this.property.costs?.annual_insurance || null;
      this.form.annual_maintenance_reserve = this.property.annual_maintenance_reserve || this.property.costs?.annual_maintenance_reserve || null;
      this.form.other_annual_costs = this.property.other_annual_costs || this.property.costs?.other_annual_costs || null;
      this.form.sdlt_paid = this.property.sdlt_paid || null;

      // Rental fields (may be nested or top-level)
      this.form.monthly_rental_income = this.property.monthly_rental_income || this.property.rental?.monthly_rental_income || null;
      this.form.annual_rental_income = this.property.annual_rental_income || this.property.rental?.annual_rental_income || null;
      this.form.occupancy_rate_percent = this.property.occupancy_rate_percent || this.property.rental?.occupancy_rate_percent || null;
      this.form.tenant_name = this.property.tenant_name || this.property.rental?.tenant_name || '';
      this.form.lease_start_date = this.property.lease_start_date || this.property.rental?.lease_start_date || '';
      this.form.lease_end_date = this.property.lease_end_date || this.property.rental?.lease_end_date || '';
    },

    nextStep() {
      if (this.currentStep < 5) {
        this.currentStep++;
      }
    },

    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },

    validateForm() {
      // Basic validation
      if (!this.form.property_type || !this.form.address_line_1 || !this.form.city || !this.form.postcode) {
        this.error = 'Please fill in all required fields.';
        return false;
      }

      if (!this.form.purchase_date || !this.form.purchase_price || !this.form.current_value) {
        this.error = 'Please fill in all required financial fields.';
        return false;
      }

      if (!this.form.ownership_type || !this.form.ownership_percentage) {
        this.error = 'Please fill in ownership details.';
        return false;
      }

      this.error = null;
      return true;
    },

    async handleSubmit() {
      if (!this.validateForm()) {
        return;
      }

      this.submitting = true;
      this.error = null;

      // Emit 'save' event (NOT 'submit' - see CLAUDE.md)
      this.$emit('save', {
        property: this.form,
        mortgage: this.hasMortgage ? this.mortgageForm : null,
      });
    },
  },
};
</script>
