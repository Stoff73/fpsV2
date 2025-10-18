/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Sanctum SPA Authentication Configuration
 * This ensures cookies (including the session cookie) are sent with every request
 */
window.axios.defaults.withCredentials = true;

// Use environment-specific base URL (production or local development)
// In production with subfolder deployment, VITE_API_BASE_URL should be set to the full path (e.g., https://csjones.co/fps)
// In local development, it defaults to window.location.origin
const apiBaseURL = import.meta.env.VITE_API_BASE_URL || window.location.origin;
window.axios.defaults.baseURL = apiBaseURL;

// Debug: Log the API base URL being used
console.log('[AXIOS CONFIG] API Base URL:', apiBaseURL);
console.log('[AXIOS CONFIG] VITE_API_BASE_URL from env:', import.meta.env.VITE_API_BASE_URL);

// Add CSRF token from meta tag to all requests
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
} else {
    console.warn('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Export configured axios instance for use in service modules
export default window.axios;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
