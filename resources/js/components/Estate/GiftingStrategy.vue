<template>
  <div class="gifting-strategy-tab">
    <!-- Planned Gifting Strategy Section -->
    <div v-if="plannedStrategy" class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Planned Gifting Strategy</h2>
        <button
          @click="refreshPlannedStrategy"
          class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
          :disabled="loadingStrategy"
        >
          <svg v-if="!loadingStrategy" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <div v-else class="animate-spin h-4 w-4 mr-1 border-2 border-blue-600 border-t-transparent rounded-full"></div>
          Refresh
        </button>
      </div>

      <!-- Life Expectancy Summary -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <p class="text-sm text-gray-600 mb-1">Current Age</p>
          <p class="text-2xl font-bold text-gray-900">{{ plannedStrategy.life_expectancy_analysis.current_age }}</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <p class="text-sm text-gray-600 mb-1">Life Expectancy</p>
          <p class="text-2xl font-bold text-gray-900">{{ plannedStrategy.life_expectancy_analysis.life_expectancy_years }} years</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <p class="text-sm text-gray-600 mb-1">Years Until Death</p>
          <p class="text-2xl font-bold text-gray-900">{{ plannedStrategy.life_expectancy_analysis.years_until_expected_death }}</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-green-100">
          <p class="text-sm text-green-700 mb-1 font-medium">Complete PET Cycles</p>
          <p class="text-2xl font-bold text-green-900">{{ plannedStrategy.life_expectancy_analysis.complete_7_year_pet_cycles }}</p>
          <p class="text-xs text-green-600">7-year cycles available</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <p class="text-sm text-gray-600 mb-1">Current IHT Liability</p>
          <p class="text-2xl font-bold text-red-600">{{ formatCurrency(plannedStrategy.current_estate.iht_liability) }}</p>
        </div>
      </div>

      <!-- Annual Exemption Plan -->
      <div v-if="plannedStrategy.annual_exemption_plan" class="bg-white rounded-lg p-6 border border-green-200 mb-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Annual Exemption (£3,000/year)</h3>
          <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            Immediately Exempt - No Risk
          </span>
        </div>

        <p class="text-gray-700 mb-4">
          You can gift £{{ formatNumber(plannedStrategy.annual_exemption_plan.annual_amount) }} per year using the annual exemption. These gifts are immediately exempt from IHT with no 7-year waiting period.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-green-50 rounded p-3">
            <p class="text-sm text-green-700 font-medium">Annual Gift</p>
            <p class="text-2xl font-bold text-green-900">{{ formatCurrency(plannedStrategy.annual_exemption_plan.annual_amount) }}</p>
            <p class="text-xs text-green-600 mt-1">Every year</p>
          </div>
          <div class="bg-blue-50 rounded p-3">
            <p class="text-sm text-blue-700 font-medium">Total Over Lifetime</p>
            <p class="text-2xl font-bold text-blue-900">{{ formatCurrency(plannedStrategy.annual_exemption_plan.total_over_lifetime) }}</p>
            <p class="text-xs text-blue-600 mt-1">{{ plannedStrategy.annual_exemption_plan.years_available }} years</p>
          </div>
          <div class="bg-purple-50 rounded p-3">
            <p class="text-sm text-purple-700 font-medium">IHT Savings</p>
            <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(plannedStrategy.annual_exemption_plan.total_iht_saved) }}</p>
            <p class="text-xs text-purple-600 mt-1">40% of gifts</p>
          </div>
        </div>
      </div>

      <!-- PET Cycle Framework -->
      <div v-if="plannedStrategy.pet_cycle_framework" class="bg-white rounded-lg p-6 border border-indigo-200 mb-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">PET Gifting Cycles (Every 7 Years)</h3>
          <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
            {{ plannedStrategy.pet_cycle_framework.cycles_available }} Cycles Available
          </span>
        </div>

        <div v-if="!plannedStrategy.pet_cycle_framework.has_iht_liability" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <p class="text-blue-900 font-medium mb-2">Educational Framework</p>
          <p class="text-blue-800 text-sm">
            You currently have no IHT liability. However, you have <strong>{{ plannedStrategy.pet_cycle_framework.cycles_available }} complete 7-year cycles</strong> available over your lifetime.
            This means you could potentially gift up to <strong>{{ formatCurrency(plannedStrategy.pet_cycle_framework.maximum_per_cycle) }}</strong> (your Nil Rate Band)
            every 7 years, for a total of <strong>{{ formatCurrency(plannedStrategy.pet_cycle_framework.total_potential) }}</strong> over {{ plannedStrategy.pet_cycle_framework.cycles_available }} cycles.
          </p>
        </div>

        <div v-else-if="petStrategy" class="mb-4">
          <p class="text-gray-700 mb-4">{{ petStrategy.description }}</p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-blue-50 rounded p-3">
              <p class="text-sm text-blue-700 font-medium">Recommended Per Cycle</p>
              <p class="text-2xl font-bold text-blue-900">{{ formatCurrency(petStrategy.amount_per_cycle) }}</p>
            </div>
            <div class="bg-purple-50 rounded p-3">
              <p class="text-sm text-purple-700 font-medium">Total Over {{ petStrategy.number_of_cycles }} Cycles</p>
              <p class="text-2xl font-bold text-purple-900">{{ formatCurrency(petStrategy.total_gifted) }}</p>
            </div>
            <div class="bg-green-50 rounded p-3">
              <p class="text-sm text-green-700 font-medium">Total IHT Savings</p>
              <p class="text-2xl font-bold text-green-900">{{ formatCurrency(petStrategy.iht_saved) }}</p>
            </div>
          </div>
        </div>

        <!-- PET Timeline -->
        <div class="mt-6">
          <h4 class="text-md font-semibold text-gray-900 mb-3">PET Cycle Timeline</h4>
          <div class="space-y-3">
            <div
              v-for="(cycle, index) in plannedStrategy.pet_cycle_framework.cycles"
              :key="index"
              class="flex items-center p-4 bg-gray-50 rounded-lg border border-gray-200"
            >
              <div class="flex-shrink-0 w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold">
                {{ cycle.cycle_number }}
              </div>
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="font-semibold text-gray-900">
                      PET Cycle {{ cycle.cycle_number }}
                    </p>
                    <p class="text-sm text-gray-600">Year {{ cycle.gift_year }} (Age {{ cycle.gift_age }}): Make gift</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-gray-600">Becomes IHT-free:</p>
                    <p class="font-semibold text-green-600">
                      Year {{ cycle.exempt_year }} (Age {{ cycle.exempt_age }})
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 bg-gray-50 border border-gray-200 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-900 mb-2">How PETs Work</h4>
          <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
            <li>You can gift any amount as a Potentially Exempt Transfer (PET)</li>
            <li>If you survive 7 years, the gift becomes completely IHT-free</li>
            <li>Taper relief applies from year 3-7 if death occurs before 7 years</li>
            <li>Gifting up to £{{ formatNumber(plannedStrategy.pet_cycle_framework.nil_rate_band) }} (NRB) every 7 years maximizes IHT efficiency</li>
            <li>Over {{ plannedStrategy.pet_cycle_framework.cycles_available }} cycles, you could gift up to {{ formatCurrency(plannedStrategy.pet_cycle_framework.total_potential) }} IHT-free</li>
          </ul>
        </div>
      </div>

      <!-- Other Strategies -->
      <div v-if="otherStrategies.length > 0" class="space-y-3">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Additional Strategies</h3>
        <div
          v-for="(strategy, index) in otherStrategies"
          :key="index"
          class="bg-white rounded-lg p-4 border border-gray-200"
        >
          <div class="flex items-center justify-between mb-2">
            <h4 class="font-semibold text-gray-900">{{ strategy.strategy_name }}</h4>
            <span class="text-sm font-medium text-green-600">Save {{ formatCurrency(strategy.iht_saved) }}</span>
          </div>
          <p class="text-sm text-gray-700 mb-2">{{ strategy.description }}</p>
          <div class="flex items-center justify-between text-sm">
            <span v-if="strategy.annual_amount" class="text-gray-600">Annual: {{ formatCurrency(strategy.annual_amount) }}</span>
            <span v-if="strategy.gift_amount" class="text-gray-600">Gift Amount: {{ formatCurrency(strategy.gift_amount) }}</span>
            <span class="text-gray-600">Priority: {{ strategy.priority }}</span>
          </div>
        </div>
      </div>

      <!-- Summary -->
      <div v-if="plannedStrategy.gifting_strategy && plannedStrategy.gifting_strategy.summary" class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Strategy Impact Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm text-gray-600">Original IHT Liability</p>
            <p class="text-lg font-bold text-red-600">{{ formatCurrency(plannedStrategy.gifting_strategy.summary.original_iht_liability) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Total IHT Saved</p>
            <p class="text-lg font-bold text-green-600">{{ formatCurrency(plannedStrategy.gifting_strategy.summary.total_iht_saved) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Remaining IHT Liability</p>
            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(plannedStrategy.gifting_strategy.summary.remaining_iht_liability) }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-600">Reduction</p>
            <p class="text-lg font-bold text-blue-600">{{ plannedStrategy.gifting_strategy.summary.reduction_percentage }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Error/Info Messages for Planning -->
    <div v-if="strategyError" class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div>
          <p class="font-medium">{{ strategyError }}</p>
          <p v-if="requiresProfileUpdate" class="text-sm mt-1">
            Please update your profile with your date of birth and gender to calculate your personalized gifting strategy.
            <router-link to="/settings" class="underline font-medium">Go to Profile Settings</router-link>
          </p>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t-2 border-gray-200 my-8"></div>

    <!-- Actual Gifts Section Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Gifts Made (Actual)</h2>
      <p class="text-gray-600">Track gifts you've actually made and monitor their PET status</p>
    </div>

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
import estateService from '@/services/estateService';

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
      plannedStrategy: null,
      loadingStrategy: false,
      strategyError: null,
      requiresProfileUpdate: false,
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

    petStrategy() {
      if (!this.plannedStrategy?.gifting_strategy?.strategies) {
        return null;
      }
      return this.plannedStrategy.gifting_strategy.strategies.find(
        s => s.strategy_name === 'Potentially Exempt Transfers (PETs)'
      );
    },

    otherStrategies() {
      if (!this.plannedStrategy?.gifting_strategy?.strategies) {
        return [];
      }
      return this.plannedStrategy.gifting_strategy.strategies.filter(
        s => s.strategy_name !== 'Potentially Exempt Transfers (PETs)' &&
             s.strategy_name !== 'Annual Exemption'
      );
    },
  },

  mounted() {
    this.loadPlannedStrategy();
  },

  methods: {
    ...mapActions('estate', ['createGift', 'updateGift', 'deleteGift']),

    async loadPlannedStrategy() {
      this.loadingStrategy = true;
      this.strategyError = null;
      this.requiresProfileUpdate = false;

      try {
        console.log('[GiftingStrategy] Loading planned strategy...');
        const response = await estateService.getPlannedGiftingStrategy();
        console.log('[GiftingStrategy] API Response:', response);

        if (response.success) {
          this.plannedStrategy = response.data;
          console.log('[GiftingStrategy] Planned strategy loaded:', this.plannedStrategy);
        } else {
          this.strategyError = response.message || 'Failed to load planned gifting strategy';
          this.requiresProfileUpdate = response.requires_profile_update || false;
          console.error('[GiftingStrategy] API returned error:', this.strategyError);
        }
      } catch (error) {
        console.error('[GiftingStrategy] Failed to load planned strategy:', error);
        console.error('[GiftingStrategy] Error response:', error.response);
        if (error.response?.status === 422) {
          this.strategyError = error.response.data.message;
          this.requiresProfileUpdate = error.response.data.requires_profile_update || false;
        } else {
          this.strategyError = 'Unable to calculate gifting strategy. Please ensure your profile is complete.';
        }
      } finally {
        this.loadingStrategy = false;
      }
    },

    async refreshPlannedStrategy() {
      await this.loadPlannedStrategy();
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(value);
    },

    formatNumber(value) {
      return new Intl.NumberFormat('en-GB', {
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
