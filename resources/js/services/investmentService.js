import api from './api';

/**
 * Investment Module API Service
 * Handles all API calls related to investment accounts, holdings, portfolio analysis, and Monte Carlo simulations
 */
const investmentService = {
    /**
     * Get all investment data for the authenticated user
     * @returns {Promise} Investment data including accounts, holdings, goals, and risk profile
     */
    async getInvestmentData() {
        const response = await api.get('/investment');
        return response.data;
    },

    /**
     * Run comprehensive portfolio analysis
     * @returns {Promise} Analysis results with recommendations
     */
    async analyzeInvestment() {
        const response = await api.post('/investment/analyze');
        return response.data;
    },

    /**
     * Get investment recommendations
     * @returns {Promise} Prioritized recommendations
     */
    async getRecommendations() {
        const response = await api.get('/investment/recommendations');
        return response.data;
    },

    /**
     * Build what-if scenarios
     * @param {Object} scenarioData - Scenario parameters (monthly_contribution, etc.)
     * @returns {Promise} Scenario analysis results
     */
    async runScenario(scenarioData) {
        const response = await api.post('/investment/scenarios', scenarioData);
        return response.data;
    },

    /**
     * Start Monte Carlo simulation
     * @param {Object} params - Simulation parameters
     * @param {Number} params.start_value - Starting portfolio value
     * @param {Number} params.monthly_contribution - Monthly contribution amount
     * @param {Number} params.expected_return - Expected annual return (0-0.5)
     * @param {Number} params.volatility - Volatility (0-1)
     * @param {Number} params.years - Number of years to project
     * @param {Number} params.iterations - Number of simulations (100-10000)
     * @param {Number} params.goal_amount - Optional goal amount for probability calculation
     * @returns {Promise} Job ID for polling
     */
    async startMonteCarlo(params) {
        const response = await api.post('/investment/monte-carlo', params);
        return response.data;
    },

    /**
     * Get Monte Carlo simulation results
     * @param {String} jobId - Job ID from startMonteCarlo
     * @returns {Promise} Simulation results or status
     */
    async getMonteCarloResults(jobId) {
        const response = await api.get(`/investment/monte-carlo/${jobId}`);
        return response.data;
    },

    // Investment Account Methods
    /**
     * Create a new investment account
     * @param {Object} accountData - Account data
     * @param {String} accountData.account_type - Account type (isa, gia, etc.)
     * @param {String} accountData.provider - Provider name
     * @param {String} accountData.platform - Platform name
     * @param {Number} accountData.current_value - Current account value
     * @param {Number} accountData.contributions_ytd - Contributions year to date
     * @param {String} accountData.tax_year - Tax year
     * @param {Number} accountData.platform_fee_percent - Platform fee percentage
     * @returns {Promise} Created account
     */
    async createAccount(accountData) {
        try {
            const response = await api.post('/investment/accounts', accountData);
            return response.data;
        } catch (error) {
            console.error('Account creation failed:', error.response?.data);
            throw error;
        }
    },

    /**
     * Update an investment account
     * @param {Number} id - Account ID
     * @param {Object} accountData - Updated account data
     * @returns {Promise} Updated account
     */
    async updateAccount(id, accountData) {
        const response = await api.put(`/investment/accounts/${id}`, accountData);
        return response.data;
    },

    /**
     * Delete an investment account
     * @param {Number} id - Account ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteAccount(id) {
        const response = await api.delete(`/investment/accounts/${id}`);
        return response.data;
    },

    // Holdings Methods
    /**
     * Create a new holding
     * @param {Object} holdingData - Holding data
     * @param {Number} holdingData.investment_account_id - Account ID
     * @param {String} holdingData.asset_type - Asset type (equity, bond, etc.)
     * @param {String} holdingData.security_name - Security name
     * @param {String} holdingData.ticker - Ticker symbol
     * @param {Number} holdingData.quantity - Quantity held
     * @param {Number} holdingData.purchase_price - Purchase price per unit
     * @param {String} holdingData.purchase_date - Purchase date
     * @param {Number} holdingData.current_price - Current price per unit
     * @param {Number} holdingData.current_value - Current total value
     * @param {Number} holdingData.cost_basis - Cost basis
     * @param {Number} holdingData.ocf_percent - Ongoing charges figure percentage
     * @returns {Promise} Created holding
     */
    async createHolding(holdingData) {
        try {
            const response = await api.post('/investment/holdings', holdingData);
            return response.data;
        } catch (error) {
            console.error('Holding creation failed:', error.response?.data);
            throw error;
        }
    },

    /**
     * Update a holding
     * @param {Number} id - Holding ID
     * @param {Object} holdingData - Updated holding data
     * @returns {Promise} Updated holding
     */
    async updateHolding(id, holdingData) {
        const response = await api.put(`/investment/holdings/${id}`, holdingData);
        return response.data;
    },

    /**
     * Delete a holding
     * @param {Number} id - Holding ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteHolding(id) {
        const response = await api.delete(`/investment/holdings/${id}`);
        return response.data;
    },

    // Investment Goals Methods
    /**
     * Create a new investment goal
     * @param {Object} goalData - Goal data
     * @param {String} goalData.goal_name - Goal name
     * @param {String} goalData.goal_type - Goal type (retirement, education, wealth, home)
     * @param {Number} goalData.target_amount - Target amount
     * @param {String} goalData.target_date - Target date
     * @param {String} goalData.priority - Priority (high, medium, low)
     * @param {Boolean} goalData.is_essential - Is essential goal
     * @param {Array} goalData.linked_account_ids - Linked account IDs
     * @returns {Promise} Created goal
     */
    async createGoal(goalData) {
        const response = await api.post('/investment/goals', goalData);
        return response.data;
    },

    /**
     * Update an investment goal
     * @param {Number} id - Goal ID
     * @param {Object} goalData - Updated goal data
     * @returns {Promise} Updated goal
     */
    async updateGoal(id, goalData) {
        const response = await api.put(`/investment/goals/${id}`, goalData);
        return response.data;
    },

    /**
     * Delete an investment goal
     * @param {Number} id - Goal ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteGoal(id) {
        const response = await api.delete(`/investment/goals/${id}`);
        return response.data;
    },

    // Risk Profile Methods
    /**
     * Create or update risk profile
     * @param {Object} profileData - Risk profile data
     * @param {String} profileData.risk_tolerance - Risk tolerance (cautious, balanced, adventurous)
     * @param {Number} profileData.capacity_for_loss_percent - Capacity for loss percentage
     * @param {Number} profileData.time_horizon_years - Time horizon in years
     * @param {String} profileData.knowledge_level - Knowledge level (novice, intermediate, experienced)
     * @param {String} profileData.attitude_to_volatility - Attitude to volatility
     * @param {Boolean} profileData.esg_preference - ESG preference
     * @returns {Promise} Risk profile
     */
    async saveRiskProfile(profileData) {
        const response = await api.post('/investment/risk-profile', profileData);
        return response.data;
    },
};

export default investmentService;
