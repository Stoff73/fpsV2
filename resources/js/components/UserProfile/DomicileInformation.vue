<template>
  <div>
    <div class="mb-6">
      <h2 class="text-h4 font-semibold text-gray-900">Domicile Information</h2>
      <p class="mt-1 text-body-sm text-gray-600">
        Your domicile status affects UK inheritance tax on your worldwide assets
      </p>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="rounded-md bg-success-50 p-4 mb-6">
      <div class="flex">
        <div class="ml-3">
          <p class="text-body-sm font-medium text-success-800">
            {{ successMessage }}
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="rounded-md bg-error-50 p-4 mb-6">
      <div class="flex">
        <div class="ml-3">
          <h3 class="text-body-sm font-medium text-error-800">Error updating domicile information</h3>
          <div class="mt-2 text-body-sm text-error-700">
            <p>{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Domicile Status -->
      <div>
        <label for="domicile_status" class="block text-body-sm font-medium text-gray-700 mb-1">
          Domicile Status <span class="text-error-600">*</span>
        </label>
        <select
          id="domicile_status"
          v-model="form.domicile_status"
          required
          class="form-select"
          :disabled="!isEditing"
        >
          <option value="">Select domicile status</option>
          <option value="uk_domiciled">UK Domiciled</option>
          <option value="non_uk_domiciled">Non-UK Domiciled</option>
        </select>
        <p class="mt-1 text-body-xs text-gray-500">
          UK domiciled individuals have worldwide assets subject to UK IHT
        </p>
      </div>

      <!-- Country of Birth -->
      <div>
        <CountrySelector
          v-model="form.country_of_birth"
          label="Country of Birth"
          :required="true"
          :disabled="!isEditing"
          placeholder="Search for your country of birth..."
        />
      </div>

      <!-- UK Arrival Date (conditional) -->
      <div v-if="form.domicile_status === 'non_uk_domiciled'">
        <label for="uk_arrival_date" class="block text-body-sm font-medium text-gray-700 mb-1">
          UK Arrival Date <span class="text-error-600">*</span>
        </label>
        <input
          id="uk_arrival_date"
          v-model="form.uk_arrival_date"
          type="date"
          :required="form.domicile_status === 'non_uk_domiciled'"
          class="form-input"
          :disabled="!isEditing"
          :max="today"
        />
        <p class="mt-1 text-body-xs text-gray-500">
          When did you first arrive in the UK?
        </p>
      </div>

      <!-- Calculated Information (Display Only) -->
      <div v-if="domicileInfo && form.domicile_status" class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <h3 class="text-body-sm font-semibold text-blue-900 mb-2">Your Domicile Status</h3>

        <div class="space-y-2 text-body-sm text-blue-800">
          <!-- Years UK Resident -->
          <div v-if="domicileInfo.years_uk_resident !== null" class="flex justify-between">
            <span class="font-medium">Years UK Resident:</span>
            <span>{{ domicileInfo.years_uk_resident }} years</span>
          </div>

          <!-- Deemed Domicile Status -->
          <div class="flex justify-between">
            <span class="font-medium">Deemed Domiciled:</span>
            <span :class="domicileInfo.is_deemed_domiciled ? 'text-green-700 font-semibold' : 'text-gray-700'">
              {{ domicileInfo.is_deemed_domiciled ? 'Yes' : 'No' }}
            </span>
          </div>

          <!-- Deemed Domicile Date -->
          <div v-if="domicileInfo.deemed_domicile_date" class="flex justify-between">
            <span class="font-medium">Deemed Domicile Date:</span>
            <span>{{ formatDate(domicileInfo.deemed_domicile_date) }}</span>
          </div>

          <!-- Explanation -->
          <div class="mt-3 pt-3 border-t border-blue-300">
            <p class="text-body-xs italic">
              {{ domicileInfo.explanation }}
            </p>
          </div>

          <!-- Additional Info for Non-Deemed -->
          <div v-if="!domicileInfo.is_deemed_domiciled && form.domicile_status === 'non_uk_domiciled'" class="mt-3 pt-3 border-t border-blue-300">
            <h4 class="text-body-xs font-semibold mb-1">Deemed Domicile Rule (15 of 20 years)</h4>
            <p class="text-body-xs">
              You will become deemed UK domiciled for IHT purposes after being UK resident for 15 years.
              This means your worldwide assets will become subject to UK inheritance tax.
            </p>
          </div>
        </div>
      </div>

      <!-- IHT Implications -->
      <div v-if="form.domicile_status" class="bg-gray-50 border border-gray-200 rounded-md p-4">
        <h3 class="text-body-sm font-semibold text-gray-900 mb-2">Inheritance Tax Implications</h3>

        <div v-if="form.domicile_status === 'uk_domiciled' || (domicileInfo && domicileInfo.is_deemed_domiciled)" class="text-body-sm text-gray-700">
          <p class="mb-2">As a UK domiciled individual:</p>
          <ul class="list-disc list-inside space-y-1 text-body-xs">
            <li>Your <strong>worldwide assets</strong> are subject to UK inheritance tax</li>
            <li>Nil Rate Band: £325,000</li>
            <li>Residence Nil Rate Band: £175,000 (if applicable)</li>
            <li>Unlimited spouse exemption (if spouse is also UK domiciled)</li>
            <li>IHT rate: 40% on estate above allowances</li>
          </ul>
        </div>

        <div v-else-if="form.domicile_status === 'non_uk_domiciled'" class="text-body-sm text-gray-700">
          <p class="mb-2">As a non-UK domiciled individual:</p>
          <ul class="list-disc list-inside space-y-1 text-body-xs">
            <li>Only <strong>UK-situs assets</strong> are subject to UK inheritance tax</li>
            <li>UK-situs assets include: UK property, UK bank accounts, UK investments, UK business interests</li>
            <li>Foreign assets are NOT subject to UK IHT</li>
            <li>Nil Rate Band: £325,000</li>
            <li>Spouse exemption may be limited if spouse is non-UK domiciled</li>
          </ul>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3">
        <button
          v-if="isEditing"
          type="button"
          @click="cancelEdit"
          class="btn-secondary"
        >
          Cancel
        </button>
        <button
          v-if="!isEditing"
          type="button"
          @click="enableEdit"
          class="btn-primary"
        >
          Edit
        </button>
        <button
          v-if="isEditing"
          type="submit"
          :disabled="saving"
          class="btn-primary"
        >
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import CountrySelector from '@/components/Shared/CountrySelector.vue';
import api from '@/services/api';

export default {
  name: 'DomicileInformation',

  components: {
    CountrySelector,
  },

  props: {
    user: {
      type: [Object, null],
      default: null,
    },
    domicileInfo: {
      type: Object,
      default: null,
    },
  },

  emits: ['updated'],

  data() {
    return {
      isEditing: false,
      saving: false,
      successMessage: '',
      errorMessage: '',
      form: {
        domicile_status: this.user?.domicile_status || '',
        country_of_birth: this.user?.country_of_birth || '',
        uk_arrival_date: this.user?.uk_arrival_date || '',
      },
      originalForm: {},
    };
  },

  computed: {
    today() {
      return new Date().toISOString().split('T')[0];
    },
  },

  watch: {
    user: {
      handler(newUser) {
        if (!this.isEditing && newUser) {
          this.form = {
            domicile_status: newUser.domicile_status || '',
            country_of_birth: newUser.country_of_birth || '',
            uk_arrival_date: newUser.uk_arrival_date || '',
          };
        }
      },
      deep: true,
    },
  },

  methods: {
    enableEdit() {
      this.isEditing = true;
      this.originalForm = { ...this.form };
      this.successMessage = '';
      this.errorMessage = '';
    },

    cancelEdit() {
      this.form = { ...this.originalForm };
      this.isEditing = false;
      this.errorMessage = '';
    },

    async handleSubmit() {
      this.saving = true;
      this.errorMessage = '';
      this.successMessage = '';

      try {
        const response = await api.put('/user/profile/domicile', this.form);

        if (response.data.success) {
          this.successMessage = response.data.message;
          this.isEditing = false;

          // Emit updated event to parent
          this.$emit('updated', response.data.data.user);

          // Clear success message after 5 seconds
          setTimeout(() => {
            this.successMessage = '';
          }, 5000);
        }
      } catch (error) {
        console.error('Error updating domicile info:', error);

        if (error.response?.data?.errors) {
          const errors = error.response.data.errors;
          this.errorMessage = Object.values(errors).flat().join(' ');
        } else if (error.response?.data?.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = 'An unexpected error occurred. Please try again.';
        }
      } finally {
        this.saving = false;
      }
    },

    formatDate(dateString) {
      if (!dateString) return '';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      });
    },
  },
};
</script>
