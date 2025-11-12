<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-h2 font-display text-gray-900">Version Information</h1>
        <p class="mt-2 text-body-base text-gray-600">
          TenGo - Financial Planning System version history and changelog
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
              <div class="text-3xl font-bold text-primary-600">v0.2.6</div>
              <div class="text-body-sm text-gray-500 mt-1">Beta Release</div>
            </div>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <h3 class="text-body-sm font-semibold text-gray-700 mb-2">Release Date</h3>
              <p class="text-body-base text-gray-900">12 November 2025</p>
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
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🏗️ Refactoring & Cleanup in v0.2.6</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Estate Module Refactor:</strong> Removed deprecated EstateAgent.php and old IHT calculation services (GiftingStrategy, IHTCalculator, SecondDeathIHTCalculator). Added new IHTCalculationService with database persistence. Removed 14,985 lines of legacy code.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Database-Driven IHT Calculations:</strong> Added ActuarialLifeTable model with ONS 2020-2022 life expectancy data. Added IHTCalculation model for storing projected IHT liabilities. Includes 3 new migrations and seeder.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Investment Module Fixes:</strong> Fixed DateTime/Carbon type issues (2 files). Fixed polymorphic relationship access pattern - changed investmentAccount to holdable across 5 Investment service files. Fixed American/British spelling inconsistency in InvestmentDashboard.vue.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Retirement Module Cleanup:</strong> Removed confusing readiness score, income gap, retirement gauge, and readiness breakdown from RetirementReadiness.vue. Simplified to show only Years to Retirement and Projected Income placeholders.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Estate & Savings UI Cleanup:</strong> Removed legacy Estate components (Recommendations.vue, WhatIfScenarios.vue, SurvivingSpouseIHTPlanning.vue). Removed What-If Scenarios tab from Savings module. Simplified UI and removed confusing elements.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-purple-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Documentation Cleanup:</strong> Moved all deployment-related markdown files to deployment/ folder. Removed obsolete session summaries and fix tracking docs. Organised deployment guides and approval documentation.</span>
              </li>
            </ul>
          </div>

          <!-- Technical Debt -->
          <div class="mb-6">
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🔧 Technical Debt Resolved in v0.2.6</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700">Fixed polymorphic relationship access pattern across 5 Investment service files (TaxDragCalculator, AccountTypeRecommender, FeeAnalyzer)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700">Fixed DateTime/Carbon type issues in 2 Investment service files (Carbon objects can't be passed to DateTime constructor)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700">Fixed American/British spelling inconsistencies in method calls (analyzeInvestment → analyseInvestment)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700">Removed deprecated agent pattern in Estate module (EstateAgent.php deleted)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700">Consolidated IHT calculation logic into single service with database persistence (IHTCalculationService)</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Code Reduction:</strong> Removed 14,985 lines of code, added 2,492 lines (net -12,493 lines). 71 files modified: 17 deleted, 7 added, 47 updated.</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Version History Divider -->
        <div class="border-t border-gray-200 my-8"></div>

        <!-- Previous Version: v0.2.5 -->
        <div class="mb-8">
          <h2 class="text-h3 font-display text-gray-900 mb-4">Previous Release: v0.2.5</h2>
          <p class="text-body-sm text-gray-600 mb-4">Released: 10 November 2025</p>

          <div class="mb-6">
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🐛 Bug Fixes in v0.2.5</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Estate Will Tab Display:</strong> Fixed executor name and date not displaying correctly after data entry due to database schema mismatch.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Estate Spouse Gifting Timeline:</strong> Fixed incorrect "enable data sharing" message showing despite spouse accounts being linked.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Protection Policies Detection:</strong> Fixed Gap Analysis not detecting policies entered during onboarding due to property naming mismatch.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Protection Tab Reorganisation:</strong> Renamed "Recommendations" tab to "Strategy" and removed "What-If Scenarios" tab for cleaner UX.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Spouse Income in Gap Analysis:</strong> Fixed spouse income not being included in human capital calculation. Implemented proper spouse account creation with income population during onboarding, maintaining single source of truth in users table.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Education Level Data Flow:</strong> Removed duplicate education level field from Personal Information tab. Fixed onboarding data not syncing health_status, smoking_status, and education_level to users table. Education level now appears only in Health tab as intended.</span>
              </li>
            </ul>
          </div>

          <!-- Technical Improvements -->
          <div class="mb-6">
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">⚙️ Technical Improvements in v0.2.5</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Database Schema Improvements:</strong> Fixed will information schema (added executor_name field, renamed last_reviewed_date to will_last_updated). Changed income/expenditure columns from DECIMAL to DOUBLE for proper JSON serialisation.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Spouse Account Management:</strong> Enhanced FamilyMembersController to properly populate spouse user accounts with income data during creation, linking, and updates. Maintains single source of truth in users table.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Onboarding Service Enhancement:</strong> Fixed processPersonalInfo() to save health_status, smoking_status, and education_level to users table during onboarding, ensuring data flows correctly to User Profile tabs.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>UI/UX Improvements:</strong> Moved "Edit Information" button to top right in all User Profile tabs. Standardised button placement across 4 profile tab components for consistent user experience.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>British English Conversion:</strong> Converted all user-facing text from American to British English spelling (252 Vue/JS files updated). Maintained American spelling in code syntax (class names, CSS properties, API routes) per coding conventions.</span>
              </li>
              <li class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-body-base text-gray-700"><strong>Protection Module Refinements:</strong> Fixed Vuex property naming (camelCase), removed unused components, improved cache invalidation for spouse income updates.</span>
              </li>
            </ul>
          </div>

          <!-- Current Functionality -->
          <div>
            <h3 class="text-body-lg font-semibold text-gray-900 mb-4">🚀 Current Functionality</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">✅ Completed Modules</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li><strong>Core Features:</strong></li>
                  <li>• User authentication & profile management</li>
                  <li>• Family members & spouse account linking</li>
                  <li>• Income, occupation, and assets tracking</li>
                  <li>• Joint ownership & trust ownership</li>
                  <li>• Main dashboard with quick actions</li>

                  <li class="mt-2"><strong>Investment Module:</strong></li>
                  <li>• Portfolio analysis with risk metrics (Alpha, Beta, Sharpe)</li>
                  <li>• Asset allocation & diversification scoring</li>
                  <li>• Fee analysis & optimisation suggestions</li>
                  <li>• Monte Carlo simulations (1,000 iterations)</li>
                  <li>• Efficient Frontier analysis</li>
                  <li>• Holdings management & rebalancing</li>

                  <li class="mt-2"><strong>Retirement Module:</strong></li>
                  <li>• DC/DB pension tracking & projections</li>
                  <li>• State pension integration</li>
                  <li>• Annual allowance monitoring (£60k + carry forward)</li>
                  <li>• DC pension portfolio optimisation</li>
                  <li>• Holdings management for pension pots</li>
                  <li>• Retirement readiness scoring</li>
                  <li>• Decumulation planning</li>

                  <li class="mt-2"><strong>Savings Module:</strong></li>
                  <li>• Emergency fund calculator & runway analysis</li>
                  <li>• Savings goals tracking & progress</li>
                  <li>• ISA allowance tracking (£20k annual limit)</li>
                  <li>• Cash accounts management</li>
                </ul>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">✅ Completed Modules (cont.)</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li><strong>Estate Planning Module:</strong></li>
                  <li>• IHT calculation (NRB £325k + RNRB £175k)</li>
                  <li>• Second death IHT planning</li>
                  <li>• Net worth aggregation across all modules</li>
                  <li>• Gifting strategy (PET/CLT 7-year rules)</li>
                  <li>• Life policy strategy (WoL vs self-insurance)</li>
                  <li>• Will planning with bequests</li>
                  <li>• Letter to Spouse (emergency instructions)</li>
                  <li>• Trust tracking & management</li>

                  <li class="mt-2"><strong>Protection Module:</strong></li>
                  <li>• Life insurance coverage gap analysis</li>
                  <li>• Critical illness adequacy scoring</li>
                  <li>• Income protection recommendations</li>
                  <li>• Human capital calculations</li>
                  <li>• Protection plan generation</li>
                  <li>• Scenario analysis (death, CI, disability)</li>

                  <li class="mt-2"><strong>Plans Module:</strong></li>
                  <li>• Investment & Savings Plan (consolidated view)</li>
                  <li>• Risk metrics dashboard</li>
                  <li>• Goal progress tracking</li>
                  <li>• Asset allocation analysis</li>
                </ul>

                <h4 class="font-semibold text-gray-900 mb-2 mt-4">🚧 In Development</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Holistic planning coordination (cross-module)</li>
                  <li>• Advanced scenario modeling</li>
                  <li>• Tax optimisation strategies</li>
                  <li>• Probate planning</li>
                  <li>• Comprehensive reporting & exports</li>
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
          <!-- v0.2.1 -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.2.1</h3>
                <p class="text-body-sm text-gray-600">4 November 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-blue-100 text-blue-800">
                Beta Release
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Investment & Savings Plans Module with consolidated goal tracking</li>
                  <li>• DC Pension Portfolio Optimisation with full holdings management</li>
                  <li>• Polymorphic Holdings System for Investment Accounts and DC Pensions</li>
                  <li>• Advanced Risk Metrics (Alpha, Beta, Sharpe Ratio, VaR, Max Drawdown)</li>
                  <li>• Portfolio Analysis & Fee Optimisation suggestions</li>
                  <li>• Monte Carlo Simulation and Efficient Frontier integration</li>
                </ul>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Technical</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Service Reuse Architecture (shared Investment services in Retirement)</li>
                  <li>• Database migration with rollback for polymorphic holdings</li>
                  <li>• Enhanced RetirementAgent with 5 Investment module services</li>
                  <li>• 9 new API endpoints for holdings and portfolio analysis</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- v0.1.2.13 -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.1.2.13</h3>
                <p class="text-body-sm text-gray-600">29 October 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-green-100 text-green-800">
                Beta Release
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Letter to Spouse feature with comprehensive emergency instructions</li>
                  <li>• Dual view mode (edit own letter, view spouse's letter)</li>
                  <li>• Auto-population from all modules (Protection, Estate, Savings, etc.)</li>
                  <li>• Application rebranding from "FPS" to "TenGo"</li>
                </ul>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Bug Fixes</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Fixed Letter to Spouse type errors (number_format, ucfirst)</li>
                  <li>• Fixed API authentication issues (401 errors)</li>
                  <li>• Fixed component lifecycle mounting issues</li>
                  <li>• Fixed User Profile layout tab overflow</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- v0.1.2.3 -->
          <div class="mb-8 pb-8 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="text-xl font-bold text-gray-900">v0.1.2.3</h3>
                <p class="text-body-sm text-gray-600">25 October 2025</p>
              </div>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-body-sm font-medium bg-green-100 text-green-800">
                Beta Release
              </span>
            </div>
            <div class="space-y-4">
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Features</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Comprehensive Protection Plan with professional report generation</li>
                  <li>• Executive Summary with adequacy scores (0-100)</li>
                  <li>• Coverage Gap Analysis for Life/CI/IP</li>
                  <li>• Optimised Strategy with prioritized recommendations</li>
                  <li>• Scenario Analysis (death, critical illness, disability)</li>
                  <li>• Print/PDF Export functionality</li>
                </ul>
              </div>
              <div>
                <h4 class="font-semibold text-gray-900 mb-2">Bug Fixes</h4>
                <ul class="space-y-1 text-body-sm text-gray-700">
                  <li>• Fixed Protection Plan array key mismatch errors</li>
                  <li>• Fixed type errors with str_replace() for nullable fields</li>
                  <li>• Fixed Income Protection data structure access issues</li>
                </ul>
              </div>
            </div>
          </div>

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
        <p>For detailed changelog and bug fixes, see the <a href="/OCTOBER_2025_FEATURES_UPDATE.md" class="text-primary-600 hover:text-primary-700 underline">October 2025 Features Update</a> document.</p>
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
