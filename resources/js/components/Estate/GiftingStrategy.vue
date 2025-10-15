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

    <!-- Success/Error Messages -->
    <div v-if="successMessage" class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
      {{ errorMessage }}
    </div>

    <!-- HMRC 7-Year Rule & Taper Relief Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-semibold text-blue-900">HMRC 7-Year Rule & Taper Relief</h3>
          <div class="mt-2 text-sm text-blue-800">
            <p class="mb-2">
              Potentially Exempt Transfers (PETs) become completely exempt from Inheritance Tax if you survive for 7 years after making the gift.
            </p>
            <p class="font-medium mb-1">If death occurs within 7 years, taper relief applies:</p>
            <ul class="list-disc list-inside space-y-1 ml-2">
              <li><span class="font-semibold">Years 0-3:</span> 40% IHT rate (no relief)</li>
              <li><span class="font-semibold">Years 3-4:</span> 32% IHT rate (20% taper relief)</li>
              <li><span class="font-semibold">Years 4-5:</span> 24% IHT rate (40% taper relief)</li>
              <li><span class="font-semibold">Years 5-6:</span> 16% IHT rate (60% taper relief)</li>
              <li><span class="font-semibold">Years 6-7:</span> 8% IHT rate (80% taper relief)</li>
              <li><span class="font-semibold">After 7 years:</span> 0% IHT rate (100% relief - fully exempt)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Gift Button -->
    <div class="mb-6">
      <button
        @click="openCreateGiftForm"
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
                <span>{{ formatGiftType(gift.gift_type) }}</span>
              </div>

              <!-- Taper Relief Timeline (only for PETs within 7 years) -->
              <div v-if="shouldShowTaperRelief(gift)" class="mt-3">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                  <span>Taper Relief Timeline</span>
                  <span class="font-medium">{{ getTaperReliefPercentage(gift) }}% IHT if death occurs now</span>
                </div>
                <div class="relative h-8 bg-gray-100 rounded-lg overflow-hidden">
                  <!-- Progress bar showing years elapsed -->
                  <div
                    class="absolute inset-y-0 left-0 transition-all duration-300"
                    :style="{ width: getTimelineProgress(gift) + '%', backgroundColor: getTimelineColor(gift) }"
                  ></div>

                  <!-- Taper relief markers -->
                  <div class="absolute inset-0 flex">
                    <div v-for="year in 7" :key="year" class="flex-1 border-r border-gray-300 last:border-r-0 flex items-center justify-center">
                      <span class="text-xs font-medium" :class="getYearLabelClass(gift, year)">
                        {{ getTaperReliefAtYear(year) }}%
                      </span>
                    </div>
                  </div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>Gift date: {{ formatDate(gift.gift_date) }}</span>
                  <span>IHT-free: {{ formatDate(getSevenYearDate(gift)) }}</span>
                </div>
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
                @click="handleDeleteGift(gift.id)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Gift Form Modal -->
    <div v-if="showGiftForm" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <GiftForm
          :gift="currentGift"
          :mode="formMode"
          @save="handleSaveGift"
          @cancel="closeGiftForm"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import GiftForm from './GiftForm.vue';

export default {
  name: 'GiftingStrategy',

  components: {
    GiftForm,
  },

  data() {
    return {
      showGiftForm: false,
      currentGift: null,
      formMode: 'create',
      annualExemption: 3000,
      successMessage: '',
      errorMessage: '',
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
    ...mapActions('estate', ['createGift', 'updateGift', 'deleteGift']),

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

    formatGiftType(type) {
      const types = {
        pet: 'PET',
        clt: 'CLT',
        exempt: 'Exempt',
        small_gift: 'Small Gift',
        annual_exemption: 'Annual Exemption',
      };
      return types[type] || type;
    },

    getGiftStatus(gift) {
      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      const now = new Date();

      if (now >= sevenYearsLater) {
        return 'Survived 7 years - IHT-free';
      }

      const yearsRemaining = Math.ceil((sevenYearsLater - now) / (365 * 24 * 60 * 60 * 1000));
      return `${yearsRemaining} ${yearsRemaining === 1 ? 'year' : 'years'} remaining`;
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

    // Taper relief methods (HMRC rules)
    shouldShowTaperRelief(gift) {
      // Only show taper relief for PETs (Potentially Exempt Transfers)
      if (gift.gift_type !== 'pet') {
        return false;
      }

      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      const now = new Date();

      // Only show if gift is still within 7 years
      return now < sevenYearsLater;
    },

    getTaperReliefAtYear(year) {
      // HMRC taper relief schedule:
      // Years 0-3: 40% IHT rate (no relief)
      // Year 3-4: 32% (20% relief)
      // Year 4-5: 24% (40% relief)
      // Year 5-6: 16% (60% relief)
      // Year 6-7: 8% (80% relief)
      // Year 7+: 0% (100% relief - IHT-free)

      if (year <= 3) return 40;
      if (year === 4) return 32;
      if (year === 5) return 24;
      if (year === 6) return 16;
      if (year === 7) return 8;
      return 0;
    },

    getTaperReliefPercentage(gift) {
      const giftDate = new Date(gift.gift_date);
      const now = new Date();

      // Calculate years elapsed (with decimal precision)
      const yearsElapsed = (now - giftDate) / (365.25 * 24 * 60 * 60 * 1000);

      // HMRC taper relief rules
      if (yearsElapsed < 3) return 40; // Full 40% IHT rate
      if (yearsElapsed < 4) return 32; // 20% taper relief
      if (yearsElapsed < 5) return 24; // 40% taper relief
      if (yearsElapsed < 6) return 16; // 60% taper relief
      if (yearsElapsed < 7) return 8;  // 80% taper relief
      return 0; // 100% relief - IHT-free
    },

    getTimelineProgress(gift) {
      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      const now = new Date();

      const totalDuration = sevenYearsLater - giftDate;
      const elapsed = now - giftDate;

      return Math.min(100, Math.max(0, (elapsed / totalDuration) * 100));
    },

    getTimelineColor(gift) {
      const percentage = this.getTaperReliefPercentage(gift);

      if (percentage >= 32) return '#f59e0b'; // Amber (high IHT rate)
      if (percentage >= 16) return '#3b82f6'; // Blue (moderate IHT rate)
      return '#10b981'; // Green (low IHT rate)
    },

    getYearLabelClass(gift, year) {
      const giftDate = new Date(gift.gift_date);
      const now = new Date();
      const yearsElapsed = (now - giftDate) / (365.25 * 24 * 60 * 60 * 1000);

      if (yearsElapsed >= year) {
        return 'text-white font-bold';
      }
      return 'text-gray-600';
    },

    getSevenYearDate(gift) {
      const giftDate = new Date(gift.gift_date);
      const sevenYearsLater = new Date(giftDate);
      sevenYearsLater.setFullYear(sevenYearsLater.getFullYear() + 7);
      return sevenYearsLater;
    },

    openCreateGiftForm() {
      this.currentGift = null;
      this.formMode = 'create';
      this.showGiftForm = true;
    },

    editGift(gift) {
      this.currentGift = gift;
      this.formMode = 'edit';
      this.showGiftForm = true;
    },

    closeGiftForm() {
      this.showGiftForm = false;
      this.currentGift = null;
      this.formMode = 'create';
      this.successMessage = '';
      this.errorMessage = '';
    },

    async handleSaveGift(giftData) {
      this.errorMessage = '';
      this.successMessage = '';

      try {
        if (this.formMode === 'edit') {
          await this.updateGift({
            id: giftData.id,
            giftData: giftData,
          });
          this.successMessage = 'Gift updated successfully';
        } else {
          await this.createGift(giftData);
          this.successMessage = 'Gift recorded successfully';
        }

        // Close the form after successful save
        this.closeGiftForm();

        // Show success message briefly
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Failed to save gift:', error);
        this.errorMessage = error.response?.data?.message || error.message || 'Failed to save gift';
      }
    },

    async handleDeleteGift(id) {
      if (confirm('Are you sure you want to delete this gift record?')) {
        this.errorMessage = '';

        try {
          await this.deleteGift(id);
          this.successMessage = 'Gift deleted successfully';

          setTimeout(() => {
            this.successMessage = '';
          }, 3000);
        } catch (error) {
          console.error('Failed to delete gift:', error);
          this.errorMessage = error.response?.data?.message || error.message || 'Failed to delete gift';
        }
      }
    },
  },
};
</script>
