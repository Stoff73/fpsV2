<template>
  <div class="space-y-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <p class="text-body-sm text-blue-800">
        <strong>Why this matters:</strong> Health and lifestyle information helps us provide accurate protection recommendations and estimate insurance premium costs.
      </p>
    </div>

    <!-- Display Mode -->
    <div v-if="!isEditing" class="bg-white rounded-lg border border-gray-200 p-6">
      <div class="flex justify-between items-start mb-6">
        <h3 class="text-h4 font-semibold text-gray-900">Health & Lifestyle</h3>
        <button
          @click="startEditing"
          class="btn-secondary"
        >
          Edit
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <dt class="text-body-sm font-medium text-gray-500">Health Status</dt>
          <dd class="mt-1 text-body-base text-gray-900">
            {{ displayData.good_health ? 'Good Health' : 'Pre-existing Conditions' }}
          </dd>
        </div>

        <div>
          <dt class="text-body-sm font-medium text-gray-500">Smoker Status</dt>
          <dd class="mt-1 text-body-base text-gray-900">
            {{ displayData.smoker ? 'Smoker' : 'Non-Smoker' }}
          </dd>
        </div>
      </div>
    </div>

    <!-- Edit Mode -->
    <div v-else class="bg-white rounded-lg border border-gray-200 p-6">
      <h3 class="text-h4 font-semibold text-gray-900 mb-6">Edit Health & Lifestyle</h3>

      <form @submit.prevent="saveChanges" class="space-y-6">
        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
          <p class="text-body-sm text-red-800">{{ error }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Good Health -->
          <div>
            <label for="good_health" class="label">
              Are you in good health? <span class="text-red-500">*</span>
            </label>
            <select
              id="good_health"
              v-model="formData.good_health"
              class="input-field"
              required
            >
              <option value="">Select...</option>
              <option :value="true">Yes</option>
              <option :value="false">No (pre-existing conditions)</option>
            </select>
            <p class="mt-1 text-body-sm text-gray-500">
              Affects protection insurance premiums
            </p>
          </div>

          <!-- Smoker Status -->
          <div>
            <label for="smoker" class="label">
              Do you smoke? <span class="text-red-500">*</span>
            </label>
            <select
              id="smoker"
              v-model="formData.smoker"
              class="input-field"
              required
            >
              <option value="">Select...</option>
              <option :value="false">No</option>
              <option :value="true">Yes</option>
            </select>
            <p class="mt-1 text-body-sm text-gray-500">
              Significantly impacts insurance premiums
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button
            type="button"
            @click="cancelEditing"
            class="btn-secondary"
            :disabled="saving"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="btn-primary"
            :disabled="saving"
          >
            <span v-if="saving">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { useStore } from 'vuex';
import userProfileService from '@/services/userProfileService';

export default {
  name: 'HealthInformation',

  setup() {
    const store = useStore();
    const isEditing = ref(false);
    const saving = ref(false);
    const error = ref(null);

    const user = computed(() => store.getters['auth/currentUser']);

    const displayData = computed(() => ({
      good_health: user.value?.good_health ?? true,
      smoker: user.value?.smoker ?? false,
    }));

    const formData = ref({
      good_health: true,
      smoker: false,
    });

    // Watch for user changes and update form data
    watch(user, (newUser) => {
      if (newUser) {
        formData.value = {
          good_health: newUser.good_health ?? true,
          smoker: newUser.smoker ?? false,
        };
      }
    }, { immediate: true });

    const startEditing = () => {
      formData.value = {
        good_health: displayData.value.good_health,
        smoker: displayData.value.smoker,
      };
      error.value = null;
      isEditing.value = true;
    };

    const cancelEditing = () => {
      isEditing.value = false;
      error.value = null;
    };

    const saveChanges = async () => {
      error.value = null;
      saving.value = true;

      try {
        await userProfileService.updatePersonalInfo(formData.value);

        // Update the user in the store
        await store.dispatch('auth/fetchUser');

        isEditing.value = false;
      } catch (err) {
        console.error('Failed to save health information:', err);
        error.value = err.response?.data?.message || 'Failed to save health information. Please try again.';
      } finally {
        saving.value = false;
      }
    };

    return {
      isEditing,
      saving,
      error,
      displayData,
      formData,
      startEditing,
      cancelEditing,
      saveChanges,
    };
  },
};
</script>
