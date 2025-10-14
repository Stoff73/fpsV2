import api from './api';

/**
 * Savings Module API Service
 * Handles all API calls related to savings accounts, goals, and analysis
 */
const savingsService = {
    /**
     * Get all savings data for the authenticated user
     * @returns {Promise} Savings data including accounts, goals, and analysis
     */
    async getSavingsData() {
        const response = await api.get('/savings');
        return response.data;
    },

    /**
     * Analyze savings and emergency fund adequacy
     * @param {Object} data - Analysis parameters
     * @returns {Promise} Analysis results with recommendations
     */
    async analyzeSavings(data) {
        const response = await api.post('/savings/analyze', data);
        return response.data;
    },

    /**
     * Get savings recommendations
     * @returns {Promise} Prioritized recommendations
     */
    async getRecommendations() {
        const response = await api.get('/savings/recommendations');
        return response.data;
    },

    /**
     * Run a what-if scenario
     * @param {Object} scenarioData - Scenario parameters
     * @returns {Promise} Scenario analysis results
     */
    async runScenario(scenarioData) {
        const response = await api.post('/savings/scenarios', scenarioData);
        return response.data;
    },

    /**
     * Get ISA allowance information for a tax year
     * @param {String} taxYear - Tax year (e.g., '2024-25')
     * @returns {Promise} ISA allowance data
     */
    async getISAAllowance(taxYear) {
        const response = await api.get(`/savings/isa-allowance/${taxYear}`);
        return response.data;
    },

    // Savings Account Methods
    /**
     * Create a new savings account
     * @param {Object} accountData - Account data
     * @returns {Promise} Created account
     */
    async createAccount(accountData) {
        const response = await api.post('/savings/accounts', accountData);
        return response.data;
    },

    /**
     * Update a savings account
     * @param {Number} id - Account ID
     * @param {Object} accountData - Updated account data
     * @returns {Promise} Updated account
     */
    async updateAccount(id, accountData) {
        const response = await api.put(`/savings/accounts/${id}`, accountData);
        return response.data;
    },

    /**
     * Delete a savings account
     * @param {Number} id - Account ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteAccount(id) {
        const response = await api.delete(`/savings/accounts/${id}`);
        return response.data;
    },

    // Savings Goals Methods
    /**
     * Get all savings goals
     * @returns {Promise} Array of goals
     */
    async getGoals() {
        const response = await api.get('/savings/goals');
        return response.data;
    },

    /**
     * Create a new savings goal
     * @param {Object} goalData - Goal data
     * @returns {Promise} Created goal
     */
    async createGoal(goalData) {
        const response = await api.post('/savings/goals', goalData);
        return response.data;
    },

    /**
     * Update a savings goal
     * @param {Number} id - Goal ID
     * @param {Object} goalData - Updated goal data
     * @returns {Promise} Updated goal
     */
    async updateGoal(id, goalData) {
        const response = await api.put(`/savings/goals/${id}`, goalData);
        return response.data;
    },

    /**
     * Delete a savings goal
     * @param {Number} id - Goal ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteGoal(id) {
        const response = await api.delete(`/savings/goals/${id}`);
        return response.data;
    },

    /**
     * Update progress on a savings goal
     * @param {Number} id - Goal ID
     * @param {Number} amount - Amount to add to current_saved
     * @returns {Promise} Updated goal
     */
    async updateGoalProgress(id, amount) {
        const response = await api.patch(`/savings/goals/${id}/progress`, { amount });
        return response.data;
    },

    // Expenditure Profile Methods
    /**
     * Get expenditure profile
     * @returns {Promise} Expenditure profile data
     */
    async getExpenditureProfile() {
        const response = await api.get('/savings/expenditure-profile');
        return response.data;
    },

    /**
     * Update expenditure profile
     * @param {Object} profileData - Expenditure profile data
     * @returns {Promise} Updated profile
     */
    async updateExpenditureProfile(profileData) {
        const response = await api.put('/savings/expenditure-profile', profileData);
        return response.data;
    },
};

export default savingsService;
