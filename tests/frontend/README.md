# Frontend Testing Guide

## Quick Start

```bash
# Run all tests
npm run test:run

# Watch mode (re-run on file changes)
npm run test

# Interactive UI
npm run test:ui

# Run specific test file
npm run test:run tests/frontend/components/Protection/ProtectionOverviewCard.test.js
```

## Test Structure

```
tests/frontend/
├── setup.js                          # Global test configuration
├── api/
│   ├── protectionApi.test.js         # API integration tests (jsdom)
│   └── test-protection-api.sh        # API integration tests (bash/curl)
└── components/
    └── Protection/
        ├── ProtectionOverviewCard.test.js
        ├── CoverageAdequacyGauge.test.js
        ├── RecommendationCard.test.js
        └── PolicyCard.test.js
```

## Writing Tests

### Example Component Test

```javascript
import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import MyComponent from '@/components/MyComponent.vue';

describe('MyComponent', () => {
  it('renders correctly', () => {
    const wrapper = mount(MyComponent, {
      props: {
        title: 'Test Title',
      },
    });

    expect(wrapper.text()).toContain('Test Title');
  });

  it('emits event on button click', async () => {
    const wrapper = mount(MyComponent);

    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('submit')).toBeTruthy();
  });
});
```

### Testing Components with Vuex

```javascript
import { createStore } from 'vuex';

const store = createStore({
  modules: {
    protection: {
      namespaced: true,
      state: { ... },
      getters: { ... },
    },
  },
});

const wrapper = mount(Component, {
  global: {
    plugins: [store],
  },
});
```

### Testing Components with Vue Router

```javascript
import { createRouter, createMemoryHistory } from 'vue-router';

const router = createRouter({
  history: createMemoryHistory(),
  routes: [
    { path: '/protection', component: ProtectionDashboard },
  ],
});

const wrapper = mount(Component, {
  global: {
    plugins: [router],
  },
});
```

## Test Patterns

### 1. Component Rendering
```javascript
it('renders with props', () => {
  const wrapper = mount(Component, {
    props: { value: 42 },
  });

  expect(wrapper.exists()).toBe(true);
  expect(wrapper.text()).toContain('42');
});
```

### 2. User Interactions
```javascript
it('handles button click', async () => {
  const wrapper = mount(Component);

  await wrapper.find('button').trigger('click');

  expect(wrapper.emitted('click')).toBeTruthy();
});
```

### 3. Conditional Rendering
```javascript
it('shows error message when error prop is true', () => {
  const wrapper = mount(Component, {
    props: { error: true },
  });

  expect(wrapper.find('.error-message').exists()).toBe(true);
});
```

### 4. Computed Properties
```javascript
it('calculates total correctly', () => {
  const wrapper = mount(Component, {
    props: { items: [1, 2, 3] },
  });

  expect(wrapper.vm.total).toBe(6);
});
```

## Mocking

### Mocking API Calls
```javascript
import { vi } from 'vitest';
import * as api from '@/services/protectionService';

vi.mock('@/services/protectionService', () => ({
  getProtectionData: vi.fn(() => Promise.resolve({ data: [] })),
}));
```

### Mocking ApexCharts
Already configured in `setup.js`:
```javascript
config.global.stubs = {
  apexchart: {
    template: '<div class="apexchart-mock"></div>',
  },
};
```

## Debugging Tests

### Using console.log
```javascript
it('debug test', () => {
  const wrapper = mount(Component);
  console.log(wrapper.html()); // Print component HTML
  console.log(wrapper.vm);     // Print component instance
});
```

### Using debug()
```javascript
import { mount } from '@vue/test-utils';

it('debug test', () => {
  const wrapper = mount(Component);
  wrapper.get('button').trigger('click');
  // Pause execution here
  debugger;
});
```

## Best Practices

1. **Test User Behavior, Not Implementation**
   - Focus on what the user sees and does
   - Avoid testing internal methods directly

2. **Use Descriptive Test Names**
   - Good: `it('displays error message when email is invalid')`
   - Bad: `it('test 1')`

3. **Arrange, Act, Assert (AAA)**
   ```javascript
   it('example test', () => {
     // Arrange
     const wrapper = mount(Component);

     // Act
     wrapper.find('button').trigger('click');

     // Assert
     expect(wrapper.text()).toContain('Clicked');
   });
   ```

4. **Keep Tests Independent**
   - Each test should work in isolation
   - Don't rely on test execution order

5. **Use data-testid for Stable Selectors**
   ```vue
   <button data-testid="submit-button">Submit</button>
   ```
   ```javascript
   wrapper.find('[data-testid="submit-button"]');
   ```

## Common Issues

### Issue: "Failed to resolve component"
**Solution**: Add component to global stubs in `setup.js`

### Issue: "$route is undefined"
**Solution**: Mock is already configured in `setup.js`

### Issue: "ApexCharts is not defined"
**Solution**: ApexCharts mock is already configured in `setup.js`

### Issue: Test timeout
**Solution**: Increase timeout or make async operations faster:
```javascript
it('async test', { timeout: 10000 }, async () => {
  // test code
});
```

## Code Coverage

Generate coverage report:
```bash
# Run tests with coverage
npm run test:run -- --coverage

# View HTML report
open coverage/index.html
```

Configure coverage in `vitest.config.js`:
```javascript
test: {
  coverage: {
    provider: 'v8',
    reporter: ['text', 'json', 'html'],
    include: ['resources/js/**/*.{js,vue}'],
  },
}
```

## CI/CD Integration

### GitHub Actions Example
```yaml
- name: Run Frontend Tests
  run: npm run test:run
```

### GitLab CI Example
```yaml
test:
  script:
    - npm install
    - npm run test:run
```

## Additional Resources

- [Vitest Docs](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Library](https://testing-library.com/docs/vue-testing-library/intro/)
