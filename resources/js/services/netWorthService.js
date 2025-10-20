import api from './api';

const API_BASE = '/net-worth';

export default {
    /**
     * Get net worth overview
     */
    async getOverview() {
        const response = await api.get(`${API_BASE}/overview`);
        return response.data;
    },

    /**
     * Get asset breakdown with percentages
     */
    async getBreakdown() {
        const response = await api.get(`${API_BASE}/breakdown`);
        return response.data;
    },

    /**
     * Get net worth trend
     * @param {number} months - Number of months to retrieve (default: 12)
     */
    async getTrend(months = 12) {
        const response = await api.get(`${API_BASE}/trend`, {
            params: { months }
        });
        return response.data;
    },

    /**
     * Get assets summary
     */
    async getAssetsSummary() {
        const response = await api.get(`${API_BASE}/assets-summary`);
        return response.data;
    },

    /**
     * Get joint assets
     */
    async getJointAssets() {
        const response = await api.get(`${API_BASE}/joint-assets`);
        return response.data;
    },

    /**
     * Refresh net worth (bypass cache)
     */
    async refresh() {
        const response = await api.post(`${API_BASE}/refresh`);
        return response.data;
    }
};
