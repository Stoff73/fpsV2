import { ref, watch } from 'vue';

const DESIGN_MODE_KEY = 'fps_design_mode';

// Reactive state shared across all components
const designMode = ref(localStorage.getItem(DESIGN_MODE_KEY) || 'normal');

export function useDesignMode() {
  const toggleDesignMode = () => {
    designMode.value = designMode.value === 'normal' ? 'slippery' : 'normal';
  };

  const isSlipperyMode = () => {
    return designMode.value === 'slippery';
  };

  const isNormalMode = () => {
    return designMode.value === 'normal';
  };

  // Watch for changes and update localStorage + apply CSS class to body
  watch(designMode, (newMode) => {
    localStorage.setItem(DESIGN_MODE_KEY, newMode);

    // Update body class
    if (newMode === 'slippery') {
      document.documentElement.classList.add('slippery-mode');
      document.documentElement.classList.remove('normal-mode');
    } else {
      document.documentElement.classList.add('normal-mode');
      document.documentElement.classList.remove('slippery-mode');
    }
  }, { immediate: true });

  return {
    designMode,
    toggleDesignMode,
    isSlipperyMode,
    isNormalMode,
  };
}
