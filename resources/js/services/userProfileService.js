import api from './api';

const userProfileService = {
  /**
   * Get the authenticated user's complete profile
   * @returns {Promise}
   */
  async getProfile() {
    const response = await api.get('/user/profile');
    return response.data;
  },

  /**
   * Update personal information
   * @param {Object} data - Personal information data
   * @returns {Promise}
   */
  async updatePersonalInfo(data) {
    const response = await api.put('/user/profile/personal', data);
    return response.data;
  },

  /**
   * Update income and occupation information
   * @param {Object} data - Income and occupation data
   * @returns {Promise}
   */
  async updateIncomeOccupation(data) {
    const response = await api.put('/user/profile/income-occupation', data);
    return response.data;
  },

  /**
   * Get all family members
   * @returns {Promise}
   */
  async getFamilyMembers() {
    const response = await api.get('/user/family-members');
    return response.data;
  },

  /**
   * Create a new family member
   * @param {Object} data - Family member data
   * @returns {Promise}
   */
  async createFamilyMember(data) {
    const response = await api.post('/user/family-members', data);
    return response.data;
  },

  /**
   * Get a single family member by ID
   * @param {number} id - Family member ID
   * @returns {Promise}
   */
  async getFamilyMember(id) {
    const response = await api.get(`/user/family-members/${id}`);
    return response.data;
  },

  /**
   * Update a family member
   * @param {number} id - Family member ID
   * @param {Object} data - Updated family member data
   * @returns {Promise}
   */
  async updateFamilyMember(id, data) {
    const response = await api.put(`/user/family-members/${id}`, data);
    return response.data;
  },

  /**
   * Delete a family member
   * @param {number} id - Family member ID
   * @returns {Promise}
   */
  async deleteFamilyMember(id) {
    const response = await api.delete(`/user/family-members/${id}`);
    return response.data;
  },

  /**
   * Get personal accounts (saved line items)
   * @returns {Promise}
   */
  async getPersonalAccounts() {
    const response = await api.get('/user/personal-accounts');
    return response.data;
  },

  /**
   * Calculate personal accounts (P&L, Cashflow, Balance Sheet)
   * @param {Object} params - Query parameters (start_date, end_date, as_of_date)
   * @returns {Promise}
   */
  async calculatePersonalAccounts(params = {}) {
    const response = await api.post('/user/personal-accounts/calculate', params);
    return response.data;
  },

  /**
   * Create a manual line item
   * @param {Object} data - Line item data
   * @returns {Promise}
   */
  async createLineItem(data) {
    const response = await api.post('/user/personal-accounts/line-item', data);
    return response.data;
  },

  /**
   * Update a line item
   * @param {number} id - Line item ID
   * @param {Object} data - Updated line item data
   * @returns {Promise}
   */
  async updateLineItem(id, data) {
    const response = await api.put(`/user/personal-accounts/line-item/${id}`, data);
    return response.data;
  },

  /**
   * Delete a line item
   * @param {number} id - Line item ID
   * @returns {Promise}
   */
  async deleteLineItem(id) {
    const response = await api.delete(`/user/personal-accounts/line-item/${id}`);
    return response.data;
  },
};

export default userProfileService;
