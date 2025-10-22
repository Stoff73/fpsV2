<template>
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <div class="flex-shrink-0 flex items-center">
            <h1 class="font-display text-h4 text-primary-600">FPS</h1>
          </div>
          <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
            <router-link
              to="/dashboard"
              class="inline-flex items-center px-1 pt-1 border-b-2 text-body-sm font-medium"
              :class="isActive('/dashboard') ? 'border-primary-600 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
            >
              Dashboard
            </router-link>
          </div>
        </div>

        <div class="hidden sm:ml-6 sm:flex sm:items-center space-x-4">
          <router-link
            v-if="!onboardingCompleted"
            to="/onboarding"
            class="inline-flex items-center px-3 py-2 border border-transparent text-body-sm font-medium rounded-button text-white bg-primary-600 hover:bg-primary-700"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            Complete Setup
          </router-link>
          <router-link
            v-if="isAdmin"
            to="/admin"
            class="inline-flex items-center px-3 py-2 border border-transparent text-body-sm font-medium rounded-button text-white bg-red-600 hover:bg-red-700"
          >
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Admin
          </router-link>
          <router-link
            to="/profile"
            class="inline-flex items-center px-3 py-2 border border-transparent text-body-sm font-medium rounded-button text-gray-700 bg-gray-100 hover:bg-gray-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            {{ userName }}
          </router-link>
        </div>

        <div class="flex items-center sm:hidden">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="inline-flex items-center justify-center p-2 rounded-button text-gray-400 hover:text-gray-500 hover:bg-gray-100"
          >
            <span class="sr-only">Open main menu</span>
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                v-if="!mobileMenuOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <div v-if="mobileMenuOpen" class="sm:hidden">
      <div class="pt-2 pb-3 space-y-1">
        <router-link
          to="/dashboard"
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          :class="isActive('/dashboard') ? 'bg-primary-50 border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
        >
          Dashboard
        </router-link>
        <router-link
          v-if="isAdmin"
          to="/admin"
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          :class="isActive('/admin') ? 'bg-red-50 border-red-600 text-red-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
        >
          Admin Panel
        </router-link>
        <router-link
          to="/settings"
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium"
          :class="isActive('/settings') ? 'bg-primary-50 border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700'"
        >
          Settings
        </router-link>
      </div>
      <div class="pt-4 pb-3 border-t border-gray-200">
        <div class="flex items-center px-4">
          <div class="flex-shrink-0">
            <span class="text-body text-gray-900">{{ userName }}</span>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { useRoute } from 'vue-router';

export default {
  name: 'Navbar',

  setup() {
    const store = useStore();
    const route = useRoute();

    const mobileMenuOpen = ref(false);

    const userName = computed(() => {
      const user = store.getters['auth/currentUser'];
      return user?.name || 'User';
    });

    const isAdmin = computed(() => {
      return store.getters['auth/isAdmin'];
    });

    const onboardingCompleted = computed(() => {
      const user = store.getters['auth/currentUser'];
      return user?.onboarding_completed || false;
    });

    const isActive = (path) => {
      return route.path === path;
    };

    return {
      mobileMenuOpen,
      userName,
      isAdmin,
      onboardingCompleted,
      isActive,
    };
  },
};
</script>
