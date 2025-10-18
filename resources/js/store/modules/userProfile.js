import userProfileService from '@/services/userProfileService';

const state = {
  profile: null,
  personalInfo: null,
  familyMembers: [],
  incomeOccupation: null,
  personalAccounts: {
    profitAndLoss: null,
    cashflow: null,
    balanceSheet: null,
  },
  loading: false,
  error: null,
};

const getters = {
  profile: (state) => state.profile,
  personalInfo: (state) => state.personalInfo,
  familyMembers: (state) => state.familyMembers,
  incomeOccupation: (state) => state.incomeOccupation,
  totalAnnualIncome: (state) => {
    if (!state.incomeOccupation) return 0;
    return state.incomeOccupation.total_annual_income || 0;
  },
  personalAccounts: (state) => state.personalAccounts,
  loading: (state) => state.loading,
  error: (state) => state.error,
};

const actions = {
  /**
   * Fetch complete user profile
   */
  async fetchProfile({ commit }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.getProfile();

      if (response.success) {
        commit('setProfile', response.data);
        commit('setPersonalInfo', response.data.personal_info);
        commit('setIncomeOccupation', response.data.income_occupation);
        commit('setFamilyMembers', response.data.family_members || []);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch profile';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Update personal information
   */
  async updatePersonalInfo({ commit }, data) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.updatePersonalInfo(data);

      if (response.success) {
        commit('setPersonalInfo', response.data.user);

        // Update auth store user as well
        commit('auth/setUser', response.data.user, { root: true });
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update personal information';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Update income and occupation information
   */
  async updateIncomeOccupation({ commit }, data) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.updateIncomeOccupation(data);

      if (response.success) {
        commit('setIncomeOccupation', response.data.user);

        // Update auth store user as well
        commit('auth/setUser', response.data.user, { root: true });
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update income/occupation';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Fetch family members
   */
  async fetchFamilyMembers({ commit }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.getFamilyMembers();

      if (response.success) {
        commit('setFamilyMembers', response.data.family_members || []);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch family members';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Add a new family member
   */
  async addFamilyMember({ commit }, data) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.createFamilyMember(data);

      if (response.success) {
        commit('addFamilyMember', response.data.family_member);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to add family member';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Update a family member
   */
  async updateFamilyMember({ commit }, { id, data }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.updateFamilyMember(id, data);

      if (response.success) {
        commit('updateFamilyMember', response.data.family_member);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update family member';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Delete a family member
   */
  async deleteFamilyMember({ commit }, id) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.deleteFamilyMember(id);

      if (response.success) {
        commit('removeFamilyMember', id);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to delete family member';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Calculate personal accounts
   */
  async calculatePersonalAccounts({ commit }, params = {}) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.calculatePersonalAccounts(params);

      if (response.success) {
        commit('setPersonalAccounts', response.data);
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to calculate personal accounts';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Add a line item to personal accounts
   */
  async addLineItem({ commit, dispatch }, data) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.createLineItem(data);

      if (response.success) {
        // Recalculate accounts after adding line item
        await dispatch('calculatePersonalAccounts');
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to add line item';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Update a line item
   */
  async updateLineItem({ commit, dispatch }, { id, data }) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.updateLineItem(id, data);

      if (response.success) {
        // Recalculate accounts after updating line item
        await dispatch('calculatePersonalAccounts');
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to update line item';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },

  /**
   * Delete a line item
   */
  async deleteLineItem({ commit, dispatch }, id) {
    commit('setLoading', true);
    commit('setError', null);

    try {
      const response = await userProfileService.deleteLineItem(id);

      if (response.success) {
        // Recalculate accounts after deleting line item
        await dispatch('calculatePersonalAccounts');
      }

      return response;
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to delete line item';
      commit('setError', errorMessage);
      throw error;
    } finally {
      commit('setLoading', false);
    }
  },
};

const mutations = {
  setProfile(state, profile) {
    state.profile = profile;
  },

  setPersonalInfo(state, personalInfo) {
    state.personalInfo = personalInfo;
  },

  setFamilyMembers(state, familyMembers) {
    state.familyMembers = familyMembers;
  },

  setIncomeOccupation(state, incomeOccupation) {
    state.incomeOccupation = incomeOccupation;
  },

  setPersonalAccounts(state, data) {
    state.personalAccounts = {
      profitAndLoss: data.profit_and_loss || null,
      cashflow: data.cashflow || null,
      balanceSheet: data.balance_sheet || null,
    };
  },

  addFamilyMember(state, familyMember) {
    state.familyMembers.push(familyMember);
  },

  updateFamilyMember(state, updatedMember) {
    const index = state.familyMembers.findIndex(m => m.id === updatedMember.id);
    if (index !== -1) {
      state.familyMembers.splice(index, 1, updatedMember);
    }
  },

  removeFamilyMember(state, id) {
    state.familyMembers = state.familyMembers.filter(m => m.id !== id);
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
