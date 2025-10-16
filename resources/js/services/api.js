import axios from 'axios';

// Create axios instance with default config
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response) {
      // Handle 401 Unauthorized errors
      if (error.response.status === 401) {
        // Don't redirect if we're already on login/register endpoints (let component handle it)
        const isAuthEndpoint = error.config?.url?.includes('/auth/login') ||
                               error.config?.url?.includes('/auth/register');

        if (!isAuthEndpoint) {
          // Clear token and redirect to login for protected routes
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          // Get the base path from the current location to handle subfolder deployments
          const basePath = window.location.pathname.includes('/fps/') ? '/fps' : '';
          window.location.href = `${basePath}/login`;
        } else {
          // For auth endpoints, return the error to be handled by the component
          return Promise.reject({
            message: error.response.data.message || 'Invalid credentials',
            errors: error.response.data.errors || null,
          });
        }
      }

      // Handle 422 Validation errors
      if (error.response.status === 422) {
        return Promise.reject({
          message: error.response.data.message || 'Validation failed',
          errors: error.response.data.errors || null,
          status: error.response.status,
          response: error.response,
        });
      }

      // Handle other errors
      return Promise.reject({
        message: error.response.data.message || 'An error occurred',
        errors: error.response.data.errors || null,
        status: error.response.status,
        response: error.response,
      });
    }

    // Network errors or other issues
    return Promise.reject({
      message: 'Network error. Please check your connection.',
      errors: null,
      status: null,
      response: null,
    });
  }
);

export default api;
