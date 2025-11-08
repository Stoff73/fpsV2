<template>
  <div v-if="show" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

      <!-- Center modal -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <!-- Icon -->
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full" :class="isCreated ? 'bg-green-100' : 'bg-blue-100'">
            <svg v-if="isCreated" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <svg v-else class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </div>

          <!-- Title -->
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              {{ title }}
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                {{ message }}
              </p>
            </div>
          </div>

          <!-- Account Details (for created accounts) -->
          <div v-if="isCreated && spouseEmail && temporaryPassword" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-yellow-800">
                  Share these credentials with your spouse
                </h3>
                <div class="mt-2 text-sm text-yellow-700 space-y-2">
                  <div class="bg-white rounded px-3 py-2 font-mono text-xs border border-yellow-300">
                    <div class="font-semibold text-gray-700 mb-1">Email</div>
                    <div class="select-all">{{ spouseEmail }}</div>
                  </div>
                  <div class="bg-white rounded px-3 py-2 font-mono text-xs border border-yellow-300">
                    <div class="font-semibold text-gray-700 mb-1">Temporary Password</div>
                    <div class="select-all break-all">{{ temporaryPassword }}</div>
                  </div>
                  <p class="text-xs">
                    Your spouse will be required to change this password when they first log in.
                    They will also receive an email with login instructions.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Linked Account Info -->
          <div v-if="!isCreated" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3 flex-1">
                <p class="text-sm text-blue-700">
                  Your accounts are now linked. You can both view and manage shared family information.
                  An email notification has been sent to your spouse.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-5 sm:mt-6">
          <button
            type="button"
            class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:text-sm"
            @click="$emit('close')"
          >
            {{ isCreated ? 'Got it!' : 'Close' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SpouseSuccessModal',

  props: {
    show: {
      type: Boolean,
      required: true,
    },
    isCreated: {
      type: Boolean,
      default: false,
    },
    spouseEmail: {
      type: String,
      default: null,
    },
    temporaryPassword: {
      type: String,
      default: null,
    },
  },

  emits: ['close'],

  computed: {
    title() {
      return this.isCreated
        ? 'Spouse Account Created'
        : 'Spouse Account Linked';
    },
    message() {
      return this.isCreated
        ? 'A new account has been created for your spouse. Please share the login credentials below with them.'
        : 'Your spouse\'s existing account has been successfully linked to yours.';
    },
  },
};
</script>
