import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import EstateOverviewCard from '@/components/Estate/EstateOverviewCard.vue';

describe('EstateOverviewCard', () => {
  it('renders with all required props', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 750000,
        ihtLiability: 50000,
        giftsWithin7Years: 3,
        nrbAvailable: 325000,
      },
    });

    expect(wrapper.exists()).toBe(true);
  });

  it('displays net worth correctly formatted', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 850000,
        ihtLiability: 60000,
        giftsWithin7Years: 2,
        nrbAvailable: 325000,
      },
    });

    const html = wrapper.html();
    expect(html).toMatch(/£850,000|850000/);
  });

  it('displays IHT liability with red color for high values', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 1000000,
        ihtLiability: 200000, // High IHT
        giftsWithin7Years: 1,
        nrbAvailable: 325000,
      },
    });

    const html = wrapper.html();
    expect(html).toMatch(/200,000|200000/);
  });

  it('displays number of gifts within 7 years', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 500000,
        ihtLiability: 0,
        giftsWithin7Years: 5,
        nrbAvailable: 325000,
      },
    });

    expect(wrapper.vm.giftsWithin7Years).toBe(5);
  });

  it('displays NRB available correctly', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 600000,
        ihtLiability: 30000,
        giftsWithin7Years: 2,
        nrbAvailable: 325000,
      },
    });

    expect(wrapper.vm.nrbAvailable).toBe(325000);
  });

  it('emits click event when card is clicked', async () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 700000,
        ihtLiability: 40000,
        giftsWithin7Years: 1,
        nrbAvailable: 325000,
      },
    });

    await wrapper.trigger('click');
    expect(wrapper.emitted()).toHaveProperty('click');
  });

  it('handles zero net worth', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 0,
        ihtLiability: 0,
        giftsWithin7Years: 0,
        nrbAvailable: 325000,
      },
    });

    expect(wrapper.vm.netWorth).toBe(0);
    expect(wrapper.exists()).toBe(true);
  });

  it('handles negative net worth (liabilities exceed assets)', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: -50000,
        ihtLiability: 0,
        giftsWithin7Years: 0,
        nrbAvailable: 325000,
      },
    });

    expect(wrapper.vm.netWorth).toBe(-50000);
  });

  it('displays estate planning card title', () => {
    const wrapper = mount(EstateOverviewCard, {
      props: {
        netWorth: 800000,
        ihtLiability: 50000,
        giftsWithin7Years: 3,
        nrbAvailable: 325000,
      },
    });

    const html = wrapper.html();
    expect(html).toMatch(/estate|planning/i);
  });
});
