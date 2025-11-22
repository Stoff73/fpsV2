import { ref, watch } from 'vue';

const DESIGN_MODE_KEY = 'fps_design_mode';

// Reactive state shared across all components
// Default to 'normal' mode - slippery mode must be explicitly activated from dashboard only
// Clear any stale localStorage value to ensure normal mode is default
localStorage.removeItem(DESIGN_MODE_KEY);
const designMode = ref('normal');

console.log('[DesignMode] Initial mode from localStorage:', designMode.value);

export function useDesignMode() {
  const toggleDesignMode = () => {
    const oldMode = designMode.value;
    designMode.value = designMode.value === 'normal' ? 'slippery' : 'normal';
    console.log('[DesignMode] Toggle clicked:', oldMode, '→', designMode.value);
  };

  const isSlipperyMode = () => {
    return designMode.value === 'slippery';
  };

  const isNormalMode = () => {
    return designMode.value === 'normal';
  };

  // Watch for changes and update localStorage + apply CSS class to body
  watch(designMode, (newMode) => {
    console.log('[DesignMode] Mode changed to:', newMode);
    localStorage.setItem(DESIGN_MODE_KEY, newMode);

    // Update HTML element class
    if (newMode === 'slippery') {
      document.documentElement.classList.add('slippery-mode');
      document.documentElement.classList.remove('normal-mode');
      console.log('[DesignMode] Applied slippery-mode class to <html>');
      console.log('[DesignMode] HTML classes:', document.documentElement.classList.toString());
    } else {
      document.documentElement.classList.add('normal-mode');
      document.documentElement.classList.remove('slippery-mode');
      console.log('[DesignMode] Applied normal-mode class to <html>');
      console.log('[DesignMode] HTML classes:', document.documentElement.classList.toString());
    }
  }, { immediate: true });

  return {
    designMode,
    toggleDesignMode,
    isSlipperyMode,
    isNormalMode,
  };
}
