<template>
  <div class="gifting-strategy-tab">
    <!-- Gifting Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-blue-50 rounded-lg p-6">
        <p class="text-sm text-blue-600 font-medium mb-2">Gifts Within 7 Years</p>
        <p class="text-3xl font-bold text-gray-900">{{ giftsWithin7YearsCount }}</p>
      </div>
      <div class="bg-purple-50 rounded-lg p-6">
        <p class="text-sm text-purple-600 font-medium mb-2">Total Value</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedGiftsValue }}</p>
      </div>
      <div class="bg-green-50 rounded-lg p-6">
        <p class="text-sm text-green-600 font-medium mb-2">Annual Exemption Available</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedAnnualExemption }}</p>
      </div>
    </div>

    <!-- Add Gift Button -->
    <div class="mb-6">
      <button
        @click="showGiftForm = true"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        <svg
          class="-ml-1 mr-2 h-5 w-5"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd"
          />
        </svg>
        Record New Gift
      </button>
    </div>

    <!-- Gifts List -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Gifts Made</h3>
      </div>
      <div v-if="gifts.length === 0" class="px-6 py-8 text-center text-gray-500">
        No gifts recorded yet
      </div>
      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="gift in sortedGifts"
          :key="gift.id"
          class="px-6 py-4 hover:bg-gray-50"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center">
                <p class="text-sm font-medium text-gray-900">{{ gift.recipient }}</p>
                <span
                  :class="[
                    'ml-3 px-2 py-1 text-xs font-medium rounded-full',
                    getGiftStatusColor(gift),
                  ]"
                >
                  {{ getGiftStatus(gift) }}
                </span>
              </div>
              <div class="mt-1 flex items-center text-sm text-gray-500">
                <span>{{ formatDate(gift.gift_date) }}</span>
                <span class="mx-2">•</span>
                <span>{{ formatCurrency(gift.gift_value) }}</span>
                <span class="mx-2">•</span>
                <span>{{ gift.gift_type }}</span>
              </div>
            </div>
            <div class="ml-4 flex-shrink-0">
              <button
                @click="editGift(gift)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="deleteGift(gift.id)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gift Form Modal (placeholder) -->
    <div v-if="showGiftForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Record New Gift</h3>
        <p class="text-sm text-gray-600 mb-4">Gift form will be implemented here</p>
        <button
          @click="showGiftForm = false"
          class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';

export default {
  name: 'GiftingStrategy',

  data() {
    return {
      showGiftForm: false,
      annualExemption: 3000,
    };
  },

  computed: {
    ...mapState('estate', ['gifts']),
    ...mapGetters('estate', ['giftsWithin7Years', 'giftsWithin7YearsValue']),

    giftsWithin7YearsCount() {
      return this.giftsWithin7Years.length;
    },

    formattedGiftsValue() {
      return this.formatCurrency(this.giftsWithin7YearsValue);
    },

    formattedAnnualExemption() {
      return this.formatCurrency(this.annualExemption);
    },

    sortedGifts() {
      return [...this.gifts].sort((a, b) => new Date(b.gift_date) - new Date(a.gift_date));
    },
  },

  methods: {
    ...mapActions('estate', ['deleteGift']),

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatDate(date) {
      return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      });
    },

    getGiftStatus(gift) {
      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      const now = new Date();

      if (now >= sevenYearsLater) {
        return 'Survived';
      }

      const yearsRemaining = Math.ceil((sevenYearsLater - now) / (365 * 24 * 60 * 60 * 1000));
      return `${yearsRemaining} years remaining`;
    },

    getGiftStatusColor(gift) {
      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      const now = new Date();

      if (now >= sevenYearsLater) {
        return 'bg-green-100 text-green-800';
      } else {
        return 'bg-amber-100 text-amber-800';
      }
    },

    editGift(gift) {
      // Placeholder for edit functionality
      console.log('Edit gift:', gift);
    },

    async deleteGift(id) {
      if (confirm('Are you sure you want to delete this gift record?')) {
        try {
          await this.deleteGift(id);
        } catch (error) {
          console.error('Failed to delete gift:', error);
        }
      }
    },
  },
};
</script>
