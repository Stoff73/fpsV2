import protectionService from '@/services/protectionService';

const state = {
    profile: null,
    policies: {
        life: [],
        criticalIllness: [],
        incomeProtection: [],
        disability: [],
        sicknessIllness: [],
    },
    analysis: null,
    recommendations: [],
    loading: false,
    error: null,
};

const getters = {
    // Get adequacy score from analysis
    adequacyScore: (state) => {
        return state.analysis?.adequacy_score || 0;
    },

    // Get total coverage across all policy types
    totalCoverage: (state) => {
        const lifeCoverage = state.policies.life.reduce((sum, policy) => sum + parseFloat(policy.sum_assured || 0), 0);
        const criticalIllnessCoverage = state.policies.criticalIllness.reduce((sum, policy) => sum + parseFloat(policy.sum_assured || 0), 0);
        return lifeCoverage + criticalIllnessCoverage;
    },

    // Get total premium across all policy types
    totalPremium: (state) => {
        let total = 0;

        Object.values(state.policies).forEach(policyType => {
            total += policyType.reduce((sum, policy) => {
                const premium = parseFloat(policy.premium_amount || 0);
                const frequency = policy.premium_frequency || 'monthly';

                // Convert all premiums to monthly for consistency
                if (frequency === 'monthly') {
                    return sum + premium;
                } else if (frequency === 'annual') {
                    return sum + (premium / 12);
                }
                return sum + premium;
            }, 0);
        });

        return total;
    },

    // Get coverage gaps from analysis
    coverageGaps: (state) => {
        return state.analysis?.gaps || [];
    },

    // Get high priority recommendations
    priorityRecommendations: (state) => {
        return state.recommendations.filter(rec => rec.priority === 'high');
    },

    // Get all policies as a flat array with type indicator
    allPolicies: (state) => {
        const allPolicies = [];

        Object.entries(state.policies).forEach(([type, policies]) => {
            policies.forEach(policy => {
                allPolicies.push({
                    ...policy,
                    policy_type: type,
                });
            });
        });

        return allPolicies;
    },

    // Premium breakdown by policy type
    premiumBreakdown: (state) => {
        const breakdown = {
            life: 0,
            criticalIllness: 0,
            incomeProtection: 0,
            disability: 0,
            sicknessIllness: 0,
        };

        Object.entries(state.policies).forEach(([type, policies]) => {
            breakdown[type] = policies.reduce((sum, policy) => {
                const premium = parseFloat(policy.premium_amount || 0);
                const frequency = policy.premium_frequency || 'monthly';

                // Convert to monthly
                if (frequency === 'monthly') {
                    return sum + premium;
                } else if (frequency === 'annual') {
                    return sum + (premium / 12);
                }
                return sum + premium;
            }, 0);
        });

        return breakdown;
    },

    loading: (state) => state.loading,
    error: (state) => state.error,
};

const actions = {
    // Fetch all protection data
    async fetchProtectionData({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.getProtectionData();
            const data = response.data || response;
            commit('setProfile', data.profile || null);
            commit('setPolicies', data.policies || {});
            commit('setAnalysis', data.analysis || null);
            return response;
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch protection data';
            commit('setError', errorMessage);
            console.error('Protection data fetch error:', error);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Analyze protection coverage
    async analyzeProtection({ commit }, data) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.analyzeProtection(data);
            commit('setAnalysis', response.data.analysis);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Analysis failed';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Fetch recommendations
    async fetchRecommendations({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.getRecommendations();
            commit('setRecommendations', response.data.recommendations);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch recommendations';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Create policy (dispatches to specific policy type action)
    async createPolicy({ dispatch }, { policyType, policyData }) {
        const actionMap = {
            life: 'createLifePolicy',
            criticalIllness: 'createCriticalIllnessPolicy',
            incomeProtection: 'createIncomeProtectionPolicy',
            disability: 'createDisabilityPolicy',
            sicknessIllness: 'createSicknessIllnessPolicy',
        };

        const action = actionMap[policyType];
        if (!action) {
            throw new Error(`Unknown policy type: ${policyType}`);
        }

        return dispatch(action, policyData);
    },

    // Update policy (dispatches to specific policy type action)
    async updatePolicy({ dispatch }, { policyType, id, policyData }) {
        const actionMap = {
            life: 'updateLifePolicy',
            criticalIllness: 'updateCriticalIllnessPolicy',
            incomeProtection: 'updateIncomeProtectionPolicy',
            disability: 'updateDisabilityPolicy',
            sicknessIllness: 'updateSicknessIllnessPolicy',
        };

        const action = actionMap[policyType];
        if (!action) {
            throw new Error(`Unknown policy type: ${policyType}`);
        }

        return dispatch(action, { id, policyData });
    },

    // Delete policy (dispatches to specific policy type action)
    async deletePolicy({ dispatch }, { policyType, id }) {
        const actionMap = {
            life: 'deleteLifePolicy',
            criticalIllness: 'deleteCriticalIllnessPolicy',
            incomeProtection: 'deleteIncomeProtectionPolicy',
            disability: 'deleteDisabilityPolicy',
            sicknessIllness: 'deleteSicknessIllnessPolicy',
        };

        const action = actionMap[policyType];
        if (!action) {
            throw new Error(`Unknown policy type: ${policyType}`);
        }

        return dispatch(action, id);
    },

    // Life Insurance Policy Actions
    async createLifePolicy({ commit }, policyData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.createLifePolicy(policyData);
            // Backend returns: { success: true, message: "...", data: { policy object } }
            // Service returns response.data, so response = { success, message, data }
            // The actual policy is in response.data.data
            const policy = response.data || response;
            commit('addPolicy', { type: 'life', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create life insurance policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateLifePolicy({ commit }, { id, policyData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.updateLifePolicy(id, policyData);
            const policy = response.data || response;
            commit('updatePolicy', { type: 'life', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update life insurance policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteLifePolicy({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.deleteLifePolicy(id);
            commit('removePolicy', { type: 'life', id });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete life insurance policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Critical Illness Policy Actions
    async createCriticalIllnessPolicy({ commit }, policyData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.createCriticalIllnessPolicy(policyData);
            const policy = response.data || response;
            commit('addPolicy', { type: 'criticalIllness', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create critical illness policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateCriticalIllnessPolicy({ commit }, { id, policyData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.updateCriticalIllnessPolicy(id, policyData);
            const policy = response.data || response;
            commit('updatePolicy', { type: 'criticalIllness', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update critical illness policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteCriticalIllnessPolicy({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.deleteCriticalIllnessPolicy(id);
            commit('removePolicy', { type: 'criticalIllness', id });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete critical illness policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Income Protection Policy Actions
    async createIncomeProtectionPolicy({ commit }, policyData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.createIncomeProtectionPolicy(policyData);
            const policy = response.data || response;
            commit('addPolicy', { type: 'incomeProtection', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create income protection policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateIncomeProtectionPolicy({ commit }, { id, policyData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.updateIncomeProtectionPolicy(id, policyData);
            const policy = response.data || response;
            commit('updatePolicy', { type: 'incomeProtection', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update income protection policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteIncomeProtectionPolicy({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.deleteIncomeProtectionPolicy(id);
            commit('removePolicy', { type: 'incomeProtection', id });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete income protection policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Disability Policy Actions
    async createDisabilityPolicy({ commit }, policyData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.createDisabilityPolicy(policyData);
            const policy = response.data || response;
            commit('addPolicy', { type: 'disability', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create disability policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateDisabilityPolicy({ commit }, { id, policyData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.updateDisabilityPolicy(id, policyData);
            const policy = response.data || response;
            commit('updatePolicy', { type: 'disability', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update disability policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteDisabilityPolicy({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.deleteDisabilityPolicy(id);
            commit('removePolicy', { type: 'disability', id });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete disability policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Sickness/Illness Policy Actions
    async createSicknessIllnessPolicy({ commit }, policyData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.createSicknessIllnessPolicy(policyData);
            const policy = response.data || response;
            commit('addPolicy', { type: 'sicknessIllness', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create sickness/illness policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateSicknessIllnessPolicy({ commit }, { id, policyData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.updateSicknessIllnessPolicy(id, policyData);
            const policy = response.data || response;
            commit('updatePolicy', { type: 'sicknessIllness', policy });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update sickness/illness policy';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteSicknessIllnessPolicy({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await protectionService.deleteSicknessIllnessPolicy(id);
            commit('removePolicy', { type: 'sicknessIllness', id });
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete sickness/illness policy';
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

    setPolicies(state, policies) {
        state.policies = {
            life: policies.life_insurance || policies.life || [],
            criticalIllness: policies.critical_illness || [],
            incomeProtection: policies.income_protection || [],
            disability: policies.disability || [],
            sicknessIllness: policies.sickness_illness || [],
        };
    },

    setAnalysis(state, analysis) {
        state.analysis = analysis;
    },

    setRecommendations(state, recommendations) {
        state.recommendations = recommendations;
    },

    addPolicy(state, { type, policy }) {
        state.policies[type].push(policy);
    },

    updatePolicy(state, { type, policy }) {
        const index = state.policies[type].findIndex(p => p.id === policy.id);
        if (index !== -1) {
            state.policies[type].splice(index, 1, policy);
        }
    },

    removePolicy(state, { type, id }) {
        const index = state.policies[type].findIndex(p => p.id === id);
        if (index !== -1) {
            state.policies[type].splice(index, 1);
        }
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
