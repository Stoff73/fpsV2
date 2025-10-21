<template>
  <AppLayout>
    <div class="px-4 sm:px-0">
      <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-h1 text-gray-900">
          Welcome to FPS
        </h1>

        <!-- Refresh Button -->
        <button
          @click="refreshDashboard"
          :disabled="refreshing"
          class="flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg
            :class="{'animate-spin': refreshing}"
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          <span>{{ refreshing ? 'Refreshing...' : 'Refresh' }}</span>
        </button>
      </div>

      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Card 1: Net Worth (Phase 3) -->
        <NetWorthOverviewCard />

        <!-- Card 2: Retirement Planning -->
        <div v-if="loading.retirement" class="card animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-full"></div>
        </div>
        <div v-else-if="errors.retirement" class="card border-2 border-red-300 bg-red-50">
          <h3 class="text-h4 text-red-900 mb-2">Retirement Module</h3>
          <p class="text-body text-red-700 mb-4">
            Failed to load retirement data. {{ errors.retirement }}
          </p>
          <button
            @click="retryLoadModule('retirement')"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Retry
          </button>
        </div>
        <RetirementOverviewCard
          v-else
          :total-pension-value="retirementData.totalPensionValue"
          :projected-income="retirementData.projectedIncome"
          :years-to-retirement="retirementData.yearsToRetirement"
        />

        <!-- Card 3: Estate Planning -->
        <div v-if="loading.estate" class="card animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-full"></div>
        </div>
        <div v-else-if="errors.estate" class="card border-2 border-red-300 bg-red-50">
          <h3 class="text-h4 text-red-900 mb-2">Estate Module</h3>
          <p class="text-body text-red-700 mb-4">
            Failed to load estate data. {{ errors.estate }}
          </p>
          <button
            @click="retryLoadModule('estate')"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Retry
          </button>
        </div>
        <EstateOverviewCard
          v-else
          :taxable-estate="estateData.taxableEstate"
          :iht-liability="estateData.ihtLiability"
          :probate-readiness="estateData.probateReadiness"
        />

        <!-- Card 4: Protection -->
        <div v-if="loading.protection" class="card animate-pulse">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="h-8 bg-gray-200 rounded w-1/2 mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-full"></div>
        </div>
        <div v-else-if="errors.protection" class="card border-2 border-red-300 bg-red-50">
          <h3 class="text-h4 text-red-900 mb-2">Protection Module</h3>
          <p class="text-body text-red-700 mb-4">
            Failed to load protection data. {{ errors.protection }}
          </p>
          <button
            @click="retryLoadModule('protection')"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Retry
          </button>
        </div>
        <ProtectionOverviewCard
          v-else
          :adequacy-score="protectionData.adequacyScore"
          :total-coverage="protectionData.totalCoverage"
          :premium-total="protectionData.premiumTotal"
          :critical-gaps="protectionData.criticalGaps"
        />

        <!-- Card 5: Actions & Recommendations (Phase 5) -->
        <div class="lg:col-span-2">
          <QuickActions />
        </div>

        <!-- Card 6: Trusts (Phase 6) -->
        <TrustsOverviewCard />

        <!-- Card 7: UK Taxes & Allowances (Admin Only) -->
        <UKTaxesOverviewCard v-if="isAdmin" />
      </div>
    </div>
  </AppLayout>
</template>

<script>
import { mapGetters } from 'vuex';
import AppLayout from '@/layouts/AppLayout.vue';
import NetWorthOverviewCard from '@/components/Dashboard/NetWorthOverviewCard.vue';
import ProtectionOverviewCard from '@/components/Protection/ProtectionOverviewCard.vue';
import RetirementOverviewCard from '@/components/Retirement/RetirementOverviewCard.vue';
import EstateOverviewCard from '@/components/Estate/EstateOverviewCard.vue';
import QuickActions from '@/components/Dashboard/QuickActions.vue';
import TrustsOverviewCard from '@/components/Trusts/TrustsOverviewCard.vue';
import UKTaxesOverviewCard from '@/components/Dashboard/UKTaxesOverviewCard.vue';

export default {
  name: 'Dashboard',

  components: {
    AppLayout,
    NetWorthOverviewCard,
    ProtectionOverviewCard,
    RetirementOverviewCard,
    EstateOverviewCard,
    QuickActions,
    TrustsOverviewCard,
    UKTaxesOverviewCard,
  },

  data() {
    return {
      loading: {
        protection: false,
        retirement: false,
        estate: false,
      },
      errors: {
        protection: null,
        retirement: null,
        estate: null,
      },
      refreshing: false,
    };
  },

  computed: {
    ...mapGetters('auth', ['isAdmin']),
    ...mapGetters('netWorth', {
      netWorthValue: 'netWorth',
      netWorthAssets: 'totalAssets',
      netWorthLiabilities: 'totalLiabilities',
    }),
    ...mapGetters('protection', {
      protectionAdequacyScore: 'adequacyScore',
      protectionTotalCoverage: 'totalCoverage',
      protectionTotalPremium: 'totalPremium',
      protectionCoverageGaps: 'coverageGaps',
    }),
    ...mapGetters('retirement', {
      retirementTotalPensionValue: 'totalPensionWealth',
      retirementProjectedIncome: 'projectedIncome',
      retirementYearsToRetirement: 'yearsToRetirement',
    }),
    ...mapGetters('estate', {
      estateIHTLiability: 'ihtLiability',
      estateProbateReadiness: 'probateReadiness',
      estateTaxableEstate: 'taxableEstate',
    }),

    netWorthData() {
      return {
        netWorth: this.netWorthValue || 0,
        totalAssets: this.netWorthAssets || 0,
        totalLiabilities: this.netWorthLiabilities || 0,
      };
    },

    protectionData() {
      return {
        adequacyScore: this.protectionAdequacyScore || 0,
        totalCoverage: this.protectionTotalCoverage || 0,
        premiumTotal: this.protectionTotalPremium || 0,
        criticalGaps: this.protectionCoverageGaps?.filter(gap => gap.severity === 'high').length || 0,
      };
    },

    retirementData() {
      return {
        totalPensionValue: this.retirementTotalPensionValue || 0,
        projectedIncome: this.retirementProjectedIncome || 0,
        yearsToRetirement: this.retirementYearsToRetirement || 0,
      };
    },

    estateData() {
      return {
        taxableEstate: this.estateTaxableEstate || 0,
        ihtLiability: this.estateIHTLiability || 0,
        probateReadiness: this.estateProbateReadiness || 0,
      };
    },
  },

  methods: {
    async loadAllData() {
      // Load all module data in parallel with Promise.allSettled
      const moduleLoaders = [
        { name: 'netWorth', action: 'netWorth/fetchOverview' },
        { name: 'protection', action: 'protection/fetchProtectionData' },
        { name: 'retirement', action: 'retirement/fetchRetirementData' },
        { name: 'retirement', action: 'retirement/analyzeRetirement' },
        { name: 'estate', action: 'estate/fetchEstateData' },
        { name: 'estate', action: 'estate/calculateIHT' },
        { name: 'recommendations', action: 'recommendations/fetchRecommendations' },
        { name: 'trusts', action: 'trusts/fetchTrusts' },
      ];

      // Set all modules to loading
      Object.keys(this.loading).forEach(key => {
        this.loading[key] = true;
        this.errors[key] = null;
      });

      // Create promises for all module loads
      const promises = moduleLoaders.map(loader =>
        this.$store.dispatch(loader.action)
          .then(() => ({ module: loader.name, success: true }))
          .catch(error => ({
            module: loader.name,
            success: false,
            error: error.response?.data?.message || error.message || 'Unknown error'
          }))
      );

      // Wait for all promises to settle
      const results = await Promise.allSettled(promises);

      // Process results
      results.forEach(result => {
        if (result.status === 'fulfilled') {
          const { module, success, error } = result.value;
          if (!success && this.loading.hasOwnProperty(module)) {
            this.errors[module] = error;
          }
          if (this.loading.hasOwnProperty(module)) {
            this.loading[module] = false;
          }
        } else {
          // Promise was rejected
          console.error('Failed to load module:', result.reason);
        }
      });
    },

    async retryLoadModule(moduleName) {
      this.loading[moduleName] = true;
      this.errors[moduleName] = null;

      const actions = {
        protection: ['protection/fetchProtectionData'],
        retirement: ['retirement/fetchRetirementData', 'retirement/analyzeRetirement'],
        estate: ['estate/fetchEstateData', 'estate/calculateIHT'],
      };

      try {
        const moduleActions = actions[moduleName] || [];
        await Promise.all(
          moduleActions.map(action => this.$store.dispatch(action))
        );
        this.loading[moduleName] = false;
      } catch (error) {
        this.errors[moduleName] = error.response?.data?.message || error.message || 'Unknown error';
        this.loading[moduleName] = false;
      }
    },

    async refreshDashboard() {
      this.refreshing = true;
      await this.loadAllData();
      this.refreshing = false;
    },
  },

  mounted() {
    // Load all data when dashboard mounts
    this.loadAllData();
  },
};
</script>
