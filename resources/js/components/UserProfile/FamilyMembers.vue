<template>
  <div>
    <div class="mb-6 flex justify-between items-start">
      <div>
        <h2 class="text-h4 font-semibold text-gray-900">Family Members</h2>
        <p class="mt-1 text-body-sm text-gray-600">
          Manage your family members and dependents
        </p>
      </div>
      <button
        @click="openAddModal"
        class="btn-primary"
      >
        Add Family Member
      </button>
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

    <!-- Family Members List -->
    <div v-if="familyMembers.length > 0" class="space-y-4">
      <div
        v-for="member in familyMembers"
        :key="member.id"
        class="card p-4"
      >
        <div class="flex justify-between items-start">
          <div class="flex-1">
            <div class="flex items-center space-x-3">
              <h3 class="text-h5 font-semibold text-gray-900">{{ member.name }}</h3>
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                :class="getRelationshipBadgeClass(member.relationship)"
              >
                {{ formatRelationship(member.relationship) }}
              </span>
              <span
                v-if="member.is_dependent"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
              >
                Dependent
              </span>
            </div>

            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-if="member.date_of_birth">
                <p class="text-body-xs text-gray-500">Date of Birth</p>
                <p class="text-body-sm text-gray-900">{{ formatDate(member.date_of_birth) }}</p>
                <p class="text-body-xs text-gray-500">Age: {{ calculateAge(member.date_of_birth) }}</p>
              </div>

              <div v-if="member.gender">
                <p class="text-body-xs text-gray-500">Gender</p>
                <p class="text-body-sm text-gray-900 capitalize">{{ member.gender }}</p>
              </div>

              <div v-if="member.annual_income">
                <p class="text-body-xs text-gray-500">Annual Income</p>
                <p class="text-body-sm text-gray-900">{{ formatCurrency(member.annual_income) }}</p>
              </div>

              <div v-if="member.education_status">
                <p class="text-body-xs text-gray-500">Education</p>
                <p class="text-body-sm text-gray-900 capitalize">{{ member.education_status.replace('_', ' ') }}</p>
              </div>
            </div>

            <div v-if="member.notes" class="mt-3">
              <p class="text-body-xs text-gray-500">Notes</p>
              <p class="text-body-sm text-gray-900">{{ member.notes }}</p>
            </div>
          </div>

          <div class="flex space-x-2 ml-4">
            <button
              @click="openEditModal(member)"
              class="btn-secondary-sm"
            >
              Edit
            </button>
            <button
              @click="confirmDelete(member)"
              class="btn-danger-sm"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card p-8 text-center">
      <p class="text-body-base text-gray-500">No family members added yet</p>
      <button
        @click="openAddModal"
        class="btn-primary mt-4"
      >
        Add Your First Family Member
      </button>
    </div>

    <!-- Family Member Form Modal -->
    <FamilyMemberFormModal
      v-if="showModal"
      :member="selectedMember"
      @save="handleSave"
      @close="closeModal"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteConfirm"
      title="Delete Family Member"
      :message="`Are you sure you want to delete ${memberToDelete?.name}? This action cannot be undone.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      @confirm="handleDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import FamilyMemberFormModal from './FamilyMemberFormModal.vue';
import ConfirmationModal from '@/components/Common/ConfirmationModal.vue';

export default {
  name: 'FamilyMembers',

  components: {
    FamilyMemberFormModal,
    ConfirmationModal,
  },

  setup() {
    const store = useStore();
    const showModal = ref(false);
    const selectedMember = ref(null);
    const successMessage = ref('');
    const showDeleteConfirm = ref(false);
    const memberToDelete = ref(null);

    const familyMembers = computed(() => store.getters['userProfile/familyMembers']);

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    };

    const calculateAge = (dateString) => {
      if (!dateString) return 'N/A';
      const birthDate = new Date(dateString);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const monthDiff = today.getMonth() - birthDate.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      return age;
    };

    const formatCurrency = (amount) => {
      if (!amount) return '£0';
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount);
    };

    const formatRelationship = (relationship) => {
      if (!relationship) return '';
      return relationship.replace('_', ' ');
    };

    const getRelationshipBadgeClass = (relationship) => {
      const classes = {
        spouse: 'bg-purple-100 text-purple-800',
        child: 'bg-blue-100 text-blue-800',
        parent: 'bg-green-100 text-green-800',
        other_dependent: 'bg-amber-100 text-amber-800',
      };
      return classes[relationship] || 'bg-gray-100 text-gray-800';
    };

    const openAddModal = () => {
      selectedMember.value = null;
      showModal.value = true;
    };

    const openEditModal = (member) => {
      selectedMember.value = member;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      selectedMember.value = null;
    };

    const handleSave = async (formData) => {
      try {
        if (selectedMember.value) {
          // Update existing member
          await store.dispatch('userProfile/updateFamilyMember', {
            id: selectedMember.value.id,
            data: formData,
          });
          successMessage.value = 'Family member updated successfully!';
        } else {
          // Add new member
          await store.dispatch('userProfile/addFamilyMember', formData);
          successMessage.value = 'Family member added successfully!';
        }

        closeModal();

        // Clear success message after 3 seconds
        setTimeout(() => {
          successMessage.value = '';
        }, 3000);
      } catch (error) {
        console.error('Failed to save family member:', error);
      }
    };

    const confirmDelete = (member) => {
      memberToDelete.value = member;
      showDeleteConfirm.value = true;
    };

    const handleDelete = async () => {
      try {
        await store.dispatch('userProfile/deleteFamilyMember', memberToDelete.value.id);
        successMessage.value = 'Family member deleted successfully!';
        showDeleteConfirm.value = false;
        memberToDelete.value = null;

        // Clear success message after 3 seconds
        setTimeout(() => {
          successMessage.value = '';
        }, 3000);
      } catch (error) {
        console.error('Failed to delete family member:', error);
        showDeleteConfirm.value = false;
      }
    };

    // Disabled auto-fetch to prevent infinite loop
    // onMounted(async () => {
    //   await store.dispatch('userProfile/fetchFamilyMembers');
    // });

    return {
      familyMembers,
      showModal,
      selectedMember,
      successMessage,
      showDeleteConfirm,
      memberToDelete,
      formatDate,
      calculateAge,
      formatCurrency,
      formatRelationship,
      getRelationshipBadgeClass,
      openAddModal,
      openEditModal,
      closeModal,
      handleSave,
      confirmDelete,
      handleDelete,
    };
  },
};
</script>
