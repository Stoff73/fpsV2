<template>
  <div class="chattels-list">
    <div class="list-header">
      <h2 class="list-title">Chattels & Valuables</h2>
      <div class="list-controls">
        <select v-model="filterType" class="filter-select">
          <option value="all">All Chattels</option>
          <option value="vehicle">Vehicles</option>
          <option value="art">Art</option>
          <option value="antique">Antiques</option>
          <option value="jewelry">Jewelry</option>
          <option value="collectible">Collectibles</option>
          <option value="other">Other</option>
        </select>
        <select v-model="sortBy" class="sort-select">
          <option value="value_desc">Value (High to Low)</option>
          <option value="value_asc">Value (Low to High)</option>
          <option value="name">Name</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <p>Loading chattels...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
    </div>

    <div v-else-if="filteredChattels.length === 0" class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="empty-icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
      </svg>
      <p>No chattels found</p>
      <p class="empty-subtitle">Chattels will be managed in Phase 4</p>
    </div>

    <div v-else class="chattels-grid">
      <ChattelCard
        v-for="chattel in filteredChattels"
        :key="chattel.id"
        :chattel="chattel"
      />
    </div>
  </div>
</template>

<script>
import ChattelCard from './ChattelCard.vue';

export default {
  name: 'ChattelsList',

  components: {
    ChattelCard,
  },

  data() {
    return {
      chattels: [],
      loading: false,
      error: null,
      filterType: 'all',
      sortBy: 'value_desc',
    };
  },

  computed: {
    filteredChattels() {
      let filtered = [...this.chattels];

      // Apply filter
      if (this.filterType !== 'all') {
        filtered = filtered.filter(c => c.chattel_type === this.filterType);
      }

      // Apply sort
      if (this.sortBy === 'value_desc') {
        filtered.sort((a, b) => b.current_value - a.current_value);
      } else if (this.sortBy === 'value_asc') {
        filtered.sort((a, b) => a.current_value - b.current_value);
      } else if (this.sortBy === 'name') {
        filtered.sort((a, b) => a.chattel_name.localeCompare(b.chattel_name));
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
.chattels-list {
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

.chattels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
  .chattels-list {
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

  .chattels-grid {
    grid-template-columns: 1fr;
  }
}
</style>
