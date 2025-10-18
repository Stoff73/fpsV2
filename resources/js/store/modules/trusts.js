import axios from '../../bootstrap';

const state = {
  trusts: [],
  selectedTrust: null,
  trustAssets: null,
  loading: false,
  error: null,
};

const getters = {
  activeTrusts: (state) => state.trusts.filter(t => t.is_active),
  inactiveTrusts: (state) => state.trusts.filter(t => !t.is_active),
  relevantPropertyTrusts: (state) => state.trusts.filter(t => t.is_relevant_property_trust),
  totalTrustValue: (state) => {
    return state.trusts.reduce((sum, trust) => {
      return sum + (trust.total_asset_value || trust.current_value || 0);
    }, 0);
  },
  getTrustById: (state) => (id) => {
    return state.trusts.find(t => t.id === id);
  },
};

const actions = {
  async fetchTrusts({ commit }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.get('/api/estate/trusts');
      commit('setTrusts', response.data.data);
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to fetch trusts';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async fetchTrustById({ commit }, id) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      // In a full implementation, you'd have a specific endpoint for this
      // For now, fetch all and filter
      const response = await axios.get('/api/estate/trusts');
      const trust = response.data.data.find(t => t.id === parseInt(id));
      commit('setSelectedTrust', trust);
      return trust;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to fetch trust';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async fetchTrustAssets({ commit }, trustId) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.get(`/api/estate/trusts/${trustId}/assets`);
      commit('setTrustAssets', response.data.data);
      return response.data.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to fetch trust assets';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async createTrust({ commit, dispatch }, trustData) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.post('/api/estate/trusts', trustData);
      await dispatch('fetchTrusts'); // Refresh list
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to create trust';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async updateTrust({ commit, dispatch }, { id, data }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.put(`/api/estate/trusts/${id}`, data);
      await dispatch('fetchTrusts'); // Refresh list
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to update trust';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async deleteTrust({ commit, dispatch }, id) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.delete(`/api/estate/trusts/${id}`);
      await dispatch('fetchTrusts'); // Refresh list
      return response.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to delete trust';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async calculateTrustIHTImpact({ commit }, trustId) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.post(`/api/estate/trusts/${trustId}/calculate-iht-impact`);
      return response.data.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to calculate IHT impact';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  async fetchUpcomingTaxReturns({ commit }, monthsAhead = 12) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await axios.get('/api/estate/trusts/upcoming-tax-returns', {
        params: { months_ahead: monthsAhead },
      });
      return response.data.data;
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'Failed to fetch tax returns';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },
};

const mutations = {
  setTrusts(state, trusts) {
    state.trusts = trusts;
  },

  setSelectedTrust(state, trust) {
    state.selectedTrust = trust;
  },

  setTrustAssets(state, assets) {
    state.trustAssets = assets;
  },

  setLoading(state, loading) {
    state.loading = loading;
  },

  setError(state, error) {
    state.error = error;
  },

  clearError(state) {
    state.error = null;
  },
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
};
