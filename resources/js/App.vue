<template>
  <router-view />
</template>

<script>
import { onMounted } from 'vue';
import { useStore } from 'vuex';

export default {
  name: 'App',

  setup() {
    const store = useStore();

    onMounted(async () => {
      // On app initialization, fetch user data fresh from API if token exists
      if (store.getters['auth/isAuthenticated']) {
        try {
          await store.dispatch('auth/fetchUser');
        } catch (error) {
          // Token is invalid, clear it
          store.commit('auth/clearAuth');
          localStorage.removeItem('auth_token');
        }
      }
    });

    return {};
  },
};
</script>
