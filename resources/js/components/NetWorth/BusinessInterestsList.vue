<template>
  <div class="business-interests-list">
    <div class="list-header">
      <h2 class="list-title">Business Interests</h2>
      <div class="list-controls">
        <select v-model="filterType" class="filter-select">
          <option value="all">All Businesses</option>
          <option value="sole_trader">Sole Trader</option>
          <option value="partnership">Partnership</option>
          <option value="limited_company">Limited Company</option>
          <option value="llp">LLP</option>
        </select>
        <select v-model="sortBy" class="sort-select">
          <option value="value_desc">Value (High to Low)</option>
          <option value="value_asc">Value (Low to High)</option>
          <option value="name">Business Name</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <p>Loading business interests...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="filteredBusinesses.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
      </svg>
      <p class="empty-title">Business Interests</p>
      <p class="empty-subtitle">Track and manage your business interests including sole trader businesses, partnerships, limited companies and LLPs.</p>
      <p class="coming-soon-badge">Coming in Beta</p>
    </div>

    <div v-else class="businesses-grid">
      <BusinessInterestCard
        v-for="business in filteredBusinesses"
        :key="business.id"
        :business="business"
      />
    </div>
  </div>
</template>

<script>
import BusinessInterestCard from './BusinessInterestCard.vue';

export default {
  name: 'BusinessInterestsList',

  components: {
    BusinessInterestCard,
  },

  data() {
    return {
      businesses: [],
      loading: false,
      error: null,
      filterType: 'all',
      sortBy: 'value_desc',
    };
  },

  computed: {
    filteredBusinesses() {
      let filtered = [...this.businesses];

      // Apply filter
      if (this.filterType !== 'all') {
        filtered = filtered.filter(b => b.business_type === this.filterType);
      }

      // Apply sort
      if (this.sortBy === 'value_desc') {
        filtered.sort((a, b) => b.current_valuation - a.current_valuation);
      } else if (this.sortBy === 'value_asc') {
        filtered.sort((a, b) => a.current_valuation - b.current_valuation);
      } else if (this.sortBy === 'name') {
        filtered.sort((a, b) => a.business_name.localeCompare(b.business_name));
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
.business-interests-list {
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

.businesses-grid {
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

.coming-soon-badge {
  display: inline-block;
  margin-top: 16px;
  padding: 8px 16px;
  background: #dbeafe;
  color: #1e40af;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .business-interests-list {
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

  .businesses-grid {
    grid-template-columns: 1fr;
  }
}
</style>
