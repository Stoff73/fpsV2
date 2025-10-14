import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ReadinessGauge from '@/components/Retirement/ReadinessGauge.vue';

describe('ReadinessGauge', () => {
  beforeEach(() => {
    // Mock ApexCharts if not already mocked globally
    if (!global.ApexCharts) {
      global.ApexCharts = class {
        constructor() {}
        render() {}
        updateOptions() {}
        updateSeries() {}
        destroy() {}
      };
    }
  });

  it('renders with score prop', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 75,
      },
    });

    expect(wrapper.exists()).toBe(true);
  });

  it('displays correct score (0-100)', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 85,
      },
    });

    expect(wrapper.vm.score).toBe(85);
  });

  it('uses green color for excellent score (90+)', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 95,
      },
    });

    const color = wrapper.vm.gaugeColor;
    // Green color for excellent readiness
    expect(color).toMatch(/#10b981|#22c55e|#16a34a/i);
  });

  it('uses amber color for good score (70-89)', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 75,
      },
    });

    const color = wrapper.vm.gaugeColor;
    // Amber color for good readiness
    expect(color).toMatch(/#f59e0b|#eab308|#fbbf24/i);
  });

  it('uses orange color for fair score (50-69)', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 60,
      },
    });

    const color = wrapper.vm.gaugeColor;
    // Orange color for fair readiness
    expect(color).toMatch(/#f97316|#fb923c/i);
  });

  it('uses red color for critical score (<50)', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 30,
      },
    });

    const color = wrapper.vm.gaugeColor;
    // Red color for critical readiness
    expect(color).toMatch(/#ef4444|#dc2626|#f87171/i);
  });

  it('handles edge case score of 0', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 0,
      },
    });

    expect(wrapper.vm.score).toBe(0);
    const color = wrapper.vm.gaugeColor;
    expect(color).toMatch(/#ef4444|#dc2626/i); // Should be red
  });

  it('handles edge case score of 100', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 100,
      },
    });

    expect(wrapper.vm.score).toBe(100);
    const color = wrapper.vm.gaugeColor;
    expect(color).toMatch(/#10b981|#22c55e/i); // Should be green
  });

  it('computes correct status label for excellent', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 95,
      },
    });

    expect(wrapper.vm.statusLabel).toMatch(/excellent|great|ready/i);
  });

  it('computes correct status label for critical', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 25,
      },
    });

    expect(wrapper.vm.statusLabel).toMatch(/poor|critical|needs attention|low/i);
  });

  it('displays retirement readiness text', () => {
    const wrapper = mount(ReadinessGauge, {
      props: {
        score: 75,
      },
    });

    const html = wrapper.html();
    expect(html).toMatch(/readiness|retirement/i);
  });
});
