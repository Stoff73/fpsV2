<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-h2 font-display text-gray-900">Version Information</h1>
        <p class="mt-2 text-body-base text-gray-600">
          FPS - Financial Planning System version history and changelog
        </p>
      </div>

      <!-- Current Version Card -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-h3 font-display text-gray-900">Current Version</h2>
              <p class="mt-1 text-body-sm text-gray-600">Latest release information</p>
            </div>
            <div class="text-right">
              <div class="text-3xl font-bold text-primary-600">v0.1.2.3</div>
              <div class="text-body-sm text-gray-500 mt-1">Beta Release</div>
            </div>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <h3 class="text-body-sm font-semibold text-gray-700 mb-2">Release Date</h3>
              <p class="text-body-base text-gray-900">21-25 October 2025</p>
            </div>
            <div>
              <h3 class="text-body-sm font-semibold text-gray-700 mb-2">Status</h3>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-blue-100 text-blue-800">
                🚀 Active Development
              </span>
            </div>
          </div>

          <!-- What's New -->
          <div class="mb-6">
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">✨ What's New in v0.1.2.3</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Comprehensive Protection Plan:</strong> Professional-quality report generation with complete protection analysis including executive summary with adequacy scores (0-100), coverage gap analysis for Life/CI/IP, optimized strategy with prioritized recommendations and cost estimates, scenario analysis, and print/PDF export functionality</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Protection Plan Dashboard Integration:</strong> Direct access to comprehensive protection plan from Dashboard Plans card with professional document-style layout, color-coded visual elements, and actionable next steps categorized by timeframe</span>
              </li>
            </ul>
          </div>

          <!-- Bug Fixes -->
          <div class="mb-6">
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🐛 Bug Fixes in v0.1.2.3</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Protection Plan Array Key Mismatch:</strong> Fixed incorrect array key access throughout ComprehensiveProtectionPlanService - updated to match ProtectionAgent's actual data structure (life_coverage, critical_illness_coverage, income_protection_coverage, total_gap, gaps_by_category)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Type Errors with str_replace():</strong> Fixed "Argument #3 ($subject) must be of type array|string, int given" errors by adding type checking for policy_type, marital_status, and category fields before calling str_replace()</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Income Protection Data Structure:</strong> Fixed accessing non-existent fields (premium_amount, premium_frequency, benefit_period_months) - updated to use correct fields from ProtectionAgent response (benefit_amount, benefit_frequency, deferred_period_weeks)</span>
              </li>
            </ul>
          </div>

          <!-- Current Functionality -->
          <div>
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🚀 Current Functionality</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">✅ Completed</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• User authentication (login, logout, registration)</li>
                  <li>• User profile management</li>
                  <li>• Family members tracking</li>
                  <li>• Income & occupation details</li>
                  <li>• Assets overview (properties, investments, cash)</li>
                  <li>• Liabilities overview (mortgages, debts)</li>
                  <li>• Personal accounts management</li>
                  <li>• Spouse account creation & linking</li>
                  <li>• Joint ownership across all assets</li>
                  <li>• Trust ownership tracking</li>
                  <li>• Will planning with bequests</li>
                  <li>• Main dashboard with module cards</li>
                  <li>• Settings page</li>
                  <li>• Error handling and validation</li>
                </ul>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">🚧 In Development</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Protection module (life insurance, critical illness)</li>
                  <li>• Savings module (emergency fund, goals, ISA tracking)</li>
                  <li>• Investment module (portfolio analysis, Monte Carlo)</li>
                  <li>• Retirement module (pension tracking, projections)</li>
                  <li>• Estate module enhancements (gifting strategy, probate)</li>
                  <li>• Trusts management enhancements</li>
                  <li>• Holistic planning coordination</li>
                  <li>• Advanced analytics and reporting</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Version History -->
      <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-h3 font-display text-gray-900">Version History</h2>
          <p class="mt-1 text-body-sm text-gray-600">Previous releases and changes</p>
        </div>
        <div class="p-6">
          <!-- v0.1.2.2 -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.1.2.2</h3>
                <p class="text-body-sm text-gray-600">21-23 October 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-green-100 text-green-800">
                Beta Release
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Second Death IHT Planning with actuarial life expectancy calculations</li>
                  <li>• Life Policy Strategy (Whole of Life vs. Self-Insurance comparison)</li>
                  <li>• Cross-Module Data Integration for estate planning</li>
                  <li>• Dual Gifting Timelines for married couples</li>
                  <li>• NRB Transfer Tracking (up to £650k total NRB)</li>
                  <li>• IHT Mitigation Strategies with priority badges</li>
                  <li>• Spouse Account Management (auto-creation & linking)</li>
                  <li>• Joint Ownership across all asset types</li>
                  <li>• Trust Ownership tracking</li>
                  <li>• Spouse Data Sharing Permissions</li>
                  <li>• Email Notifications for spouse accounts</li>
                  <li>• Password Security (first-time login password change)</li>
                  <li>• Will Planning Module with death scenarios & bequests</li>
                </ul>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Bug Fixes</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Fixed Second Death IHT calculation undefined property error</li>
                  <li>• Fixed type error with nullable arrays in mitigation strategies</li>
                  <li>• Fixed incorrect array access for different IHT data structures</li>
                  <li>• Fixed Investment Account Form 422 validation error</li>
                  <li>• Fixed Estate IHT calculation to include liabilities</li>
                  <li>• Fixed Property & Mortgage calculation errors</li>
                  <li>• Fixed Dashboard Estate display issues</li>
                  <li>• Fixed Will Planning authentication (401 errors)</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- v0.1.1 -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.1.1</h3>
                <p class="text-body-sm text-gray-600">20 October 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-gray-100 text-gray-700">
                Internal Release
              </span>
            </div>
            <ul class="space-y-2 text-body-sm text-gray-700">
              <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Net Worth module improvements (property management, ISA tracking)
              </li>
              <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Investment account management components
              </li>
              <li class="flex items-start">
                <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Emergency fund tracking and analysis
              </li>
            </ul>
          </div>

          <!-- v0.1.0 -->
          <div class="mb-4">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.1.0</h3>
                <p class="text-body-sm text-gray-600">18 October 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-yellow-100 text-yellow-800">
                Alpha Release
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Laravel 10.x backend with Sanctum authentication</li>
                  <li>• Vue.js 3 frontend with full auth flow</li>
                  <li>• User Profile module (personal info, family, income, assets, liabilities)</li>
                  <li>• Main dashboard with module overview cards</li>
                  <li>• Settings page</li>
                  <li>• Pest testing framework (60+ tests)</li>
                </ul>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Bug Fixes</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Fixed rate limiting issues (disabled throttle for development)</li>
                  <li>• Resolved infinite loop in FamilyMembers component</li>
                  <li>• Fixed missing axios import in TrustsDashboard</li>
                  <li>• Corrected router navigation warnings</li>
                  <li>• Restored missing navbar in NetWorthDashboard</li>
                  <li>• Fixed RecommendationsAggregatorService 500 error</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Technical Details -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-h3 font-display text-gray-900">Technical Stack</h2>
          <p class="mt-1 text-body-sm text-gray-600">Technology and frameworks used in this version</p>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Backend</h4>
              <ul class="space-y-1 text-body-sm text-gray-700">
                <li>• Laravel 10.x</li>
                <li>• PHP 8.2+</li>
                <li>• MySQL 8.0+</li>
                <li>• Laravel Sanctum (auth)</li>
                <li>• Pest (testing)</li>
              </ul>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Frontend</h4>
              <ul class="space-y-1 text-body-sm text-gray-700">
                <li>• Vue.js 3</li>
                <li>• Vuex 4.x (state management)</li>
                <li>• Vue Router 4.x</li>
                <li>• Vite 5.x (build tool)</li>
                <li>• Tailwind CSS 3.x</li>
              </ul>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Development</h4>
              <ul class="space-y-1 text-body-sm text-gray-700">
                <li>• Git version control</li>
                <li>• Laravel Pint (code formatting)</li>
                <li>• PSR-12 coding standards</li>
                <li>• Hot Module Replacement (HMR)</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Note -->
      <div class="mt-8 text-center text-body-sm text-gray-500">
        <p>For detailed changelog and bug fixes for v0.1.2.3, see the <a href="/OCTOBER_2025_FEATURES_UPDATE.md" class="text-primary-600 hover:text-primary-700 underline">October 2025 Features Update</a> document.</p>
        <p class="mt-2">Built with Claude Code</p>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'Version',

  components: {
    AppLayout,
  },
};
</script>
