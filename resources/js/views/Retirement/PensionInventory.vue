<template>
  <div class="pension-inventory">
    <!-- Header with Add Button -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Pension Inventory</h2>
        <p class="text-gray-600 mt-1">Manage all your pension arrangements</p>
      </div>
      <div class="relative">
        <button
          @click="showAddMenu = !showAddMenu"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Add Pension
        </button>
        <!-- Dropdown Menu -->
        <div
          v-if="showAddMenu"
          class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
        >
          <button
            @click="openAddForm('dc')"
            class="w-full text-left px-4 py-3 hover:bg-gray-50 border-b border-gray-200 transition-colors duration-150"
          >
            <p class="font-medium text-gray-900">DC Pension</p>
            <p class="text-xs text-gray-500">Defined Contribution</p>
          </button>
          <button
            @click="openAddForm('db')"
            class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors duration-150"
          >
            <p class="font-medium text-gray-900">DB Pension</p>
            <p class="text-xs text-gray-500">Defined Benefit</p>
          </button>
        </div>
      </div>
    </div>

    <!-- DC Pensions -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <div class="w-1 h-6 bg-blue-500 mr-3 rounded"></div>
        DC Pensions (Defined Contribution)
      </h3>
      <div v-if="dcPensions.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <PensionCard
          v-for="pension in dcPensions"
          :key="pension.id"
          :pension="pension"
          type="dc"
          @edit="editDCPension"
          @delete="deleteDCPension"
        />
      </div>
      <div v-else class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p class="text-gray-600">No DC pensions added yet</p>
        <button @click="openAddForm('dc')" class="text-indigo-600 hover:text-indigo-700 mt-2 font-medium">
          + Add your first DC pension
        </button>
      </div>
    </div>

    <!-- DB Pensions -->
    <div class="mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <div class="w-1 h-6 bg-purple-500 mr-3 rounded"></div>
        DB Pensions (Defined Benefit)
      </h3>
      <div v-if="dbPensions.length > 0">
        <!-- DB Pension Warning -->
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4 flex items-start">
          <svg class="w-5 h-5 text-amber-600 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">Important Notice</p>
            <p class="text-sm text-amber-800 mt-1">
              DB pension information is captured for income projection only. This system does not provide DB to DC transfer advice.
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <PensionCard
            v-for="pension in dbPensions"
            :key="pension.id"
            :pension="pension"
            type="db"
            @edit="editDBPension"
            @delete="deleteDBPension"
          />
        </div>
      </div>
      <div v-else class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-600">No DB pensions added yet</p>
        <button @click="openAddForm('db')" class="text-indigo-600 hover:text-indigo-700 mt-2 font-medium">
          + Add a DB pension
        </button>
      </div>
    </div>

    <!-- State Pension -->
    <div>
      <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <div class="w-1 h-6 bg-green-500 mr-3 rounded"></div>
        State Pension
      </h3>
      <div v-if="statePension" class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <p class="text-sm text-gray-600 mb-1">Forecast Weekly Amount</p>
            <p class="text-2xl font-bold text-gray-900">£{{ parseFloat(statePension.forecast_weekly_amount || 0).toFixed(2) }}</p>
            <p class="text-sm text-gray-500 mt-1">£{{ (parseFloat(statePension.forecast_weekly_amount || 0) * 52).toLocaleString() }}/year</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-1">Qualifying Years</p>
            <p class="text-2xl font-bold text-gray-900">{{ statePension.qualifying_years || 0 }}</p>
            <p class="text-sm text-gray-500 mt-1">of 35 years required</p>
          </div>
          <div>
            <p class="text-sm text-gray-600 mb-1">State Pension Age</p>
            <p class="text-2xl font-bold text-gray-900">{{ statePension.state_pension_age || 67 }}</p>
            <p class="text-sm text-gray-500 mt-1">years</p>
          </div>
        </div>
        <button
          @click="openStatePensionForm"
          class="mt-4 text-indigo-600 hover:text-indigo-700 font-medium text-sm"
        >
          Update State Pension Details
        </button>
      </div>
      <div v-else class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
        <p class="text-gray-600">No state pension information</p>
        <button @click="openStatePensionForm" class="text-indigo-600 hover:text-indigo-700 mt-2 font-medium">
          + Add state pension details
        </button>
      </div>
    </div>

    <!-- Modals -->
    <DCPensionForm
      v-if="showDCForm"
      :pension="selectedPension"
      :is-edit="isEditMode"
      @close="closeForms"
      @save="handleDCSave"
    />

    <DBPensionForm
      v-if="showDBForm"
      :pension="selectedPension"
      :is-edit="isEditMode"
      @close="closeForms"
      @save="handleDBSave"
    />

    <StatePensionForm
      v-if="showStatePensionForm"
      :state-pension="statePension"
      @close="closeForms"
      @save="handleStatePensionSave"
    />
  </div>
</template>

<script>
import { mapState } from 'vuex';
import PensionCard from '../../components/Retirement/PensionCard.vue';
import DCPensionForm from '../../components/Retirement/DCPensionForm.vue';
import DBPensionForm from '../../components/Retirement/DBPensionForm.vue';
import StatePensionForm from '../../components/Retirement/StatePensionForm.vue';

export default {
  name: 'PensionInventory',

  components: {
    PensionCard,
    DCPensionForm,
    DBPensionForm,
    StatePensionForm,
  },

  data() {
    return {
      showAddMenu: false,
      showDCForm: false,
      showDBForm: false,
      showStatePensionForm: false,
      selectedPension: null,
      isEditMode: false,
    };
  },

  computed: {
    ...mapState('retirement', ['dcPensions', 'dbPensions', 'statePension']),
  },

  methods: {
    openAddForm(type) {
      this.showAddMenu = false;
      this.isEditMode = false;
      this.selectedPension = null;

      if (type === 'dc') {
        this.showDCForm = true;
      } else if (type === 'db') {
        this.showDBForm = true;
      }
    },

    editDCPension(pension) {
      this.selectedPension = pension;
      this.isEditMode = true;
      this.showDCForm = true;
    },

    editDBPension(pension) {
      this.selectedPension = pension;
      this.isEditMode = true;
      this.showDBForm = true;
    },

    openStatePensionForm() {
      this.showStatePensionForm = true;
    },

    closeForms() {
      this.showDCForm = false;
      this.showDBForm = false;
      this.showStatePensionForm = false;
      this.selectedPension = null;
      this.isEditMode = false;
    },

    async handleDCSave(pensionData) {
      try {
        if (this.isEditMode) {
          await this.$store.dispatch('retirement/updateDCPension', {
            id: this.selectedPension.id,
            data: pensionData,
          });
        } else {
          await this.$store.dispatch('retirement/createDCPension', pensionData);
        }
        this.closeForms();
      } catch (error) {
        console.error('Failed to save DC pension:', error);
        alert('Failed to save pension. Please try again.');
      }
    },

    async handleDBSave(pensionData) {
      try {
        if (this.isEditMode) {
          await this.$store.dispatch('retirement/updateDBPension', {
            id: this.selectedPension.id,
            data: pensionData,
          });
        } else {
          await this.$store.dispatch('retirement/createDBPension', pensionData);
        }
        this.closeForms();
      } catch (error) {
        console.error('Failed to save DB pension:', error);
        alert('Failed to save pension. Please try again.');
      }
    },

    async handleStatePensionSave(data) {
      try {
        await this.$store.dispatch('retirement/updateStatePension', data);
        this.closeForms();
      } catch (error) {
        console.error('Failed to update state pension:', error);
        alert('Failed to update state pension. Please try again.');
      }
    },

    async deleteDCPension(pensionId) {
      if (confirm('Are you sure you want to delete this DC pension? This action cannot be undone.')) {
        try {
          await this.$store.dispatch('retirement/deleteDCPension', pensionId);
        } catch (error) {
          console.error('Failed to delete DC pension:', error);
          alert('Failed to delete pension. Please try again.');
        }
      }
    },

    async deleteDBPension(pensionId) {
      if (confirm('Are you sure you want to delete this DB pension? This action cannot be undone.')) {
        try {
          await this.$store.dispatch('retirement/deleteDBPension', pensionId);
        } catch (error) {
          console.error('Failed to delete DB pension:', error);
          alert('Failed to delete pension. Please try again.');
        }
      }
    },
  },

  mounted() {
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.showAddMenu = false;
      }
    });
  },
};
</script>

<style scoped>
/* Animations */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.pension-inventory > div {
  animation: slideIn 0.3s ease-out;
}
</style>
