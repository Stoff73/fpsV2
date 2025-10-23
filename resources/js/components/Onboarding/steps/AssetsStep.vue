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

      <!-- Property Form -->
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
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'AssetsStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const properties = ref([]);
    const showForm = ref(false);
    const editingIndex = ref(null);
    const currentProperty = ref({
      property_type: '',
      address_line_1: '',
      address_line_2: '',
      city: '',
      postcode: '',
      current_value: 0,
      outstanding_mortgage: 0,
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
        current_value: 0,
        outstanding_mortgage: 0,
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
    };

    const editProperty = (index) => {
      editingIndex.value = index;
      currentProperty.value = { ...properties.value[index] };
      showForm.value = true;
    };

    const removeProperty = (index) => {
      if (confirm('Are you sure you want to remove this property?')) {
        properties.value.splice(index, 1);
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingIndex.value = null;
      resetCurrentProperty();
      error.value = null;
    };

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'assets',
          data: {
            properties: properties.value,
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
      if (existingData && existingData.properties && Array.isArray(existingData.properties)) {
        properties.value = existingData.properties;
      }
    });

    return {
      properties,
      showForm,
      editingIndex,
      currentProperty,
      loading,
      error,
      showAddForm,
      saveProperty,
      editProperty,
      removeProperty,
      cancelForm,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
