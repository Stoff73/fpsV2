import api from './api';

/**
 * Plans Service
 * Handles API calls for comprehensive cross-module plans
 */
const plansService = {
  /**
   * Generate comprehensive Investment & Savings Plan
   * @returns {Promise} API response with plan data
   */
  async generateInvestmentSavingsPlan() {
    const response = await api.get('/plans/investment-savings');
    return response.data;
  },

  /**
   * Clear Investment & Savings Plan cache
   * @returns {Promise} API response
   */
  async clearInvestmentSavingsPlanCache() {
    const response = await api.delete('/plans/investment-savings/clear-cache');
    return response.data;
  },
};

export default plansService;
