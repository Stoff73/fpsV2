<template>
  <AppLayout>
    <div class="net-worth-dashboard">
    <div class="dashboard-header">
      <div class="breadcrumbs">
        <router-link to="/" class="breadcrumb-link">Dashboard</router-link>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Net Worth</span>
      </div>

      <div class="header-actions">
        <button @click="refreshData" class="refresh-button" :disabled="loading">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" :class="{ 'animate-spin': loading }">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          <span v-if="!loading">Refresh</span>
          <span v-else>Refreshing...</span>
        </button>
      </div>
    </div>

    <div class="tab-navigation">
      <router-link
        v-for="tab in tabs"
        :key="tab.path"
        :to="`/net-worth/${tab.path}`"
        class="tab-link"
        active-class="active"
      >
        {{ tab.label }}
      </router-link>
    </div>

    <div class="dashboard-content">
      <router-view></router-view>
    </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'NetWorthDashboard',

  components: {
    AppLayout,
  },

  data() {
    return {
      tabs: [
        { path: 'overview', label: 'Overview' },
        { path: 'retirement', label: 'Retirement' },
        { path: 'property', label: 'Property' },
        { path: 'investments', label: 'Investments' },
        { path: 'cash', label: 'Cash' },
        { path: 'business', label: 'Business Interests' },
        { path: 'chattels', label: 'Chattels' },
      ],
    };
  },

  computed: {
    ...mapState('netWorth', ['loading']),
  },

  methods: {
    ...mapActions('netWorth', ['refreshNetWorth']),

    async refreshData() {
      try {
        await this.refreshNetWorth();
      } catch (error) {
        console.error('Failed to refresh net worth:', error);
      }
    },
  },

  mounted() {
    // Redirect to overview if at root
    if (this.$route.path === '/net-worth' || this.$route.path === '/net-worth/') {
      this.$router.replace('/net-worth/overview');
    }
  },
};
</script>

<style scoped>
.net-worth-dashboard {
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.breadcrumb-link {
  color: #3b82f6;
  text-decoration: none;
  transition: colour 0.2s;
}

.breadcrumb-link:hover {
  color: #2563eb;
  text-decoration: underline;
}

.breadcrumb-separator {
  color: #9ca3af;
}

.breadcrumb-current {
  color: #111827;
  font-weight: 600;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.refresh-button {
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

.refresh-button:hover:not(:disabled) {
  background: #2563eb;
}

.refresh-button:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.refresh-button svg {
  width: 20px;
  height: 20px;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.tab-navigation {
  display: flex;
  gap: 4px;
  border-bottom: 2px solid #e5e7eb;
  margin-bottom: 24px;
  overflow-x: auto;
}

.tab-link {
  padding: 12px 20px;
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-link:hover {
  color: #3b82f6;
}

.tab-link.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
  font-weight: 600;
}

.dashboard-content {
  min-height: 500px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .net-worth-dashboard {
    padding: 16px;
  }

  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }

  .tab-navigation {
    gap: 2px;
  }

  .tab-link {
    padding: 10px 12px;
    font-size: 13px;
  }

  .refresh-button {
    padding: 8px 12px;
    font-size: 13px;
  }
}
</style>
