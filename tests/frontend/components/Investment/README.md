# Investment Module - Frontend Tests

## Overview

This directory contains comprehensive test suites for all Investment module components using Vitest and Vue Test Utils.

## Test Files

### Component Tests

1. **AccountCard.test.js** - Tests for account display card component
   - Props rendering
   - Account type badges and color coding
   - ISA information display
   - Currency formatting
   - Event emissions (edit, delete, view-holdings)
   - Edge cases (zero values, missing data)

2. **AccountForm.test.js** - Tests for account creation/edit form
   - Create vs edit mode
   - Form field rendering
   - ISA-specific fields display logic
   - ISA allowance calculation and color coding
   - Tax year calculation
   - Form validation (required fields, ranges, ISA limits)
   - Event emissions (submit, close)
   - Form reset behavior

3. **InvestmentOverviewCard.test.js** - Tests for main dashboard overview card
   - Props rendering
   - Currency formatting for large values
   - Positive/negative/zero return color coding
   - Rebalancing alerts
   - Navigation on click
   - Edge cases (zero portfolio, very large values)

4. **HoldingsTable.test.js** - Tests for holdings table component
   - Table rendering with holdings data
   - All column displays
   - Current value calculation
   - Return percentage calculation (positive/negative)
   - OCF percentage display
   - Sorting functionality
   - Filtering by asset type
   - Total row calculation
   - Event emissions (add, edit, delete)
   - Loading and empty states
   - Expandable rows
   - Responsive design

5. **AssetAllocationChart.test.js** - Tests for asset allocation donut chart
   - Chart component rendering
   - Donut chart configuration
   - Data extraction (labels, series)
   - Percentage display in legend
   - Empty data handling
   - Color scheme application
   - Tooltip configuration
   - Legend display
   - Responsive design
   - Dynamic data updates

6. **GoalCard.test.js** - Tests for investment goal card component
   - Props rendering
   - Target amount and date display
   - Progress calculation and bar
   - Time remaining calculation
   - Monthly contribution display
   - Monte Carlo results display
   - Success probability gauge with color coding
   - Best/worst case scenarios
   - Required return display
   - Event emissions (edit, delete, run-monte-carlo, view-chart)
   - Status badges
   - Overdue goals handling

## Running Tests

### Run All Investment Tests
```bash
npm run test tests/frontend/components/Investment
```

### Run Specific Test File
```bash
npm run test tests/frontend/components/Investment/AccountCard.test.js
```

### Run Tests in Watch Mode
```bash
npm run test:watch tests/frontend/components/Investment
```

### Run Tests with Coverage
```bash
npm run test:coverage
```

## Test Structure

Each test file follows this pattern:

```javascript
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ComponentName from '@/components/Investment/ComponentName.vue';

describe('ComponentName', () => {
  // Mock data
  const mockData = { /* ... */ };

  it('renders with props', () => {
    const wrapper = mount(ComponentName, {
      props: { /* ... */ },
    });

    expect(wrapper.exists()).toBe(true);
  });

  // More tests...
});
```

## Test Coverage

### AccountCard.test.js
- ✅ 25 test cases
- ✅ All props combinations
- ✅ All account types (ISA, GIA, SIPP, Pension, Other)
- ✅ All events
- ✅ Edge cases

### AccountForm.test.js
- ✅ 30 test cases
- ✅ Create and edit modes
- ✅ ISA allowance tracking and validation
- ✅ Tax year calculation
- ✅ Form validation
- ✅ Color coding for allowance usage
- ✅ Event emissions

### InvestmentOverviewCard.test.js
- ✅ 18 test cases
- ✅ Currency formatting
- ✅ Return color coding (positive/negative/zero)
- ✅ Rebalancing alerts
- ✅ Navigation
- ✅ Edge cases

### HoldingsTable.test.js
- ✅ 30 test cases
- ✅ Table rendering
- ✅ Calculations (value, return percentage, totals)
- ✅ Sorting and filtering
- ✅ Event emissions
- ✅ Loading/empty states
- ✅ Responsive design

### AssetAllocationChart.test.js
- ✅ 20 test cases
- ✅ Chart configuration
- ✅ Data extraction and display
- ✅ Empty state handling
- ✅ Interactive features
- ✅ Responsive design
- ✅ Dynamic updates

### GoalCard.test.js
- ✅ 32 test cases
- ✅ Goal display
- ✅ Progress calculation
- ✅ Time remaining calculation
- ✅ Monte Carlo analysis display
- ✅ Success probability gauge
- ✅ Event emissions
- ✅ Status badges
- ✅ Edge cases (overdue, zero value, over 100%)

**Total Test Cases: 155+**

## Mocking Strategy

### ApexCharts
ApexCharts is mocked for chart components:

```javascript
vi.mock('vue3-apexcharts', () => ({
  default: {
    name: 'ApexChart',
    template: '<div class="mock-apex-chart"></div>',
    props: ['options', 'series', 'type', 'height'],
  },
}));
```

### Vue Router
Router is mocked for navigation tests:

```javascript
const mockRouter = {
  push: vi.fn(),
};

const wrapper = mount(Component, {
  global: {
    mocks: {
      $router: mockRouter,
    },
  },
});
```

### Date/Time
System time is mocked for tax year calculations:

```javascript
vi.useFakeTimers();
vi.setSystemTime(new Date(2025, 2, 15));
// ... tests ...
vi.useRealTimers();
```

## Testing Best Practices

1. **Arrange-Act-Assert Pattern**: Each test follows the AAA pattern
2. **Clear Test Names**: Descriptive test names that explain what is being tested
3. **Edge Cases**: Tests cover edge cases like zero values, null data, extreme values
4. **Event Testing**: All component events are tested with proper assertions
5. **Color Coding**: Tests verify color coding matches business logic (traffic light system)
6. **Currency Formatting**: Tests verify GBP formatting is applied correctly
7. **Responsive Design**: Tests verify responsive classes are applied
8. **Accessibility**: Tests check for proper HTML structure and ARIA labels

## Common Test Patterns

### Testing Props
```javascript
it('renders with props', () => {
  const wrapper = mount(Component, {
    props: {
      prop1: value1,
      prop2: value2,
    },
  });

  expect(wrapper.exists()).toBe(true);
  expect(wrapper.text()).toContain('expected text');
});
```

### Testing Events
```javascript
it('emits event when button clicked', async () => {
  const wrapper = mount(Component, { props: { /* ... */ } });

  const button = wrapper.find('button');
  await button.trigger('click');

  expect(wrapper.emitted('event-name')).toBeTruthy();
  expect(wrapper.emitted('event-name')[0][0]).toEqual(expectedData);
});
```

### Testing Color Coding
```javascript
it('displays green color for positive value', () => {
  const wrapper = mount(Component, {
    props: { value: 10 },
  });

  const html = wrapper.html();
  expect(html).toMatch(/text-green|bg-green/);
});
```

### Testing Currency Formatting
```javascript
it('formats currency values correctly', () => {
  const wrapper = mount(Component, {
    props: { amount: 123456 },
  });

  const text = wrapper.text();
  expect(text).toMatch(/£123,456|£123456/);
});
```

## Integration with CI/CD

These tests are designed to run in continuous integration pipelines:

```yaml
# .github/workflows/test.yml
- name: Run Frontend Tests
  run: npm run test tests/frontend/components/Investment
```

## Future Enhancements

- [ ] Add E2E tests using Playwright/Cypress
- [ ] Add visual regression tests
- [ ] Add accessibility tests with @axe-core/vue
- [ ] Add performance tests for large datasets
- [ ] Add integration tests with Vuex store
- [ ] Add API mocking for integration tests
- [ ] Increase coverage to 100%

## Troubleshooting

### Tests Failing

1. **Check Imports**: Ensure all component paths are correct
2. **Check Mocks**: Verify ApexCharts and Router mocks are properly set up
3. **Check Data**: Ensure mock data matches expected structure
4. **Check Async**: Use `await wrapper.vm.$nextTick()` for async updates

### Slow Tests

1. **Reduce Test Scope**: Focus on specific test files
2. **Use Watch Mode**: Run tests in watch mode for faster feedback
3. **Check Mocks**: Ensure external dependencies are properly mocked

## Related Documentation

- [Vitest Documentation](https://vitest.dev/)
- [Vue Test Utils Documentation](https://test-utils.vuejs.org/)
- [Investment Module Components](/resources/js/components/Investment/)
- [Account Components Documentation](/resources/js/components/Investment/ACCOUNT_COMPONENTS_README.md)

## Support

For questions or issues with tests:
1. Check this documentation
2. Review existing test files for patterns
3. Consult the main project README
4. Check component implementation for expected behavior
