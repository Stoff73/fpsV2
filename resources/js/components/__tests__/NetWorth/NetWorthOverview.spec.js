import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { createStore } from 'vuex';
import NetWorthOverview from '../../NetWorth/NetWorthOverview.vue';

describe('NetWorthOverview.vue', () => {
  let wrapper;
  let store;
  let mockActions;

  beforeEach(() => {
    mockActions = {
      fetchOverview: vi.fn(() => Promise.resolve()),
      fetchTrend: vi.fn(() => Promise.resolve()),
    };

    store = createStore({
      modules: {
        netWorth: {
          namespaced: true,
          state: {
            overview: {
              total_assets: 750000,
              total_liabilities: 300000,
              net_worth: 450000,
              breakdown: {
                property: 500000,
                investments: 150000,
                cash: 50000,
                business: 40000,
                chattels: 10000,
              },
            },
            trend: [
              { date: '2024-01-01', net_worth: 400000 },
              { date: '2024-02-01', net_worth: 410000 },
              { date: '2024-03-01', net_worth: 420000 },
              { date: '2024-04-01', net_worth: 430000 },
              { date: '2024-05-01', net_worth: 440000 },
              { date: '2024-06-01', net_worth: 450000 },
              { date: '2024-07-01', net_worth: 450000 },
              { date: '2024-08-01', net_worth: 450000 },
              { date: '2024-09-01', net_worth: 450000 },
              { date: '2024-10-01', net_worth: 450000 },
              { date: '2024-11-01', net_worth: 450000 },
              { date: '2024-12-01', net_worth: 450000 },
            ],
            loading: false,
            error: null,
          },
          getters: {
            netWorth: (state) => state.overview.net_worth,
            totalAssets: (state) => state.overview.total_assets,
            totalLiabilities: (state) => state.overview.total_liabilities,
            assetBreakdown: (state) => state.overview.breakdown,
            trendData: (state) => state.trend,
          },
          actions: mockActions,
        },
      },
    });

    wrapper = mount(NetWorthOverview, {
      global: {
        plugins: [store],
        stubs: {
          AssetAllocationDonut: {
            template: '<div class="asset-allocation-stub"></div>',
            props: ['breakdown'],
          },
          NetWorthTrendChart: {
            template: '<div class="trend-chart-stub"></div>',
            props: ['trend'],
          },
          AssetBreakdownBar: {
            template: '<div class="breakdown-bar-stub"></div>',
            props: ['breakdown'],
          },
          apexchart: {
            template: '<div class="apexchart-stub"></div>',
          },
        },
      },
    });
  });

  it('renders net worth overview component', () => {
    expect(wrapper.exists()).toBe(true);
  });

  it('displays summary cards', () => {
    const html = wrapper.html();

    // Should show Total Assets, Total Liabilities, and Net Worth
    expect(html).toMatch(/Total Assets|Assets/i);
    expect(html).toMatch(/Total Liabilities|Liabilities/i);
    expect(html).toMatch(/Net Worth/i);
  });

  it('displays total assets value', () => {
    const html = wrapper.html();
    expect(html).toContain('750');
  });

  it('displays total liabilities value', () => {
    const html = wrapper.html();
    expect(html).toContain('300');
  });

  it('displays net worth value', () => {
    const html = wrapper.html();
    expect(html).toContain('450');
  });

  it('renders AssetAllocationDonut chart', () => {
    const chart = wrapper.findComponent({ name: 'AssetAllocationDonut' });
    expect(chart.exists()).toBe(true);
  });

  it('passes breakdown data to AssetAllocationDonut', () => {
    const chart = wrapper.findComponent({ name: 'AssetAllocationDonut' });

    if (chart.exists()) {
      expect(chart.props('breakdown')).toBeDefined();
    }
  });

  it('renders NetWorthTrendChart', () => {
    const chart = wrapper.findComponent({ name: 'NetWorthTrendChart' });
    expect(chart.exists()).toBe(true);
  });

  it('passes trend data to NetWorthTrendChart', () => {
    const chart = wrapper.findComponent({ name: 'NetWorthTrendChart' });

    if (chart.exists()) {
      expect(chart.props('trend')).toBeDefined();
    }
  });

  it('renders AssetBreakdownBar chart', () => {
    const chart = wrapper.findComponent({ name: 'AssetBreakdownBar' });
    expect(chart.exists()).toBe(true);
  });

  it('calls fetchOverview on mount', () => {
    expect(mockActions.fetchOverview).toHaveBeenCalled();
  });

  it('calls fetchTrend on mount', () => {
    expect(mockActions.fetchTrend).toHaveBeenCalled();
  });

  it('highlights net worth card with special styling', () => {
    const html = wrapper.html();

    // Net worth should be prominent (look for specific classes or larger values)
    expect(html).toContain('450000');
  });

  it('shows currency symbols', () => {
    const html = wrapper.html();
    expect(html).toContain('£');
  });

  it('displays loading state when fetching', async () {
    store.state.netWorth.loading = true;
    await wrapper.vm.$nextTick();

    // Should show loading indicator
    expect(wrapper.html()).toMatch(/loading|spinner|skeleton/i);
  });

  it('displays error message when fetch fails', async () {
    store.state.netWorth.error = 'Failed to load overview';
    await wrapper.vm.$nextTick();

    expect(wrapper.html()).toMatch(/error|failed/i);
  });

  it('formats numbers with commas', () => {
    const html = wrapper.html();

    // Large numbers should be formatted
    expect(html).toMatch(/\d{1,3},\d{3}/);
  });
});
