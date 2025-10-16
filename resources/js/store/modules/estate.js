import estateService from '@/services/estateService';

const state = {
    assets: [],
    investmentAccounts: [], // Investment accounts from Investment module
    liabilities: [],
    gifts: [],
    trusts: [],
    ihtProfile: null,
    netWorth: null,
    cashFlow: null,
    analysis: null,
    recommendations: [],
    loading: false,
    error: null,
};

const getters = {
    // All assets (manual + investment accounts)
    allAssets: (state) => {
        const manualAssets = Array.isArray(state.assets) ? state.assets : [];
        const investmentAssets = Array.isArray(state.investmentAccounts) ? state.investmentAccounts : [];
        return [...manualAssets, ...investmentAssets].filter(asset => asset != null);
    },

    // Total assets value (including investment accounts)
    totalAssets: (state, getters) => {
        return getters.allAssets.reduce((sum, asset) => {
            if (!asset || asset.current_value === undefined) return sum;
            return sum + parseFloat(asset.current_value || 0);
        }, 0);
    },

    // Total liabilities value
    totalLiabilities: (state) => {
        return state.liabilities.reduce((sum, liability) => sum + parseFloat(liability.current_balance || 0), 0);
    },

    // Net worth value
    netWorthValue: (state, getters) => {
        return getters.totalAssets - getters.totalLiabilities;
    },

    // IHT liability from analysis
    ihtLiability: (state) => {
        return state.analysis?.iht_liability || 0;
    },

    // Gifts made within last 7 years (PETs)
    giftsWithin7Years: (state) => {
        const sevenYearsAgo = new Date();
        sevenYearsAgo.setFullYear(sevenYearsAgo.getFullYear() - 7);

        return state.gifts.filter(gift => {
            const giftDate = new Date(gift.gift_date);
            return giftDate >= sevenYearsAgo;
        });
    },

    // Total value of gifts within 7 years
    giftsWithin7YearsValue: (state, getters) => {
        return getters.giftsWithin7Years.reduce((sum, gift) => sum + parseFloat(gift.gift_value || 0), 0);
    },

    // Assets by type (including investment accounts)
    assetsByType: (state, getters) => {
        const byType = {};
        getters.allAssets.forEach(asset => {
            const type = asset.asset_type || 'Other';
            if (!byType[type]) {
                byType[type] = [];
            }
            byType[type].push(asset);
        });
        return byType;
    },

    // Liabilities by type
    liabilitiesByType: (state) => {
        const byType = {};
        state.liabilities.forEach(liability => {
            const type = liability.liability_type || 'Other';
            if (!byType[type]) {
                byType[type] = [];
            }
            byType[type].push(liability);
        });
        return byType;
    },

    // IHT exempt assets (including investment accounts)
    ihtExemptAssets: (state, getters) => {
        return getters.allAssets.filter(asset => asset.is_iht_exempt);
    },

    // High priority recommendations
    priorityRecommendations: (state) => {
        return state.recommendations.filter(rec => rec.priority === 'high');
    },

    // Alias for netWorthValue (for Dashboard compatibility)
    netWorth: (state, getters) => {
        return getters.netWorthValue;
    },

    // Probate readiness score (0-100)
    probateReadiness: (state) => {
        // Calculate based on having key documents and estate plan
        let score = 0;

        // Has assets recorded (+40)
        if (state.assets.length > 0) score += 40;

        // Has IHT analysis (+30)
        if (state.analysis) score += 30;

        // Has addressed high priority recommendations (+30)
        const highPriority = state.recommendations.filter(rec => rec.priority === 'high');
        if (highPriority.length === 0) score += 30;

        return score;
    },

    // Taxable estate value (for IHT calculation)
    taxableEstate: (state, getters) => {
        const totalAssets = getters.totalAssets;
        const exemptAssets = getters.ihtExemptAssets.reduce((sum, asset) =>
            sum + parseFloat(asset.current_value || 0), 0);
        return totalAssets - exemptAssets;
    },

    loading: (state) => state.loading,
    error: (state) => state.error,
};

const actions = {
    // Fetch all estate data
    async fetchEstateData({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.getEstateData();
            commit('setAssets', response.data.assets || []);
            commit('setInvestmentAccounts', response.data.investment_accounts || []);
            commit('setLiabilities', response.data.liabilities || []);
            commit('setGifts', response.data.gifts || []);
            commit('setTrusts', response.data.trusts || []);
            commit('setIHTProfile', response.data.iht_profile);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch estate data';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Analyze estate
    async analyzeEstate({ commit }, data) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.analyzeEstate(data);
            commit('setAnalysis', response.data);
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
            const response = await estateService.getRecommendations();
            commit('setRecommendations', response.data.recommendations || []);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch recommendations';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Calculate IHT
    async calculateIHT({ commit }, data) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.calculateIHT(data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'IHT calculation failed';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Fetch net worth
    async fetchNetWorth({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.getNetWorth();
            commit('setNetWorth', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch net worth';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Fetch cash flow
    async fetchCashFlow({ commit }, taxYear) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.getCashFlow(taxYear);
            // response is already response.data from the service
            // which is { success: true, data: {...} }
            commit('setCashFlow', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to fetch cash flow';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Asset actions
    async createAsset({ commit }, assetData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.createAsset(assetData);
            commit('addAsset', response.data.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create asset';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateAsset({ commit }, { id, assetData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.updateAsset(id, assetData);
            commit('updateAsset', response.data.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update asset';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteAsset({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.deleteAsset(id);
            commit('removeAsset', id);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete asset';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Liability actions
    async createLiability({ commit }, liabilityData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.createLiability(liabilityData);
            commit('addLiability', response.data.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create liability';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateLiability({ commit }, { id, liabilityData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.updateLiability(id, liabilityData);
            commit('updateLiability', response.data.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update liability';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteLiability({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.deleteLiability(id);
            commit('removeLiability', id);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete liability';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Gift actions
    async createGift({ commit }, giftData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.createGift(giftData);
            commit('addGift', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to create gift';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateGift({ commit }, { id, giftData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.updateGift(id, giftData);
            commit('updateGift', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || 'Failed to update gift';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteGift({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.deleteGift(id);
            commit('removeGift', id);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete gift';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Trust actions
    async fetchTrusts({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.getTrusts();
            commit('setTrusts', response.data || []);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch trusts';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async createTrust({ commit }, trustData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.createTrust(trustData);
            commit('addTrust', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create trust';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateTrust({ commit }, { id, data }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.updateTrust(id, data);
            commit('updateTrust', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update trust';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async removeTrust({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await estateService.deleteTrust(id);
            commit('removeTrust', id);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete trust';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },
};

const mutations = {
    setAssets(state, assets) {
        state.assets = assets;
    },

    setInvestmentAccounts(state, investmentAccounts) {
        state.investmentAccounts = investmentAccounts;
    },

    setLiabilities(state, liabilities) {
        state.liabilities = liabilities;
    },

    setGifts(state, gifts) {
        state.gifts = gifts;
    },

    setTrusts(state, trusts) {
        state.trusts = trusts;
    },

    setIHTProfile(state, profile) {
        state.ihtProfile = profile;
    },

    setNetWorth(state, netWorth) {
        state.netWorth = netWorth;
    },

    setCashFlow(state, cashFlow) {
        state.cashFlow = cashFlow;
    },

    setAnalysis(state, analysis) {
        state.analysis = analysis;
    },

    setRecommendations(state, recommendations) {
        state.recommendations = recommendations;
    },

    addAsset(state, asset) {
        state.assets.push(asset);
    },

    updateAsset(state, asset) {
        const index = state.assets.findIndex(a => a.id === asset.id);
        if (index !== -1) {
            state.assets.splice(index, 1, asset);
        }
    },

    removeAsset(state, id) {
        const index = state.assets.findIndex(a => a.id === id);
        if (index !== -1) {
            state.assets.splice(index, 1);
        }
    },

    addLiability(state, liability) {
        state.liabilities.push(liability);
    },

    updateLiability(state, liability) {
        const index = state.liabilities.findIndex(l => l.id === liability.id);
        if (index !== -1) {
            state.liabilities.splice(index, 1, liability);
        }
    },

    removeLiability(state, id) {
        const index = state.liabilities.findIndex(l => l.id === id);
        if (index !== -1) {
            state.liabilities.splice(index, 1);
        }
    },

    addGift(state, gift) {
        state.gifts.push(gift);
    },

    updateGift(state, gift) {
        const index = state.gifts.findIndex(g => g.id === gift.id);
        if (index !== -1) {
            state.gifts.splice(index, 1, gift);
        }
    },

    removeGift(state, id) {
        const index = state.gifts.findIndex(g => g.id === id);
        if (index !== -1) {
            state.gifts.splice(index, 1);
        }
    },

    addTrust(state, trust) {
        state.trusts.push(trust);
    },

    updateTrust(state, trust) {
        const index = state.trusts.findIndex(t => t.id === trust.id);
        if (index !== -1) {
            state.trusts.splice(index, 1, trust);
        }
    },

    removeTrust(state, id) {
        const index = state.trusts.findIndex(t => t.id === id);
        if (index !== -1) {
            state.trusts.splice(index, 1);
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
