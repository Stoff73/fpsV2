import authService from '@/services/authService';

const state = {
  token: authService.getToken(),
  user: authService.getStoredUser(),
  loading: false,
  error: null,
};

const getters = {
  isAuthenticated: (state) => !!state.token,
  currentUser: (state) => state.user,
  isAdmin: (state) => state.user?.role === 'admin',
  loading: (state) => state.loading,
  error: (state) => state.error,
};

const actions = {
  async register({ commit }, userData) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await authService.register(userData);
      commit('setAuth', {
        token: response.data.access_token,
        user: response.data.user,
      });
      return response;
    } catch (error) {
      const errorMessage = error.message || 'Registration failed';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async login({ commit }, credentials) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await authService.login(credentials);
      commit('setAuth', {
        token: response.data.access_token,
        user: response.data.user,
      });
      return response;
    } catch (error) {
      const errorMessage = error.message || 'Login failed';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async logout({ commit }) {
    commit('setLoading', true);

    try {
      await authService.logout();
      commit('clearAuth');
    } catch (error) {
      console.error('Logout error:', error);
      commit('clearAuth');
    } finally {
      commit('setLoading', false);
    }
  },

  async fetchUser({ commit }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const user = await authService.getUser();
      commit('setUser', user);
      return user;
    } catch (error) {
      const errorMessage = error.message || 'Failed to fetch user';
      commit('setError', errorMessage);
      commit('clearAuth');
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },
};

const mutations = {
  setAuth(state, { token, user }) {
    state.token = token;
    state.user = user;
  },

  setUser(state, user) {
    state.user = user;
  },

  clearAuth(state) {
    state.token = null;
    state.user = null;
  },

  setLoading(state, loading) {
    state.loading = loading;
  },

  setError(state, error) {
    state.error = error;
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
