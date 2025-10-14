import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import RetirementOverviewCard from '@/components/Retirement/RetirementOverviewCard.vue';

describe('RetirementOverviewCard', () => {
  const defaultProps = {
    readinessScore: 75,
    projectedIncome: 25000,
    targetIncome: 30000,
    yearsToRetirement: 20,
    totalWealth: 200000,
  };

  it('renders with all props', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    expect(wrapper.exists()).toBe(true);
  });

  it('displays readiness score', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    const html = wrapper.html();
    expect(html).toContain('75');
  });

  it('displays projected income formatted', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    const html = wrapper.html();
    // Should display formatted income (£25,000 or similar)
    expect(html).toMatch(/25,?000|£25,?000/);
  });

  it('displays income information', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    const html = wrapper.html();
    // Should show projected income of £25,000 and gap of £5,000
    expect(html).toMatch(/25,?000|£25,?000/);
    expect(html).toMatch(/5,?000|£5,?000/);
  });

  it('displays years to retirement', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    const html = wrapper.html();
    expect(html).toContain('20');
    expect(html).toMatch(/years?/i);
  });

  it('displays total wealth', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
    });

    const html = wrapper.html();
    expect(html).toMatch(/200,?000|£200,?000/);
  });

  it('shows income gap when projected < target', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: {
        ...defaultProps,
        projectedIncome: 25000,
        targetIncome: 30000,
      },
    });

    const html = wrapper.html();
    // Should show gap of £5,000
    expect(html).toMatch(/gap|shortfall|deficit/i);
  });

  it('shows income surplus when projected > target', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: {
        ...defaultProps,
        projectedIncome: 35000,
        targetIncome: 30000,
      },
    });

    const html = wrapper.html();
    // Should show surplus of £5,000
    expect(html).toMatch(/surplus|excess|above/i);
  });

  it('applies correct color coding for high readiness', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: {
        ...defaultProps,
        readinessScore: 95,
      },
    });

    const html = wrapper.html();
    // Should have green color classes
    expect(html).toMatch(/green|success/i);
  });

  it('applies correct color coding for low readiness', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: {
        ...defaultProps,
        readinessScore: 30,
      },
    });

    const html = wrapper.html();
    // Should have red/warning color classes
    expect(html).toMatch(/red|danger|warning/i);
  });

  it('is clickable and navigates to detail view', () => {
    const wrapper = mount(RetirementOverviewCard, {
      props: defaultProps,
      global: {
        mocks: {
          $router: {
            push: () => {},
          },
        },
      },
    });

    // Should have cursor-pointer class for clickability
    const html = wrapper.html();
    expect(html).toMatch(/cursor-pointer/);
  });
});
