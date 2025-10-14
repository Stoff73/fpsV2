import api from './api';

const authService = {
  /**
   * Register a new user
   * @param {Object} userData - User registration data
   * @returns {Promise}
   */
  async register(userData) {
    const response = await api.post('/auth/register', userData);
    if (response.data.success && response.data.data.access_token) {
      this.setToken(response.data.data.access_token);
      this.setUser(response.data.data.user);
    }
    return response.data;
  },

  /**
   * Login user
   * @param {Object} credentials - User credentials (email, password)
   * @returns {Promise}
   */
  async login(credentials) {
    const response = await api.post('/auth/login', credentials);
    if (response.data.success && response.data.data.access_token) {
      this.setToken(response.data.data.access_token);
      this.setUser(response.data.data.user);
    }
    return response.data;
  },

  /**
   * Logout user
   * @returns {Promise}
   */
  async logout() {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout API error:', error);
    } finally {
      this.clearAuth();
    }
  },

  /**
   * Get current authenticated user
   * @returns {Promise}
   */
  async getUser() {
    const response = await api.get('/auth/user');
    if (response.data.success) {
      this.setUser(response.data.data.user);
      return response.data.data.user;
    }
  },

  /**
   * Set authentication token in localStorage
   * @param {string} token
   */
  setToken(token) {
    localStorage.setItem('auth_token', token);
  },

  /**
   * Get authentication token from localStorage
   * @returns {string|null}
   */
  getToken() {
    return localStorage.getItem('auth_token');
  },

  /**
   * Set user data in localStorage
   * @param {Object} user
   */
  setUser(user) {
    localStorage.setItem('user', JSON.stringify(user));
  },

  /**
   * Get user data from localStorage
   * @returns {Object|null}
   */
  getStoredUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },

  /**
   * Clear authentication data
   */
  clearAuth() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  },

  /**
   * Check if user is authenticated
   * @returns {boolean}
   */
  isAuthenticated() {
    return !!this.getToken();
  },
};

export default authService;
