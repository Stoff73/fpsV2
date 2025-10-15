import investmentService from '@/services/investmentService';
import { pollMonteCarloJob } from '@/utils/poller';

const state = {
    accounts: [],
    goals: [],
    riskProfile: null,
    analysis: null,
    recommendations: [],
    monteCarloResults: {},      // Keyed by jobId
    monteCarloStatus: {},        // Keyed by jobId
    scenarios: null,
    loading: false,
    error: null,
};

const getters = {
    // Get accounts
    accounts: (state) => state.accounts,

    // Get total portfolio value across all accounts
    totalPortfolioValue: (state) => {
        if (state.analysis?.portfolio_summary?.total_value) {
            return state.analysis.portfolio_summary.total_value;
        }
        return state.accounts.reduce((sum, account) => {
            return sum + parseFloat(account.current_value || 0);
        }, 0);
    },

    // Get YTD return percentage
    ytdReturn: (state) => {
        return state.analysis?.returns?.ytd_return || 0;
    },

    // Get asset allocation data
    assetAllocation: (state) => {
        return state.analysis?.asset_allocation || [];
    },

    // Get total annual fees
    totalFees: (state) => {
        return state.analysis?.fee_analysis?.total_annual_fees || 0;
    },

    // Get fee drag percentage
    feeDragPercent: (state) => {
        return state.analysis?.fee_analysis?.fee_drag_percent || 0;
    },

    // Get unrealized gains
    unrealizedGains: (state) => {
        return state.analysis?.tax_efficiency?.unrealized_gains?.total_unrealized_gains || 0;
    },

    // Get tax efficiency score
    taxEfficiencyScore: (state) => {
        return state.analysis?.tax_efficiency?.efficiency_score || 0;
    },

    // Get diversification score
    diversificationScore: (state) => {
        return state.analysis?.diversification_score || 0;
    },

    // Get risk level
    riskLevel: (state) => {
        return state.analysis?.risk_metrics?.risk_level || 'medium';
    },

    // Get all holdings across all accounts
    allHoldings: (state) => {
        return state.accounts.flatMap(account => account.holdings || []);
    },

    // Get holdings count
    holdingsCount: (state, getters) => {
        return getters.allHoldings.length;
    },

    // Get accounts count
    accountsCount: (state) => {
        return state.accounts.length;
    },

    // Get ISA accounts
    isaAccounts: (state) => {
        return state.accounts.filter(account => account.account_type === 'isa');
    },

    // Get total ISA value
    totalISAValue: (state, getters) => {
        return getters.isaAccounts.reduce((sum, account) => {
            return sum + parseFloat(account.current_value || 0);
        }, 0);
    },

    // Get ISA percentage of total portfolio
    isaPercentage: (state, getters) => {
        const totalValue = getters.totalPortfolioValue;
        if (totalValue === 0) return 0;
        return Math.round((getters.totalISAValue / totalValue) * 100);
    },

    // Get current year ISA subscription (S&S ISA) from ISA accounts
    investmentISASubscription: (state, getters) => {
        return getters.isaAccounts.reduce((sum, account) => {
            return sum + parseFloat(account.isa_subscription_current_year || 0);
        }, 0);
    },

    // Get goals that are on track
    goalsOnTrack: (state) => {
        return state.goals.filter(goal => {
            const progress = (goal.current_value / goal.target_amount) * 100;
            // Simplified - in real app would calculate based on time elapsed
            return progress >= 50;
        });
    },

    // Get Monte Carlo result by job ID
    getMonteCarloResult: (state) => (jobId) => {
        return state.monteCarloResults[jobId] || null;
    },

    // Get Monte Carlo status by job ID
    getMonteCarloStatus: (state) => (jobId) => {
        return state.monteCarloStatus[jobId] || 'unknown';
    },

    // Check if needs rebalancing
    needsRebalancing: (state) => {
        return state.analysis?.allocation_deviation?.needs_rebalancing || false;
    },

    loading: (state) => state.loading,
    error: (state) => state.error,
};

const actions = {
    // Fetch all investment data
    async fetchInvestmentData({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.getInvestmentData();
            commit('setAccounts', response.data.accounts);
            commit('setGoals', response.data.goals);
            commit('setRiskProfile', response.data.risk_profile);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to fetch investment data';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Analyze investment portfolio
    async analyzeInvestment({ commit }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.analyzeInvestment();
            commit('setAnalysis', response.data.analysis);
            commit('setRecommendations', response.data.recommendations);
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
            const response = await investmentService.getRecommendations();
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

    // Run scenario analysis
    async runScenario({ commit }, scenarioData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.runScenario(scenarioData);
            commit('setScenarios', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Scenario analysis failed';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Start Monte Carlo simulation
    async startMonteCarlo({ commit }, params) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.startMonteCarlo(params);
            const jobId = response.data.job_id;
            commit('setMonteCarloStatus', { jobId, status: 'queued' });
            return { jobId, response };
        } catch (error) {
            const errorMessage = error.message || 'Failed to start Monte Carlo simulation';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Poll for Monte Carlo results
    async pollMonteCarloResults({ commit }, jobId) {
        commit('setMonteCarloStatus', { jobId, status: 'running' });
        commit('setError', null);

        try {
            const response = await pollMonteCarloJob(
                () => investmentService.getMonteCarloResults(jobId),
                {
                    onProgress: (attempt, res) => {
                        const status = res?.data?.data?.status;
                        if (status) {
                            commit('setMonteCarloStatus', { jobId, status });
                        }
                    }
                }
            );

            const results = response.data.data.results;
            commit('setMonteCarloResults', { jobId, results });
            commit('setMonteCarloStatus', { jobId, status: 'completed' });
            return results;
        } catch (error) {
            const errorMessage = error.message || 'Failed to retrieve Monte Carlo results';
            commit('setMonteCarloStatus', { jobId, status: 'failed' });
            commit('setError', errorMessage);
            throw error;
        }
    },

    // Account actions
    async createAccount({ commit, dispatch }, accountData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.createAccount(accountData);
            commit('addAccount', response.data);
            // Refresh analysis after adding account
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create account';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateAccount({ commit, dispatch }, { id, accountData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.updateAccount(id, accountData);
            commit('updateAccount', response.data);
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update account';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteAccount({ commit, dispatch }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.deleteAccount(id);
            commit('removeAccount', id);
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete account';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Holdings actions
    async createHolding({ commit, dispatch }, holdingData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.createHolding(holdingData);
            commit('addHolding', {
                accountId: holdingData.investment_account_id,
                holding: response.data
            });
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create holding';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateHolding({ commit, dispatch }, { id, holdingData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.updateHolding(id, holdingData);
            commit('updateHolding', response.data);
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update holding';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteHolding({ commit, dispatch }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.deleteHolding(id);
            commit('removeHolding', id);
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete holding';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Goal actions
    async createGoal({ commit }, goalData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.createGoal(goalData);
            commit('addGoal', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to create goal';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async updateGoal({ commit }, { id, goalData }) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.updateGoal(id, goalData);
            commit('updateGoal', response.data);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to update goal';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    async deleteGoal({ commit }, id) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.deleteGoal(id);
            commit('removeGoal', id);
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to delete goal';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },

    // Risk profile action
    async saveRiskProfile({ commit, dispatch }, profileData) {
        commit('setLoading', true);
        commit('setError', null);

        try {
            const response = await investmentService.saveRiskProfile(profileData);
            commit('setRiskProfile', response.data);
            await dispatch('analyzeInvestment');
            return response;
        } catch (error) {
            const errorMessage = error.message || 'Failed to save risk profile';
            commit('setError', errorMessage);
            throw error;
        } finally {
            commit('setLoading', false);
        }
    },
};

const mutations = {
    setAccounts(state, accounts) {
        state.accounts = accounts;
    },

    setGoals(state, goals) {
        state.goals = goals;
    },

    setRiskProfile(state, profile) {
        state.riskProfile = profile;
    },

    setAnalysis(state, analysis) {
        state.analysis = analysis;
    },

    setRecommendations(state, recommendations) {
        state.recommendations = recommendations;
    },

    setScenarios(state, scenarios) {
        state.scenarios = scenarios;
    },

    setMonteCarloResults(state, { jobId, results }) {
        state.monteCarloResults = {
            ...state.monteCarloResults,
            [jobId]: results
        };
    },

    setMonteCarloStatus(state, { jobId, status }) {
        state.monteCarloStatus = {
            ...state.monteCarloStatus,
            [jobId]: status
        };
    },

    addAccount(state, account) {
        state.accounts.push(account);
    },

    updateAccount(state, account) {
        const index = state.accounts.findIndex(a => a.id === account.id);
        if (index !== -1) {
            state.accounts.splice(index, 1, account);
        }
    },

    removeAccount(state, id) {
        const index = state.accounts.findIndex(a => a.id === id);
        if (index !== -1) {
            state.accounts.splice(index, 1);
        }
    },

    addHolding(state, { accountId, holding }) {
        const account = state.accounts.find(a => a.id === accountId);
        if (account) {
            if (!account.holdings) {
                account.holdings = [];
            }
            account.holdings.push(holding);
        }
    },

    updateHolding(state, holding) {
        for (const account of state.accounts) {
            if (account.holdings) {
                const index = account.holdings.findIndex(h => h.id === holding.id);
                if (index !== -1) {
                    account.holdings.splice(index, 1, holding);
                    break;
                }
            }
        }
    },

    removeHolding(state, id) {
        for (const account of state.accounts) {
            if (account.holdings) {
                const index = account.holdings.findIndex(h => h.id === id);
                if (index !== -1) {
                    account.holdings.splice(index, 1);
                    break;
                }
            }
        }
    },

    addGoal(state, goal) {
        state.goals.push(goal);
    },

    updateGoal(state, goal) {
        const index = state.goals.findIndex(g => g.id === goal.id);
        if (index !== -1) {
            state.goals.splice(index, 1, goal);
        }
    },

    removeGoal(state, id) {
        const index = state.goals.findIndex(g => g.id === id);
        if (index !== -1) {
            state.goals.splice(index, 1);
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
