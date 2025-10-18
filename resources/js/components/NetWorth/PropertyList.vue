<template>
  <div class="property-list">
    <div class="list-header">
      <h2 class="list-title">Properties</h2>
      <div class="list-controls">
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
  </div>
</template>

<script>
import PropertyCard from './PropertyCard.vue';

export default {
  name: 'PropertyList',

  components: {
    PropertyCard,
  },

  data() {
    return {
      properties: [],
      loading: false,
      error: null,
      filterType: 'all',
      sortBy: 'value_desc',
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

  async mounted() {
    // In Phase 4, this will fetch from the API
    // For now, show empty state
    this.loading = false;
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

  .filter-select,
  .sort-select {
    width: 100%;
  }

  .properties-grid {
    grid-template-columns: 1fr;
  }
}
</style>
