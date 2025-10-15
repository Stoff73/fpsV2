import api from './api';

/**
 * Estate Planning Module API Service
 * Handles all API calls related to estate planning, IHT, assets, liabilities, and gifts
 */
const estateService = {
    /**
     * Get all estate data for the authenticated user
     * @returns {Promise} Estate data including assets, liabilities, gifts, and IHT profile
     */
    async getEstateData() {
        const response = await api.get('/estate');
        return response.data;
    },

    /**
     * Analyze estate position and calculate IHT liability
     * @param {Object} data - Analysis parameters
     * @returns {Promise} Analysis results with IHT calculation and recommendations
     */
    async analyzeEstate(data = {}) {
        const response = await api.post('/estate/analyze', data);
        return response.data;
    },

    /**
     * Get estate planning recommendations
     * @returns {Promise} Prioritized recommendations
     */
    async getRecommendations() {
        const response = await api.get('/estate/recommendations');
        return response.data;
    },

    /**
     * Run a what-if scenario
     * @param {Object} scenarioData - Scenario parameters
     * @returns {Promise} Scenario analysis results
     */
    async runScenario(scenarioData) {
        const response = await api.post('/estate/scenarios', scenarioData);
        return response.data;
    },

    /**
     * Calculate IHT liability
     * @param {Object} data - IHT calculation parameters
     * @returns {Promise} IHT calculation breakdown
     */
    async calculateIHT(data) {
        const response = await api.post('/estate/calculate-iht', data);
        return response.data;
    },

    /**
     * Get net worth summary
     * @returns {Promise} Net worth breakdown
     */
    async getNetWorth() {
        const response = await api.get('/estate/net-worth');
        return response.data;
    },

    /**
     * Get cash flow for a specific tax year
     * @param {String} taxYear - Tax year (e.g., '2024/25')
     * @returns {Promise} Cash flow statement
     */
    async getCashFlow(taxYear) {
        const response = await api.get('/estate/cash-flow', {
            params: { taxYear }
        });
        return response.data;
    },

    // IHT Profile Methods
    /**
     * Create or update IHT profile
     * @param {Object} profileData - IHT profile data
     * @returns {Promise} Created/updated profile
     */
    async storeOrUpdateProfile(profileData) {
        const response = await api.post('/estate/profile', profileData);
        return response.data;
    },

    // Asset Methods
    /**
     * Create a new asset
     * @param {Object} assetData - Asset data
     * @returns {Promise} Created asset
     */
    async createAsset(assetData) {
        const response = await api.post('/estate/assets', assetData);
        return response.data;
    },

    /**
     * Update an asset
     * @param {Number} id - Asset ID
     * @param {Object} assetData - Updated asset data
     * @returns {Promise} Updated asset
     */
    async updateAsset(id, assetData) {
        const response = await api.put(`/estate/assets/${id}`, assetData);
        return response.data;
    },

    /**
     * Delete an asset
     * @param {Number} id - Asset ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteAsset(id) {
        const response = await api.delete(`/estate/assets/${id}`);
        return response.data;
    },

    // Liability Methods
    /**
     * Create a new liability
     * @param {Object} liabilityData - Liability data
     * @returns {Promise} Created liability
     */
    async createLiability(liabilityData) {
        const response = await api.post('/estate/liabilities', liabilityData);
        return response.data;
    },

    /**
     * Update a liability
     * @param {Number} id - Liability ID
     * @param {Object} liabilityData - Updated liability data
     * @returns {Promise} Updated liability
     */
    async updateLiability(id, liabilityData) {
        const response = await api.put(`/estate/liabilities/${id}`, liabilityData);
        return response.data;
    },

    /**
     * Delete a liability
     * @param {Number} id - Liability ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteLiability(id) {
        const response = await api.delete(`/estate/liabilities/${id}`);
        return response.data;
    },

    // Gift Methods
    /**
     * Create a new gift record
     * @param {Object} giftData - Gift data
     * @returns {Promise} Created gift
     */
    async createGift(giftData) {
        const response = await api.post('/estate/gifts', giftData);
        return response.data;
    },

    /**
     * Update a gift record
     * @param {Number} id - Gift ID
     * @param {Object} giftData - Updated gift data
     * @returns {Promise} Updated gift
     */
    async updateGift(id, giftData) {
        const response = await api.put(`/estate/gifts/${id}`, giftData);
        return response.data;
    },

    /**
     * Delete a gift record
     * @param {Number} id - Gift ID
     * @returns {Promise} Deletion confirmation
     */
    async deleteGift(id) {
        const response = await api.delete(`/estate/gifts/${id}`);
        return response.data;
    },
};

export default estateService;
