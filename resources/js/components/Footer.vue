<template>
  <footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <div class="text-body-sm text-gray-500">
          &copy; {{ currentYear }} TenGo - Financial Planning System. For demonstration purposes only.
          <span class="ml-2 text-gray-400">|</span>
          <router-link to="/version" class="ml-2 font-semibold text-primary-600 hover:text-primary-700 hover:underline">
            v0.2.9
          </router-link>
        </div>
        <div class="flex space-x-6">
          <a href="#" class="text-body-sm text-gray-500 hover:text-gray-700">
            Privacy Policy
          </a>
          <a href="#" class="text-body-sm text-gray-500 hover:text-gray-700">
            Terms of Service
          </a>
          <router-link to="/help" class="text-body-sm text-gray-500 hover:text-gray-700">
            Help
          </router-link>
        </div>
      </div>
      <div class="mt-4 text-center">
        <p class="text-caption text-gray-400">
          This system is for demonstration purposes only and does not constitute regulated financial advice.
        </p>
      </div>
    </div>
  </footer>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'Footer',

  setup() {
    const currentYear = computed(() => new Date().getFullYear());

    return {
      currentYear,
    };
  },
};
</script>
