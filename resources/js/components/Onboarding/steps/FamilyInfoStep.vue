<template>
  <OnboardingStep
    title="Family & Dependents"
    description="Add details about your family members and dependents"
    :can-go-back="true"
    :can-skip="true"
    :loading="loading"
    :error="error"
    @next="handleNext"
    @back="handleBack"
    @skip="handleSkip"
  >
    <div class="space-y-6">
      <p class="text-body-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
        This information helps us understand your family structure for estate planning and protection needs analysis.
      </p>

      <!-- Family Member Form -->
      <div v-if="showForm" class="border border-gray-200 rounded-lg p-4 bg-white">
        <h4 class="text-body font-medium text-gray-900 mb-4">
          {{ editingIndex !== null ? 'Edit Family Member' : 'Add Family Member' }}
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="member_name" class="label">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              id="member_name"
              v-model="currentMember.name"
              type="text"
              class="input-field"
              placeholder="Enter full name"
              required
            >
          </div>

          <div>
            <label for="member_relationship" class="label">
              Relationship <span class="text-red-500">*</span>
            </label>
            <select
              id="member_relationship"
              v-model="currentMember.relationship"
              class="input-field"
              required
            >
              <option value="">Select relationship</option>
              <option value="spouse">Spouse</option>
              <option value="child">Child</option>
              <option value="parent">Parent</option>
              <option value="sibling">Sibling</option>
              <option value="grandchild">Grandchild</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label for="member_dob" class="label">
              Date of Birth <span class="text-red-500">*</span>
            </label>
            <input
              id="member_dob"
              v-model="currentMember.date_of_birth"
              type="date"
              class="input-field"
              :max="today"
              required
            >
            <p v-if="currentMemberAge !== null" class="mt-1 text-body-sm text-gray-500">
              Age: {{ currentMemberAge }} years
            </p>
          </div>

          <div>
            <label class="label">
              Is this person financially dependent on you?
            </label>
            <div class="mt-2 space-x-4">
              <label class="inline-flex items-center">
                <input
                  v-model="currentMember.is_dependent"
                  type="radio"
                  :value="true"
                  class="form-radio text-primary-600"
                >
                <span class="ml-2 text-body text-gray-700">Yes</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  v-model="currentMember.is_dependent"
                  type="radio"
                  :value="false"
                  class="form-radio text-primary-600"
                >
                <span class="ml-2 text-body text-gray-700">No</span>
              </label>
            </div>
          </div>
        </div>

        <div class="flex gap-3 mt-4">
          <button
            type="button"
            class="btn-primary"
            @click="saveMember"
          >
            {{ editingIndex !== null ? 'Update Member' : 'Add Member' }}
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

      <!-- Added Family Members List -->
      <div v-if="familyMembers.length > 0" class="space-y-3">
        <h4 class="text-body font-medium text-gray-900">
          Family Members ({{ familyMembers.length }})
        </h4>

        <div
          v-for="(member, index) in familyMembers"
          :key="index"
          class="border border-gray-200 rounded-lg p-4 bg-gray-50"
        >
          <div class="flex justify-between items-start">
            <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3">
              <div>
                <p class="text-body-sm text-gray-600">Name</p>
                <p class="text-body font-medium text-gray-900">{{ member.name }}</p>
              </div>
              <div>
                <p class="text-body-sm text-gray-600">Relationship</p>
                <p class="text-body text-gray-900 capitalize">{{ member.relationship }}</p>
              </div>
              <div>
                <p class="text-body-sm text-gray-600">Age</p>
                <p class="text-body text-gray-900">{{ calculateAge(member.date_of_birth) }} years</p>
              </div>
              <div>
                <p class="text-body-sm text-gray-600">Dependent</p>
                <p class="text-body text-gray-900">{{ member.is_dependent ? 'Yes' : 'No' }}</p>
              </div>
            </div>
            <div class="flex gap-2 ml-4">
              <button
                type="button"
                class="text-primary-600 hover:text-primary-700 text-body-sm"
                @click="editMember(index)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-red-600 hover:text-red-700 text-body-sm"
                @click="removeMember(index)"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Member Button -->
      <div v-if="!showForm">
        <button
          type="button"
          class="btn-secondary w-full md:w-auto"
          @click="showAddForm"
        >
          + Add Family Member
        </button>
      </div>

      <!-- Charitable Bequest -->
      <div class="border-t pt-6">
        <label class="label">
          Do you wish to leave anything to charity?
        </label>
        <div class="mt-2 space-x-4">
          <label class="inline-flex items-center">
            <input
              v-model="charitableBequest"
              type="radio"
              :value="true"
              class="form-radio text-primary-600"
            >
            <span class="ml-2 text-body text-gray-700">Yes</span>
          </label>
          <label class="inline-flex items-center">
            <input
              v-model="charitableBequest"
              type="radio"
              :value="false"
              class="form-radio text-primary-600"
            >
            <span class="ml-2 text-body text-gray-700">No</span>
          </label>
        </div>
        <p class="mt-1 text-body-sm text-gray-500">
          Leaving 10% or more to charity can reduce your IHT rate from 40% to 36%
        </p>
      </div>
    </div>
  </OnboardingStep>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import OnboardingStep from '../OnboardingStep.vue';

export default {
  name: 'FamilyInfoStep',

  components: {
    OnboardingStep,
  },

  emits: ['next', 'back', 'skip'],

  setup(props, { emit }) {
    const store = useStore();

    const familyMembers = ref([]);
    const charitableBequest = ref(null);
    const showForm = ref(false);
    const editingIndex = ref(null);
    const currentMember = ref({
      name: '',
      relationship: '',
      date_of_birth: '',
      is_dependent: false,
    });

    const loading = ref(false);
    const error = ref(null);

    const today = computed(() => {
      return new Date().toISOString().split('T')[0];
    });

    const currentMemberAge = computed(() => {
      if (!currentMember.value.date_of_birth) return null;
      return calculateAge(currentMember.value.date_of_birth);
    });

    const calculateAge = (dateOfBirth) => {
      if (!dateOfBirth) return 0;
      const today = new Date();
      const birthDate = new Date(dateOfBirth);
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      return age;
    };

    const showAddForm = () => {
      showForm.value = true;
      editingIndex.value = null;
      resetCurrentMember();
    };

    const resetCurrentMember = () => {
      currentMember.value = {
        name: '',
        relationship: '',
        date_of_birth: '',
        is_dependent: false,
      };
    };

    const saveMember = () => {
      // Validation
      if (!currentMember.value.name || !currentMember.value.relationship || !currentMember.value.date_of_birth) {
        error.value = 'Please fill in all required fields';
        return;
      }

      error.value = null;

      if (editingIndex.value !== null) {
        // Update existing member
        familyMembers.value[editingIndex.value] = { ...currentMember.value };
      } else {
        // Add new member
        familyMembers.value.push({ ...currentMember.value });
      }

      showForm.value = false;
      resetCurrentMember();
    };

    const editMember = (index) => {
      editingIndex.value = index;
      currentMember.value = { ...familyMembers.value[index] };
      showForm.value = true;
    };

    const removeMember = (index) => {
      if (confirm('Are you sure you want to remove this family member?')) {
        familyMembers.value.splice(index, 1);
      }
    };

    const cancelForm = () => {
      showForm.value = false;
      editingIndex.value = null;
      resetCurrentMember();
      error.value = null;
    };

    const handleNext = async () => {
      loading.value = true;
      error.value = null;

      try {
        await store.dispatch('onboarding/saveStepData', {
          stepName: 'family_info',
          data: {
            family_members: familyMembers.value,
            charitable_bequest: charitableBequest.value,
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
      emit('skip', 'family_info');
    };

    onMounted(async () => {
      const existingData = await store.dispatch('onboarding/fetchStepData', 'family_info');
      if (existingData) {
        if (existingData.family_members && Array.isArray(existingData.family_members)) {
          familyMembers.value = existingData.family_members;
        }
        if (existingData.charitable_bequest !== undefined) {
          charitableBequest.value = existingData.charitable_bequest;
        }
      }
    });

    return {
      familyMembers,
      charitableBequest,
      showForm,
      editingIndex,
      currentMember,
      loading,
      error,
      today,
      currentMemberAge,
      calculateAge,
      showAddForm,
      saveMember,
      editMember,
      removeMember,
      cancelForm,
      handleNext,
      handleBack,
      handleSkip,
    };
  },
};
</script>
