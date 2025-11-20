<template>
  <PublicLayout>
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-blue-900 overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Take Control of Your Financial Future
          </h1>
          <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Comprehensive financial planning tools for UK individuals and families.
            Plan your protection, savings, investments, retirement, and estate.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <router-link
              to="/register"
              class="px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors shadow-lg"
            >
              Get Started Free
            </router-link>
            <router-link
              to="/learning-centre"
              class="px-8 py-4 bg-blue-500 text-white rounded-lg font-semibold text-lg hover:bg-blue-400 transition-colors"
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
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          What the Boma Build Does
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          A comprehensive testing environment to evaluate core planning features and data integration
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Personal Information & Data Entry -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Personal Information & Income</h3>
          <p class="text-gray-600 mb-4">
            Enter comprehensive personal details, occupation information, and income from multiple sources.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Employment and occupation details</li>
            <li>• Multiple income streams tracking</li>
            <li>• Detailed expenditure entry</li>
            <li>• Spouse and family member entry</li>
          </ul>
        </div>

        <!-- Estate Planning -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Estate Planning</h3>
          <p class="text-gray-600 mb-4">
            Comprehensive estate analysis with current and projected IHT liability calculations.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Overview of current estate value</li>
            <li>• Forward projection to approximate death age</li>
            <li>• IHT liability calculation</li>
            <li>• Mitigation strategies: Gifting & Life Policies</li>
          </ul>
        </div>

        <!-- Protection Planning -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Protection Planning</h3>
          <p class="text-gray-600 mb-4">
            Analyse your current protection coverage and identify any gaps in life and income protection.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Overview of current protection plans</li>
            <li>• Life insurance coverage adequacy check</li>
            <li>• Income protection coverage assessment</li>
            <li>• Example protection plan generation</li>
          </ul>
        </div>

        <!-- Financial Data Management -->
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-shadow">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Comprehensive Data Entry</h3>
          <p class="text-gray-600 mb-4">
            Enter and manage all your financial assets, accounts, and property details.
          </p>
          <ul class="text-sm text-gray-500 space-y-1">
            <li>• Pension plans (DC, DB & State)</li>
            <li>• Investment accounts</li>
            <li>• Cash and bank accounts</li>
            <li>• Property portfolio</li>
          </ul>
        </div>
      </div>

      <!-- Additional Features -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-md p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Additional Features Available</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Spouse Account Linking</h4>
            <p class="text-sm text-gray-600">Link accounts for joint assets, liabilities, and shared planning</p>
          </div>

          <div class="text-center">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Balance Sheet View</h4>
            <p class="text-sm text-gray-600">View joint and personal balance sheets with asset/liability breakdown</p>
          </div>

          <div class="text-center">
            <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900 mb-2">Letter to Spouse</h4>
            <p class="text-sm text-gray-600">Generate example letter with essential information for your partner</p>
          </div>
        </div>

        <div class="mt-8 text-center">
          <p class="text-sm text-gray-600 italic">Example Investment, Protection, and Estate Plans are available for review</p>
        </div>
      </div>
    </div>

    <!-- Future Vision Section -->
    <div class="bg-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            What Tengo Will Eventually Be
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Full financial planning software allowing users to ensure they are covered for all financial planning scenarios
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Protection Planning Module -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-6 border border-green-100">
            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Protection Planning Module</h3>
            <p class="text-sm text-gray-600">Showing any gaps, issues and strategies to protect you and your family.</p>
          </div>

          <!-- Investment Planning Module -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-100">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Investment Planning Module</h3>
            <p class="text-sm text-gray-600">With all the relevant tools to optimise both tax and portfolio performance.</p>
          </div>

          <!-- Retirement Planning Module -->
          <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-6 border border-purple-100">
            <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Retirement Planning Module</h3>
            <p class="text-sm text-gray-600">For accumulation and decumulation, ensuring tax optimal strategies for both.</p>
          </div>

          <!-- Estate Planning Module -->
          <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-lg p-6 border border-red-100">
            <div class="w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Estate Planning Module</h3>
            <p class="text-sm text-gray-600">Linking all modules to ensure strategies account for current and future tax, income and life needs, while protecting your wealth and ensuring generational transfer is tax optimised.</p>
          </div>

          <!-- Document Repository -->
          <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-6 border border-orange-100">
            <div class="w-10 h-10 bg-orange-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Document Repository</h3>
            <p class="text-sm text-gray-600">A repository for all your important financial and tax documents, securely stored and accessible.</p>
          </div>

          <!-- Full Account Linking -->
          <div class="bg-gradient-to-br from-teal-50 to-cyan-50 rounded-lg p-6 border border-teal-100">
            <div class="w-10 h-10 bg-teal-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Full Account Linking</h3>
            <p class="text-sm text-gray-600">So your spouse can access all important information, documents etc., in the event of illness or death.</p>
          </div>

          <!-- Partial Account Linking -->
          <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-lg p-6 border border-pink-100">
            <div class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Partial Account Linking</h3>
            <p class="text-sm text-gray-600">View-only access for your adviser, lawyer etc., which can become full access on death through death certification verification.</p>
          </div>

          <!-- Personal Accounts -->
          <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg p-6 border border-yellow-100">
            <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Full Set of Personal Accounts</h3>
            <p class="text-sm text-gray-600">Comprehensive tracking of all your personal financial accounts and holdings in one place.</p>
          </div>

          <!-- Cross Border Planning -->
          <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-100">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center mb-4">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Cross-Border Planning</h3>
            <p class="text-sm text-gray-600">Full cross-border, country interlinked financial planning for international families and assets.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- FCA News Section -->
    <div class="bg-gray-100 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Latest from the FCA
          </h2>
          <p class="text-xl text-gray-600">
            Stay informed with the latest regulatory updates from the Financial Conduct Authority
          </p>
        </div>

        <div v-if="loadingNews" class="text-center py-8">
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

        <div v-else class="bg-white rounded-lg shadow-md p-8 text-center">
          <p class="text-gray-600 mb-4">Unable to load FCA news at this time.</p>
          <a href="https://www.fca.org.uk/news" target="_blank" class="text-blue-600 hover:text-blue-700 font-medium">
            Visit FCA News →
          </a>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
          Ready to Plan Your Financial Future?
        </h2>
        <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
          Get started with TenGo today. Create your free account and access all planning tools.
        </p>
        <router-link
          to="/register"
          class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-colors shadow-lg"
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
