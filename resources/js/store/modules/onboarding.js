import onboardingService from '@/services/onboardingService';

const state = {
  status: null, // null | 'in_progress' | 'completed'
  focusArea: null,
  currentStepIndex: 0,
  currentStepName: null,
  totalSteps: 0,
  steps: [],
  stepData: {},
  progressPercentage: 0,
  loading: false,
  error: null,
  showSkipModal: false,
  currentSkipReason: '',
  skipStepName: '',
};

const getters = {
  isOnboardingComplete: (state) => state.status === 'completed',
  isOnboardingInProgress: (state) => state.status === 'in_progress',
  currentStep: (state) => {
    return state.steps[state.currentStepIndex] || null;
  },
  currentStepData: (state) => {
    if (!state.currentStepName) return null;
    return state.stepData[state.currentStepName] || null;
  },
  progressPercentage: (state) => state.progressPercentage,
  canGoNext: (state) => {
    return state.currentStepIndex < state.steps.length - 1;
  },
  canGoBack: (state) => {
    return state.currentStepIndex > 0;
  },
  isLoading: (state) => state.loading,
  hasError: (state) => !!state.error,
  errorMessage: (state) => state.error,
};

const actions = {
  async fetchOnboardingStatus({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.getStatus();
      const data = response.data;

      commit('SET_STATUS', {
        completed: data.onboarding_completed,
        focusArea: data.focus_area,
        currentStep: data.current_step,
        progressPercentage: data.progress_percentage,
        totalSteps: data.total_steps,
        completedSteps: data.completed_steps,
      });

      // If user has a focus area, fetch the steps
      if (data.focus_area) {
        await actions.fetchSteps({ commit });
      }

      return data;
    } catch (error) {
      const errorMessage = error.message || 'Failed to fetch onboarding status';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async setFocusArea({ commit, dispatch }, focusArea) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.setFocusArea(focusArea);
      const data = response.data;

      commit('SET_FOCUS_AREA', focusArea);
      commit('SET_CURRENT_STEP', data.current_step);
      commit('SET_STATUS', { completed: false, focusArea });

      // Fetch steps for this focus area
      await dispatch('fetchSteps');

      return data;
    } catch (error) {
      const errorMessage = error.message || 'Failed to set focus area';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async fetchSteps({ commit, state }) {
    if (!state.focusArea) {
      return;
    }

    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.getSteps();
      const steps = response.data.steps;

      commit('SET_STEPS', steps);

      // Set current step index based on current step name
      if (state.currentStepName) {
        const index = steps.findIndex((s) => s.name === state.currentStepName);
        if (index !== -1) {
          commit('SET_CURRENT_STEP_INDEX', index);
        }
      }

      return steps;
    } catch (error) {
      const errorMessage = error.message || 'Failed to fetch steps';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async fetchStepData({ commit }, stepName) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.getStepData(stepName);
      const data = response.data;

      commit('UPDATE_STEP_DATA', { stepName, data });

      return data;
    } catch (error) {
      const errorMessage = error.message || 'Failed to fetch step data';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async saveStepData({ commit, state }, { stepName, data }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.saveStepProgress(stepName, data);
      const result = response.data;

      commit('UPDATE_STEP_DATA', { stepName, data });
      commit('SET_PROGRESS_PERCENTAGE', result.progress_percentage);

      return result;
    } catch (error) {
      const errorMessage = error.message || 'Failed to save step data';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async skipStep({ commit, state }, stepName) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.skipStep(stepName);
      const result = response.data;

      commit('SET_PROGRESS_PERCENTAGE', result.progress_percentage);
      commit('HIDE_SKIP_MODAL');

      return result;
    } catch (error) {
      const errorMessage = error.message || 'Failed to skip step';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async showSkipConfirmation({ commit }, stepName) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.getSkipReason(stepName);
      const skipReason = response.data.skip_reason;

      commit('SHOW_SKIP_MODAL', { stepName, reason: skipReason });
    } catch (error) {
      const errorMessage = error.message || 'Failed to fetch skip reason';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  hideSkipConfirmation({ commit }) {
    commit('HIDE_SKIP_MODAL');
  },

  async goToNextStep({ commit, state, dispatch }) {
    if (state.currentStepIndex < state.steps.length - 1) {
      const nextIndex = state.currentStepIndex + 1;
      const nextStep = state.steps[nextIndex];

      commit('SET_CURRENT_STEP_INDEX', nextIndex);
      commit('SET_CURRENT_STEP', nextStep.name);

      // Fetch data for next step if it exists
      await dispatch('fetchStepData', nextStep.name);
    }
  },

  async goToPreviousStep({ commit, state, dispatch }) {
    if (state.currentStepIndex > 0) {
      const prevIndex = state.currentStepIndex - 1;
      const prevStep = state.steps[prevIndex];

      commit('SET_CURRENT_STEP_INDEX', prevIndex);
      commit('SET_CURRENT_STEP', prevStep.name);

      // Fetch data for previous step if it exists
      await dispatch('fetchStepData', prevStep.name);
    }
  },

  async completeOnboarding({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.completeOnboarding();
      const data = response.data;

      commit('SET_STATUS', { completed: true });

      return data;
    } catch (error) {
      const errorMessage = error.message || 'Failed to complete onboarding';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },

  async restartOnboarding({ commit }) {
    commit('SET_LOADING', true);
    commit('SET_ERROR', null);

    try {
      const response = await onboardingService.restartOnboarding();
      const data = response.data;

      commit('RESET_STATE');

      return data;
    } catch (error) {
      const errorMessage = error.message || 'Failed to restart onboarding';
      commit('SET_ERROR', errorMessage);
      throw error;
    } finally {
      commit('SET_LOADING', false);
    }
  },
};

const mutations = {
  SET_STATUS(state, { completed, focusArea, currentStep, progressPercentage, totalSteps }) {
    state.status = completed ? 'completed' : focusArea ? 'in_progress' : null;
    if (focusArea !== undefined) state.focusArea = focusArea;
    if (currentStep !== undefined) state.currentStepName = currentStep;
    if (progressPercentage !== undefined) state.progressPercentage = progressPercentage;
    if (totalSteps !== undefined) state.totalSteps = totalSteps;
  },

  SET_FOCUS_AREA(state, focusArea) {
    state.focusArea = focusArea;
    state.status = 'in_progress';
  },

  SET_CURRENT_STEP(state, stepName) {
    state.currentStepName = stepName;
  },

  SET_CURRENT_STEP_INDEX(state, index) {
    state.currentStepIndex = index;
  },

  SET_STEPS(state, steps) {
    state.steps = steps;
    state.totalSteps = steps.length;
  },

  UPDATE_STEP_DATA(state, { stepName, data }) {
    state.stepData = {
      ...state.stepData,
      [stepName]: data,
    };
  },

  SET_PROGRESS_PERCENTAGE(state, percentage) {
    state.progressPercentage = percentage;
  },

  SET_LOADING(state, loading) {
    state.loading = loading;
  },

  SET_ERROR(state, error) {
    state.error = error;
  },

  SHOW_SKIP_MODAL(state, { stepName, reason }) {
    state.showSkipModal = true;
    state.skipStepName = stepName;
    state.currentSkipReason = reason;
  },

  HIDE_SKIP_MODAL(state) {
    state.showSkipModal = false;
    state.skipStepName = '';
    state.currentSkipReason = '';
  },

  RESET_STATE(state) {
    state.status = null;
    state.focusArea = null;
    state.currentStepIndex = 0;
    state.currentStepName = null;
    state.totalSteps = 0;
    state.steps = [];
    state.stepData = {};
    state.progressPercentage = 0;
    state.showSkipModal = false;
    state.currentSkipReason = '';
    state.skipStepName = '';
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
