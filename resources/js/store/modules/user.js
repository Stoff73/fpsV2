const state = {
  profile: null,
  preferences: {},
};

const getters = {
  profile: (state) => state.profile,
  preferences: (state) => state.preferences,
  fullName: (state) => state.profile?.name || '',
  email: (state) => state.profile?.email || '',
};

const actions = {
  updateProfile({ commit }, profileData) {
    commit('setProfile', profileData);
  },

  updatePreferences({ commit }, preferences) {
    commit('setPreferences', preferences);
  },

  clearUserData({ commit }) {
    commit('clearProfile');
    commit('clearPreferences');
  },
};

const mutations = {
  setProfile(state, profile) {
    state.profile = profile;
  },

  setPreferences(state, preferences) {
    state.preferences = { ...state.preferences, ...preferences };
  },

  clearProfile(state) {
    state.profile = null;
  },

  clearPreferences(state) {
    state.preferences = {};
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
