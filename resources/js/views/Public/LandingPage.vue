<template>
  <PublicLayout>
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-centre">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Take Control of Your Financial Future
          </h1>
          <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Comprehensive financial planning tools for UK individuals and families.
            Plan your protection, savings, investments, retirement, and estate.
          </p>
          <div class="flex flex-col sm:flex-row justify-centre gap-4">
            <router-link
              to="/register"
              class="px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colours shadow-lg"
            >
              Get Started Free
            </router-link>
            <router-link
              to="/learning-centre"
              class="px-8 py-4 bg-blue-500 text-white rounded-lg font-semibold text-lg hover:bg-blue-400 transition-colours"
            >
              Learn More
            </router-link>
          </div>
        </div>
      </div>

      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-blue-500 rounded-full opacity-20 blur-3xl"></div>
      <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-96 h-96 bg-blue-400 rounded-full opacity-20 blur-3xl"></div>
    </div>

    <!-- Features Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
      <div class="text-centre mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Five Integrated Planning Modules
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Everything you need to plan your financial future in one comprehensive system
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Protection -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Protection Planning</h3>
          <p class="text-gray-600 mb-4">
            Life insurance, critical illness, and income protection analysis. Calculate coverage gaps and adequacy scores.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Coverage gap analysis</li>
            <li>• Human capital calculation</li>
            <li>• Policy comparison</li>
          </ul>
        </div>

        <!-- Savings -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Savings Planning</h3>
          <p class="text-gray-600 mb-4">
            Emergency fund tracker, savings goals, and ISA allowance monitoring (£20,000 annual limit).
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Emergency fund runway</li>
            <li>• ISA tracker</li>
            <li>• Goal progress</li>
          </ul>
        </div>

        <!-- Investment -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Investment Planning</h3>
          <p class="text-gray-600 mb-4">
            Portfolio analysis, asset allocation, and Monte Carlo simulations for goal probability.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Portfolio analysis</li>
            <li>• Monte Carlo simulations</li>
            <li>• Tax efficiency</li>
          </ul>
        </div>

        <!-- Retirement -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Retirement Planning</h3>
          <p class="text-gray-600 mb-4">
            Pension projections, contribution optimization, and retirement readiness scoring with £60k annual allowance tracking.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Pension projections</li>
            <li>• Annual allowance tracker</li>
            <li>• Readiness assessment</li>
          </ul>
        </div>

        <!-- Estate -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Estate Planning</h3>
          <p class="text-gray-600 mb-4">
            IHT calculations, gifting strategies, will planning, and intestacy rules with NRB/RNRB tracking.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• IHT calculation (£325k NRB)</li>
            <li>• Gifting strategy</li>
            <li>• Will & intestacy planning</li>
          </ul>
        </div>

        <!-- Holistic -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-centre justify-centre mb-4">
            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColour">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Holistic Planning</h3>
          <p class="text-gray-600 mb-4">
            Cross-module recommendations, cash flow coordination, and unified financial health score.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Financial health score</li>
            <li>• Prioritized actions</li>
            <li>• 20-year projections</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- FCA News Section -->
    <div class="bg-gray-100 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-centre mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Latest from the FCA
          </h2>
          <p class="text-xl text-gray-600">
            Stay informed with the latest regulatory updates from the Financial Conduct Authority
          </p>
        </div>

        <div v-if="loadingNews" class="text-centre py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <p class="mt-2 text-gray-600">Loading FCA news...</p>
        </div>

        <div v-else-if="fcaNews.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div
            v-for="(item, index) in fcaNews"
            :key="index"
            class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-shadow"
          >
            <div class="text-xs text-blue-600 font-semibold mb-2">{{ item.date }}</div>
            <h3 class="text-lg font-bold text-gray-900 mb-3">{{ item.title }}</h3>
            <p class="text-gray-600 mb-4 text-sm">{{ item.excerpt }}</p>
            <a :href="item.url" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
              Read more →
            </a>
          </div>
        </div>

        <div v-else class="bg-white rounded-lg shadow-md p-8 text-centre">
          <p class="text-gray-600 mb-4">Unable to load FCA news at this time.</p>
          <a href="https://www.fca.org.uk/news" target="_blank" class="text-blue-600 hover:text-blue-700 font-medium">
            Visit FCA News →
          </a>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-centre">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Ready to Plan Your Financial Future?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Get started with TenGo today. Create your free account and access all planning tools.
        </p>
        <router-link
          to="/register"
          class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colours shadow-lg"
        >
          Create Free Account
        </router-link>
      </div>
    </div>
  </PublicLayout>
</template>

<script>
import PublicLayout from '@/layouts/PublicLayout.vue';

export default {
  name: 'LandingPage',

  components: {
    PublicLayout,
  },

  data() {
    return {
      loadingNews: true,
      fcaNews: [],
    };
  },

  mounted() {
    this.loadFCANews();
  },

  methods: {
    loadFCANews() {
      // Simulated FCA news - in production, this would fetch from FCA API or RSS feed
      setTimeout(() => {
        this.fcaNews = [
          {
            date: 'October 2025',
            title: 'FCA warns consumers about pension scams',
            excerpt: 'New guidance on identifying and avoiding pension transfer scams targeting UK savers.',
            url: 'https://www.fca.org.uk/news'
          },
          {
            date: 'October 2025',
            title: 'Updated Consumer Duty guidance',
            excerpt: 'Financial firms must ensure products and services deliver good outcomes for retail customers.',
            url: 'https://www.fca.org.uk/news'
          },
          {
            date: 'September 2025',
            title: 'ISA allowance remains at £20,000',
            excerpt: 'Chancellor confirms ISA annual allowance will remain unchanged for 2025/26 tax year.',
            url: 'https://www.fca.org.uk/news'
          },
        ];
        this.loadingNews = false;
      }, 1000);
    },
  },
};
</script>
