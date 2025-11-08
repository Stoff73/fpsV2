<template>
  <div class="property-list">
    <div class="list-header">
      <h2 class="list-title">Properties</h2>
      <div class="list-controls">
        <button @click="addProperty" class="add-property-button">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="button-icon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Property
        </button>
        <select v-model="filterType" class="filter-select">
          <option value="all">All Properties</option>
          <option value="main_residence">Main Residence</option>
          <option value="secondary_residence">Secondary Residence</option>
          <option value="buy_to_let">Buy to Let</option>
        </select>
        <select v-model="sortBy" class="sort-select">
          <option value="value_desc">Value (High to Low)</option>
          <option value="value_asc">Value (Low to High)</option>
          <option value="type">Property Type</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <p>Loading properties...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="filteredProperties.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
      </svg>
      <p>No properties found</p>
      <p class="empty-subtitle">Properties will be managed in Phase 4</p>
    </div>

    <div v-else class="properties-grid">
      <PropertyCard
        v-for="property in filteredProperties"
        :key="property.id"
        :property="property"
      />
    </div>

    <!-- Property Form Modal -->
    <PropertyForm
      :show="showPropertyForm"
      :property="selectedProperty"
      @save="handleSaveProperty"
      @close="closePropertyForm"
    />

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="notification success">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="notification error">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import PropertyCard from './PropertyCard.vue';
import PropertyForm from './Property/PropertyForm.vue';
import api from '@/services/api';

export default {
  name: 'PropertyList',

  components: {
    PropertyCard,
    PropertyForm,
  },

  data() {
    return {
      properties: [],
      loading: false,
      error: null,
      filterType: 'all',
      sortBy: 'value_desc',
      showPropertyForm: false,
      selectedProperty: null,
      successMessage: null,
      errorMessage: null,
    };
  },

  computed: {
    filteredProperties() {
      let filtered = [...this.properties];

      // Apply filter
      if (this.filterType !== 'all') {
        filtered = filtered.filter(p => p.property_type === this.filterType);
      }

      // Apply sort
      if (this.sortBy === 'value_desc') {
        filtered.sort((a, b) => b.current_value - a.current_value);
      } else if (this.sortBy === 'value_asc') {
        filtered.sort((a, b) => a.current_value - b.current_value);
      } else if (this.sortBy === 'type') {
        filtered.sort((a, b) => a.property_type.localeCompare(b.property_type));
      }

      return filtered;
    },
  },

  methods: {
    addProperty() {
      this.selectedProperty = null;
      this.showPropertyForm = true;
    },

    closePropertyForm() {
      this.showPropertyForm = false;
      this.selectedProperty = null;
    },

    async handleSaveProperty(data) {
      this.clearMessages();

      try {
        let propertyResponse;

        if (data.property.id) {
          // Update existing property
          propertyResponse = await api.put(`/properties/${data.property.id}`, data.property);
          const index = this.properties.findIndex(p => p.id === data.property.id);
          if (index !== -1) {
            this.properties.splice(index, 1, propertyResponse.data);
          }
          this.successMessage = 'Property updated successfully';
        } else {
          // Create new property
          propertyResponse = await api.post('/properties', data.property);
          this.properties.push(propertyResponse.data);
          this.successMessage = 'Property added successfully';

          // If mortgage data provided, save mortgage
          if (data.mortgage && propertyResponse.data?.id) {
            const propertyId = propertyResponse.data.id;
            await api.post(`/properties/${propertyId}/mortgages`, data.mortgage);
            this.successMessage = 'Property and mortgage added successfully';
          }
        }

        this.closePropertyForm();

        // Auto-hide success message after 5 seconds
        setTimeout(() => {
          this.successMessage = null;
        }, 5000);
      } catch (error) {
        console.error('Error saving property:', error);
        this.errorMessage = error.response?.data?.message || 'Failed to save property. Please try again.';

        // Auto-hide error message after 5 seconds
        setTimeout(() => {
          this.errorMessage = null;
        }, 5000);
      }
    },

    clearMessages() {
      this.successMessage = null;
      this.errorMessage = null;
    },

    async fetchProperties() {
      this.loading = true;
      this.error = null;

      try {
        const response = await api.get('/properties');
        this.properties = response.data;
      } catch (error) {
        console.error('Error fetching properties:', error);
        this.error = error.response?.data?.message || 'Failed to load properties';
      } finally {
        this.loading = false;
      }
    },
  },

  async mounted() {
    // Fetch family members to ensure spouse data is available
    await this.$store.dispatch('userProfile/fetchFamilyMembers');
    await this.fetchProperties();
  },
};
</script>

<style scoped>
.property-list {
  padding: 24px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.list-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.list-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.add-property-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.add-property-button:hover {
  background: #2563eb;
}

.button-icon {
  width: 20px;
  height: 20px;
}

.filter-select,
.sort-select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #374151;
  background: white;
  cursor: pointer;
}

.filter-select:focus,
.sort-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.loading-state p,
.error-state p {
  color: #6b7280;
  font-size: 16px;
  margin: 0;
}

.error-state p {
  color: #ef4444;
}

.empty-state {
  background: white;
  border-radius: 12px;
  padding: 80px 40px;
  border: 2px dashed #d1d5db;
}

.empty-icon {
  width: 64px;
  height: 64px;
  color: #9ca3af;
  margin: 0 auto 16px;
}

.empty-state p {
  color: #6b7280;
  font-size: 18px;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.empty-subtitle {
  color: #9ca3af;
  font-size: 14px;
  font-weight: 400;
}

.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  z-index: 100;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s ease-out;
}

.notification.success {
  background: #10b981;
  color: white;
}

.notification.error {
  background: #ef4444;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .property-list {
    padding: 16px;
  }

  .list-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .list-controls {
    width: 100%;
    flex-direction: column;
  }

  .add-property-button,
  .filter-select,
  .sort-select {
    width: 100%;
  }

  .properties-grid {
    grid-template-columns: 1fr;
  }
}
</style>
