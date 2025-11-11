<template>
  <div class="iht-planning-tab">
    <!-- Error State - No Profile -->
    <div v-if="error && !ihtData" class="bg-amber-50 border border-amber-200 rounded-lg p-6 mb-6">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-amber-800">IHT Profile Required</h3>
          <p class="mt-2 text-sm text-amber-700">{{ error }}</p>
          <p class="mt-2 text-sm text-amber-700">Please set up your IHT profile in the Estate module to see your IHT calculation.</p>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      <p class="mt-2 text-gray-600">Calculating IHT liability...</p>
    </div>

    <!-- Spouse Exemption Notice (Always show for married users) -->
    <SpouseExemptionNotice
      v-if="showSpouseExemptionNotice && secondDeathData"
      :message="secondDeathData.spouse_exemption_message"
      :has-spouse="hasSpouse"
      :data-sharing-enabled="secondDeathData.data_sharing_enabled"
      class="mb-6"
    />

    <!-- Missing Data Alert -->
    <MissingDataAlert
      v-if="secondDeathData?.missing_data && secondDeathData.missing_data.length > 0"
      :missing-data="secondDeathData.missing_data"
      :message="getMissingDataMessage()"
      class="mb-6"
    />

    <!-- Old Spouse Exemption Notice (keep for backward compatibility with non-married) -->
    <div v-if="ihtData?.spouse_exemption_applies && ihtData?.spouse_exemption > 0 && !isMarried" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-green-800">Spouse Exemption Applied</h3>
          <p class="mt-2 text-sm text-green-700">
            <strong>{{ formatCurrency(ihtData.spouse_exemption) }}</strong> ({{ formatPercent((ihtData.spouse_exemption / ihtData.net_estate_value)) }}) of your estate is exempt from IHT due to unlimited spousal transfer on death.
            <span v-if="ihtData.death_scenario === 'user_only'">This calculation assumes only you pass away. Change to "Both Dying" scenario in the Will tab for simultaneous death planning.</span>
          </p>
        </div>
      </div>
    </div>

    <!-- IHT Summary - Second Death (Married Users) -->
    <div v-if="isMarried && secondDeathData?.second_death_analysis" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- Joint Death NOW -->
      <div class="bg-blue-50 rounded-lg p-6">
        <p class="text-sm text-blue-600 font-medium mb-2">Joint Death (Now)</p>
        <p class="text-xs text-blue-500 mb-1">Current combined estate</p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(secondDeathData.second_death_analysis.current_combined_totals?.gross_assets || secondDeathData.second_death_analysis.current_iht_calculation?.gross_estate_value || 0) }}</p>
        <p class="text-xs text-blue-600 mt-2">If both die today</p>
      </div>

      <!-- Joint Death PROJECTED -->
      <div class="bg-purple-50 rounded-lg p-6">
        <p class="text-sm text-purple-600 font-medium mb-2">Joint Death (Projected)</p>
        <p class="text-xs text-purple-500 mb-1">At age {{ secondDeathData.second_death_analysis.second_death.estimated_age_at_death }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(secondDeathData.second_death_analysis.second_death.projected_combined_estate_at_second_death) }}</p>
        <p class="text-xs text-purple-600 mt-2">Projected combined estate</p>
      </div>

      <!-- Total IHT Payable -->
      <div class="bg-red-50 rounded-lg p-6">
        <p class="text-sm text-red-600 font-medium mb-2">Total IHT Payable</p>
        <div class="space-y-3">
          <div>
            <p class="text-xs text-red-500 mb-1">If both die now:</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.iht_liability || 0) }}</p>
          </div>
          <div class="border-t border-red-200 pt-2">
            <p class="text-xs text-red-500 mb-1">At age {{ secondDeathData.second_death_analysis.second_death.estimated_age_at_death }}:</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation.iht_liability) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- IHT Summary - Standard (Non-Married Users) -->
    <div v-else-if="ihtData" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-purple-50 rounded-lg p-6">
        <p class="text-sm text-purple-600 font-medium mb-2">Gross Estate</p>
        <p class="text-xs text-purple-500 mb-1">Net of liabilities</p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(ihtData?.net_estate_value || 0) }}</p>
      </div>
      <div class="bg-green-50 rounded-lg p-6">
        <p class="text-sm text-green-600 font-medium mb-2">{{ ihtData?.spouse_exemption_applies ? 'Spouse Exemption' : 'Total Allowances' }}</p>
        <p class="text-xs text-green-500 mb-1">
          {{ ihtData?.spouse_exemption_applies ? 'Unlimited transfer' : (ihtData?.rnrb_eligible && ihtData?.rnrb > 0 ? 'NRB + RNRB' : 'NRB only') }}
        </p>
        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(ihtData?.spouse_exemption_applies ? ihtData.spouse_exemption : ihtData?.total_allowance || 0) }}</p>
      </div>
      <div class="bg-red-50 rounded-lg p-6">
        <p class="text-sm text-red-600 font-medium mb-2">Total IHT Liability</p>
        <p class="text-xs text-red-500 mb-1">{{ ihtData?.death_scenario === 'both_simultaneous' ? 'Both dying' : 'If death occurs now' }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ formattedIHTLiability }}</p>
      </div>
    </div>

    <!-- IHT Breakdown - Second Death (Married Users) -->
    <div v-if="!loading && isMarried && secondDeathData?.second_death_analysis" class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">IHT Calculation Breakdown (Second Death Scenario)</h3>
      <p class="text-sm text-gray-600 mb-6">Comparison of IHT liability if death occurs now vs. at projected life expectancy (Age {{ secondDeathData.second_death_analysis.second_death.estimated_age_at_death }})</p>

      <!-- Estate Calculation Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Item</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Now</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Death at Age {{ secondDeathData.second_death_analysis.second_death.estimated_age_at_death }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- User Assets Section -->
            <template v-if="secondDeathData.assets_breakdown && secondDeathData.assets_breakdown.user">
              <!-- User Assets Header -->
              <tr class="bg-blue-50">
                <td class="px-4 py-3 text-sm font-semibold text-blue-900">{{ secondDeathData.assets_breakdown.user.name }}'s Assets</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-blue-900" colspan="2"></td>
              </tr>

              <!-- User Property Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.user.assets.property" :key="'user-property-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Property:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- User Investment Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.user.assets.investment" :key="'user-investment-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Investment:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- User Cash/Savings Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.user.assets.cash" :key="'user-cash-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Cash/Savings:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- User Business Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.user.assets.business" :key="'user-business-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Business:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- User Chattel Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.user.assets.chattel" :key="'user-chattel-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Chattel:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- User Assets Subtotal -->
              <tr class="bg-blue-100">
                <td class="px-4 py-2 text-sm font-semibold text-blue-900 pl-8">Subtotal</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-blue-900">{{ formatCurrency(secondDeathData.assets_breakdown.user.total) }}</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-blue-900">{{ formatCurrency(userAssetsProjectedTotal) }}</td>
              </tr>
            </template>

            <!-- Spouse Assets Section -->
            <template v-if="secondDeathData.data_sharing_enabled && secondDeathData.assets_breakdown.spouse">
              <!-- Spouse Assets Header -->
              <tr class="bg-purple-50">
                <td class="px-4 py-3 text-sm font-semibold text-purple-900">{{ secondDeathData.assets_breakdown.spouse.name }}'s Assets</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-purple-900" colspan="2"></td>
              </tr>

              <!-- Spouse Property Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.spouse.assets.property" :key="'spouse-property-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Property:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- Spouse Investment Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.spouse.assets.investment" :key="'spouse-investment-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Investment:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- Spouse Cash/Savings Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.spouse.assets.cash" :key="'spouse-cash-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Cash/Savings:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- Spouse Business Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.spouse.assets.business" :key="'spouse-business-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Business:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- Spouse Chattel Assets -->
              <tr v-for="(asset, index) in secondDeathData.assets_breakdown.spouse.assets.chattel" :key="'spouse-chattel-' + index" class="bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-gray-500">Chattel:</span> {{ asset.name }}
                  <span v-if="asset.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.value) }}</td>
                <td class="px-4 py-2 text-sm text-right text-gray-700">{{ formatCurrency(asset.projected_value) }}</td>
              </tr>

              <!-- Spouse Assets Subtotal -->
              <tr class="bg-purple-100">
                <td class="px-4 py-2 text-sm font-semibold text-purple-900 pl-8">Subtotal</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-purple-900">{{ formatCurrency(secondDeathData.assets_breakdown.spouse.total) }}</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-purple-900">{{ formatCurrency(spouseAssetsProjectedTotal) }}</td>
              </tr>
            </template>

            <!-- Total Gross Assets -->
            <tr class="bg-blue-50">
              <td class="px-4 py-3 text-sm font-semibold text-blue-900">Total Gross Assets</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-blue-900">{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.gross_estate_value || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-blue-900">{{ formatCurrency(totalGrossAssetsProjected) }}</td>
            </tr>

            <!-- User Liabilities Section -->
            <template v-if="secondDeathData.liabilities_breakdown && secondDeathData.liabilities_breakdown.user">
              <!-- User Liabilities Header -->
              <tr class="bg-red-50">
                <td class="px-4 py-3 text-sm font-semibold text-red-900">{{ secondDeathData.liabilities_breakdown.user.name }}'s Liabilities</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-red-900" colspan="2"></td>
              </tr>

              <!-- User Mortgages -->
              <tr v-for="(mortgage, index) in secondDeathData.liabilities_breakdown.user.liabilities.mortgages" :key="'user-mortgage-' + index">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-red-500">Mortgage:</span> {{ mortgage.property_address }}
                  <span class="text-xs text-gray-500 ml-2">{{ mortgage.mortgage_type }}</span>
                  <span v-if="mortgage.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(mortgage.outstanding_balance) }}</td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(mortgage.projected_balance !== undefined && mortgage.projected_balance !== null ? mortgage.projected_balance : mortgage.outstanding_balance) }}</td>
              </tr>

              <!-- User Other Liabilities -->
              <tr v-for="(liability, index) in secondDeathData.liabilities_breakdown.user.liabilities.other_liabilities" :key="'user-liability-' + index">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-red-500">{{ liability.type }}:</span> {{ liability.institution }}
                  <span v-if="liability.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(liability.current_balance) }}</td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(liability.projected_balance !== undefined && liability.projected_balance !== null ? liability.projected_balance : liability.current_balance) }}</td>
              </tr>

              <!-- User Liabilities Subtotal (only if > 0) -->
              <tr v-if="secondDeathData.liabilities_breakdown.user.total > 0" class="bg-red-100">
                <td class="px-4 py-2 text-sm font-semibold text-red-900 pl-8">Subtotal</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-red-900">-{{ formatCurrency(secondDeathData.liabilities_breakdown.user.total) }}</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-red-900">-{{ formatCurrency(userLiabilitiesProjectedTotal) }}</td>
              </tr>
            </template>

            <!-- Spouse Liabilities Section -->
            <template v-if="secondDeathData.liabilities_breakdown && secondDeathData.liabilities_breakdown.spouse">
              <!-- Spouse Liabilities Header -->
              <tr class="bg-orange-50">
                <td class="px-4 py-3 text-sm font-semibold text-orange-900">{{ secondDeathData.liabilities_breakdown.spouse.name }}'s Liabilities</td>
                <td class="px-4 py-3 text-sm text-right font-semibold text-orange-900" colspan="2"></td>
              </tr>

              <!-- Spouse Mortgages -->
              <tr v-for="(mortgage, index) in secondDeathData.liabilities_breakdown.spouse.liabilities.mortgages" :key="'spouse-mortgage-' + index">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-red-500">Mortgage:</span> {{ mortgage.property_address }}
                  <span class="text-xs text-gray-500 ml-2">{{ mortgage.mortgage_type }}</span>
                  <span v-if="mortgage.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(mortgage.outstanding_balance) }}</td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(mortgage.projected_balance !== undefined && mortgage.projected_balance !== null ? mortgage.projected_balance : mortgage.outstanding_balance) }}</td>
              </tr>

              <!-- Spouse Other Liabilities -->
              <tr v-for="(liability, index) in secondDeathData.liabilities_breakdown.spouse.liabilities.other_liabilities" :key="'spouse-liability-' + index">
                <td class="px-4 py-2 text-sm text-gray-700 pl-8">
                  <span class="text-xs text-red-500">{{ liability.type }}:</span> {{ liability.institution }}
                  <span v-if="liability.is_joint" class="ml-2 text-xs text-amber-600 font-medium">(Joint - 50%)</span>
                </td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(liability.current_balance) }}</td>
                <td class="px-4 py-2 text-sm text-right text-red-600">-{{ formatCurrency(liability.projected_balance !== undefined && liability.projected_balance !== null ? liability.projected_balance : liability.current_balance) }}</td>
              </tr>

              <!-- Spouse Liabilities Subtotal (only if > 0) -->
              <tr v-if="secondDeathData.liabilities_breakdown.spouse.total > 0" class="bg-orange-100">
                <td class="px-4 py-2 text-sm font-semibold text-orange-900 pl-8">Subtotal</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-orange-900">-{{ formatCurrency(secondDeathData.liabilities_breakdown.spouse.total) }}</td>
                <td class="px-4 py-2 text-sm text-right font-semibold text-orange-900">-{{ formatCurrency(spouseLiabilitiesProjectedTotal) }}</td>
              </tr>
            </template>

            <!-- Total Liabilities -->
            <tr class="bg-red-50">
              <td class="px-4 py-3 text-sm font-semibold text-red-900">Less: Total Liabilities</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-red-900">-{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.liabilities || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-semibold text-red-900">-{{ formatCurrency(totalLiabilitiesProjected) }}</td>
            </tr>

            <!-- Net Estate -->
            <tr class="bg-purple-50">
              <td class="px-4 py-3 text-sm font-semibold text-purple-800">Net Estate</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-purple-800">{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.net_estate_value || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-purple-800">{{ formatCurrency(netEstateProjected) }}</td>
            </tr>

            <!-- NRB (Individual) -->
            <tr>
              <td class="px-4 py-3 text-sm text-gray-600">Less: NRB (Individual)</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.nrb || 325000) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation?.nrb || 325000) }}</td>
            </tr>

            <!-- NRB from Spouse -->
            <tr v-if="(secondDeathData.second_death_analysis.current_iht_calculation?.nrb_from_spouse || 0) > 0 || (secondDeathData.second_death_analysis.iht_calculation?.nrb_from_spouse || 0) > 0">
              <td class="px-4 py-3 text-sm text-gray-600">
                Less: NRB from Spouse
                <span v-if="!hasSpouseLinked" class="ml-2 text-xs text-amber-600">(Default - verify by linking spouse)</span>
              </td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.nrb_from_spouse || 325000) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation?.nrb_from_spouse || 325000) }}</td>
            </tr>

            <!-- RNRB (Individual) -->
            <tr v-if="secondDeathData.second_death_analysis.iht_calculation?.rnrb_eligible && ((secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_individual || 0) > 0 || (secondDeathData.second_death_analysis.iht_calculation?.rnrb_individual || 0) > 0)">
              <td class="px-4 py-3 text-sm text-gray-600">Less: RNRB (Individual)</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_individual || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation?.rnrb_individual || 0) }}</td>
            </tr>

            <!-- RNRB from Spouse -->
            <tr v-if="secondDeathData.second_death_analysis.iht_calculation?.rnrb_eligible && ((secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_from_spouse || 0) > 0 || (secondDeathData.second_death_analysis.iht_calculation?.rnrb_from_spouse || 0) > 0)">
              <td class="px-4 py-3 text-sm text-gray-600">
                Less: RNRB from Spouse
                <span v-if="!hasSpouseLinked" class="ml-2 text-xs text-amber-600">(Default - verify by linking spouse)</span>
              </td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_from_spouse || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(secondDeathData.second_death_analysis.iht_calculation?.rnrb_from_spouse || 0) }}</td>
            </tr>

            <!-- RNRB Taper Warning (Current) -->
            <tr v-if="secondDeathData.second_death_analysis.iht_calculation?.rnrb_eligible && secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_tapered">
              <td colspan="3" class="px-4 py-2 text-xs bg-orange-50">
                <span class="text-orange-700">
                  <strong>⚠ RNRB Tapered:</strong> Estate value {{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.net_estate_value || 0) }} exceeds {{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_taper_threshold || 2000000) }} threshold.
                  <span v-if="(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb || 0) === 0">RNRB completely tapered away (reduced by {{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_taper_amount || 0) }}).</span>
                  <span v-else>RNRB reduced by {{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.rnrb_taper_amount || 0) }} (£1 reduction for every £2 over threshold).</span>
                </span>
              </td>
            </tr>

            <!-- RNRB Not Available Message -->
            <tr v-if="!secondDeathData.second_death_analysis.iht_calculation?.rnrb_eligible">
              <td colspan="3" class="px-4 py-2 text-xs text-amber-700 bg-amber-50">
                <strong>Note:</strong> RNRB (Residence Nil Rate Band) not available - no main residence identified or property not left to direct descendants
              </td>
            </tr>

            <!-- Taxable Estate -->
            <tr class="bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">Taxable Estate</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.taxable_estate || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ formatCurrency(taxableEstateProjected) }}</td>
            </tr>

            <!-- IHT Liability -->
            <tr class="bg-red-50">
              <td class="px-4 py-3 text-sm font-semibold text-red-800">IHT Liability (40%)</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-red-800">{{ formatCurrency(secondDeathData.second_death_analysis.current_iht_calculation?.iht_liability || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-red-800">{{ formatCurrency(ihtLiabilityProjected) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- IHT Breakdown - Standard (Non-Married Users OR Married without spouse link) -->
    <div v-else-if="!loading && ihtData" class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        {{ isMarried ? 'IHT Calculation Breakdown (Spouse Exemption Applies)' : 'IHT Calculation Breakdown' }}
      </h3>
      <p v-if="projection" class="text-sm text-gray-600 mb-6">Comparison of IHT liability if death occurs now vs. at projected life expectancy (Age {{ projection.life_expectancy.death_age }})</p>

      <!-- Estate Calculation Table (if projection data available) -->
      <div v-if="projection" class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Item</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Now</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Death at Age {{ projection.life_expectancy.death_age }}</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Gross Estate -->
            <tr>
              <td class="px-4 py-3 text-sm text-gray-900">Total Estate Value</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">{{ formatCurrency(ihtData?.gross_estate_value || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">{{ formatCurrency(projection.at_death.assets || 0) }}</td>
            </tr>

            <!-- Liabilities -->
            <tr v-if="ihtData?.liabilities > 0 || projection.current.liabilities > 0">
              <td class="px-4 py-3 text-sm text-gray-600">Less: Liabilities</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(ihtData?.liabilities || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(projection.at_death.liabilities + projection.at_death.mortgages || 0) }}</td>
            </tr>

            <!-- Net Estate -->
            <tr class="bg-purple-50">
              <td class="px-4 py-3 text-sm font-semibold text-purple-800">Gross Estate</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-purple-800">{{ formatCurrency(ihtData?.net_estate_value || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-purple-800">{{ formatCurrency(projection.at_death.net_estate || 0) }}</td>
            </tr>

            <!-- NRB Individual -->
            <tr>
              <td class="px-4 py-3 text-sm text-gray-600">Less: NRB (Individual)</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(ihtData?.nrb || 325000) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(ihtData?.nrb || 325000) }}</td>
            </tr>

            <!-- NRB from Spouse -->
            <tr v-if="ihtData?.nrb_from_spouse > 0">
              <td class="px-4 py-3 text-sm text-gray-600">
                Less: NRB from Spouse
                <span v-if="!hasSpouseLinked" class="ml-2 text-xs text-amber-600">(Default)</span>
              </td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(ihtData.nrb_from_spouse) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency(ihtData.nrb_from_spouse) }}</td>
            </tr>

            <!-- RNRB -->
            <tr v-if="ihtData?.rnrb_eligible && (ihtData?.rnrb_individual > 0 || ihtData?.rnrb_from_spouse > 0)">
              <td class="px-4 py-3 text-sm text-gray-600">Less: RNRB (Total)</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency((ihtData?.rnrb_individual || 0) + (ihtData?.rnrb_from_spouse || 0)) }}</td>
              <td class="px-4 py-3 text-sm text-right text-gray-900">-{{ formatCurrency((ihtData?.rnrb_individual || 0) + (ihtData?.rnrb_from_spouse || 0)) }}</td>
            </tr>

            <!-- Taxable Estate -->
            <tr class="bg-gray-50">
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">Taxable Estate</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ formatCurrency(ihtData?.taxable_estate || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">{{ formatCurrency(Math.max(0, projection.at_death.net_estate - (ihtData?.nrb || 325000) - (ihtData?.nrb_from_spouse || 0) - ((ihtData?.rnrb_individual || 0) + (ihtData?.rnrb_from_spouse || 0)))) }}</td>
            </tr>

            <!-- IHT Liability -->
            <tr class="bg-red-50">
              <td class="px-4 py-3 text-sm font-semibold text-red-800">IHT Liability (40%)</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-red-800">{{ formatCurrency(ihtData?.estate_iht_liability || 0) }}</td>
              <td class="px-4 py-3 text-sm text-right font-bold text-red-800">{{ formatCurrency(projection.at_death.iht_liability || 0) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Fallback: Old list style if no projection data -->
      <div v-else class="space-y-3 mb-6">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Estate Calculation</h4>

        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Total Estate Value</span>
          <span class="text-sm font-medium text-gray-900">{{ formatCurrency(ihtData?.gross_estate_value || 0) }}</span>
        </div>

        <div v-if="ihtData?.liabilities > 0" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: Liabilities</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.liabilities || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-gray-200 bg-purple-50">
          <span class="text-sm font-semibold text-purple-800">Gross Estate</span>
          <span class="text-sm font-bold text-purple-800">{{ formatCurrency(ihtData?.net_estate_value || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: NRB (Individual)</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.nrb || 325000) }}</span>
        </div>

        <div v-if="ihtData?.nrb_from_spouse > 0" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">
            Less: NRB from Spouse
            <span v-if="!hasSpouseLinked" class="ml-2 text-xs text-amber-600">(Default - verify by linking spouse)</span>
          </span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData.nrb_from_spouse) }}</span>
        </div>

        <div v-if="ihtData?.rnrb_eligible && ihtData?.rnrb_individual > 0" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">Less: RNRB (Individual)</span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.rnrb_individual || 0) }}</span>
        </div>

        <div v-if="ihtData?.rnrb_eligible && ihtData?.rnrb_from_spouse > 0" class="flex justify-between items-center py-2 border-b border-gray-200">
          <span class="text-sm text-gray-600">
            Less: RNRB from Spouse
            <span v-if="!hasSpouseLinked" class="ml-2 text-xs text-amber-600">(Default - verify by linking spouse)</span>
          </span>
          <span class="text-sm font-medium text-gray-900">-{{ formatCurrency(ihtData?.rnrb_from_spouse || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-3 bg-gray-50 rounded">
          <span class="text-base font-semibold text-gray-900">Taxable Estate</span>
          <span class="text-base font-bold text-gray-900">{{ formatCurrency(ihtData?.taxable_estate || 0) }}</span>
        </div>

        <div class="flex justify-between items-center py-3 bg-red-50 rounded">
          <span class="text-base font-semibold text-red-800">IHT Liability ({{ formatPercent(ihtData?.iht_rate || 0.4) }})</span>
          <span class="text-base font-bold text-red-800">{{ formatCurrency(ihtData?.estate_iht_liability || 0) }}</span>
        </div>
      </div>

      <!-- Gift Calculation -->
      <div v-if="hasGifts" class="space-y-3 pt-6 border-t border-gray-300">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Gift Liability (7-Year Rule with Taper Relief)</h4>

        <!-- PET Gifts -->
        <div v-if="hasPETGifts" class="mb-4">
          <p class="text-xs font-medium text-gray-600 mb-2">Potentially Exempt Transfers (PETs)</p>
          <div v-for="gift in petGifts" :key="gift.gift_id" class="mb-3 pl-4 py-2 border-l-2 border-amber-400 bg-amber-50">
            <div class="flex justify-between items-center mb-1">
              <div class="flex-1">
                <span class="text-sm font-medium text-gray-800">{{ gift.recipient }}</span>
                <span class="ml-2 text-xs text-gray-500">({{ formatDate(gift.gift_date) }})</span>
              </div>
              <span class="text-sm font-bold text-amber-800">{{ formatCurrency(gift.tax_liability) }}</span>
            </div>
            <div class="text-xs text-gray-600 space-y-1 mt-2">
              <div class="flex justify-between">
                <span>Gift value:</span>
                <span class="font-medium">{{ formatCurrency(gift.gift_value) }}</span>
              </div>
              <div class="flex justify-between text-green-700">
                <span>Less: NRB covered</span>
                <span class="font-medium">-{{ formatCurrency(gift.nrb_covered) }}</span>
              </div>
              <div class="flex justify-between border-t border-amber-200 pt-1">
                <span>Taxable amount:</span>
                <span class="font-medium">{{ formatCurrency(gift.taxable_amount) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Taper relief rate ({{ gift.years_ago }} years):</span>
                <span class="font-medium">{{ formatPercent(gift.taper_rate) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CLT Gifts -->
        <div v-if="hasCLTGifts" class="mb-4">
          <p class="text-xs font-medium text-gray-600 mb-2">Chargeable Lifetime Transfers (CLTs)</p>
          <div v-for="clt in cltGifts" :key="clt.gift_id" class="flex justify-between items-center py-2 pl-4 border-l-2 border-blue-400">
            <div class="flex-1">
              <span class="text-sm text-gray-700">{{ clt.recipient }} ({{ formatDate(clt.gift_date) }})</span>
              <span class="ml-2 text-xs text-gray-500">{{ clt.years_ago }} years ago</span>
            </div>
            <span class="text-sm font-medium text-blue-700">{{ formatCurrency(clt.tax_liability) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center py-3 bg-amber-50 rounded">
          <span class="text-base font-semibold text-amber-800">Total Gift IHT Liability</span>
          <span class="text-base font-bold text-amber-800">{{ formatCurrency(ihtData?.gift_iht_liability || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- Dual Gifting Timeline (Married Users Only) -->
    <DualGiftingTimeline
      v-if="isMarried && secondDeathData?.user_gifting_timeline"
      :user-timeline="secondDeathData.user_gifting_timeline"
      :spouse-timeline="secondDeathData.spouse_gifting_timeline"
      :data-sharing-enabled="secondDeathData.data_sharing_enabled"
      class="mb-8"
    />

    <!-- Life Cover Recommendations (Married Users with Second Death Data) -->
    <LifeCoverRecommendations
      v-if="isMarried && secondDeathData?.life_cover_recommendations"
      :recommendations="secondDeathData.life_cover_recommendations"
      :iht-liability="secondDeathData.effective_iht_liability || secondDeathData.second_death_analysis?.iht_calculation?.iht_liability || 0"
      class="mb-8"
    />

    <!-- Standard Recommendations (Non-Married Users OR Married without full second death data) -->
    <div v-if="!secondDeathData?.mitigation_strategies && ihtData?.iht_liability > 0" class="bg-red-50 border-l-4 border-red-500 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">IHT Mitigation Strategies</h3>
          <div class="mt-2 text-sm text-red-700">
            <p class="font-semibold mb-2">
              Your estate has a potential IHT liability of {{ formatCurrency(ihtData?.iht_liability || 0) }}. Consider these strategies:
            </p>
            <ul class="list-disc list-inside space-y-1">
              <li>Regular gifting using PET and annual exemptions (£3,000/year)</li>
              <li>Charitable giving (can reduce IHT rate from 40% to 36% if ≥10% to charity)</li>
              <li>Trust planning to remove assets from your estate</li>
              <li>Life insurance policies written in trust to cover IHT liability</li>
              <li v-if="!ihtData?.rnrb || ihtData.rnrb === 0">Consider leaving your main residence to direct descendants to claim RNRB (up to £175,000)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="ihtData && ihtData.iht_liability === 0" class="bg-green-50 border-l-4 border-green-500 p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-green-400"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-green-800">No IHT Liability</h3>
          <div class="mt-2 text-sm text-green-700">
            <p class="mb-2">
              Good news! Your estate is currently below the IHT threshold with {{ formatCurrency(ihtData?.total_allowance || 500000) }} in allowances available.
            </p>
            <p>
              Continue to monitor your estate value as asset prices change. Review your IHT position annually or after significant life events.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Trust Planning Summary -->
    <div v-if="ihtData?.trust_details && ihtData.trust_details.length > 0" class="bg-white shadow rounded-lg p-6 mt-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Trust Planning Summary</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-purple-50 rounded-lg p-4">
          <p class="text-xs text-purple-600 font-medium">Total Trust Value</p>
          <p class="text-2xl font-bold text-purple-900 mt-1">{{ formatCurrency(totalTrustValue) }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4">
          <p class="text-xs text-green-600 font-medium">Value Outside Estate</p>
          <p class="text-2xl font-bold text-green-900 mt-1">{{ formatCurrency(trustValueOutsideEstate) }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4">
          <p class="text-xs text-blue-600 font-medium">IHT Efficiency</p>
          <p class="text-2xl font-bold text-blue-900 mt-1">{{ trustEfficiencyPercent }}%</p>
        </div>
      </div>

      <div class="space-y-3">
        <div v-for="trust in ihtData.trust_details" :key="trust.trust_id" class="border border-gray-200 rounded-lg p-3">
          <div class="flex justify-between items-start mb-2">
            <div>
              <h4 class="text-sm font-semibold text-gray-900">{{ trust.trust_name }}</h4>
              <p class="text-xs text-gray-500">{{ getTrustTypeName(trust.trust_type) }}</p>
            </div>
            <span class="text-sm font-medium text-gray-900">{{ formatCurrency(trust.current_value) }}</span>
          </div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-500">Value in Estate:</span>
              <span class="font-medium ml-1" :class="trust.iht_value > 0 ? 'text-red-600' : 'text-green-600'">
                {{ formatCurrency(trust.iht_value) }}
              </span>
            </div>
            <div>
              <span class="text-gray-500">Outside Estate:</span>
              <span class="font-medium text-green-600 ml-1">{{ formatCurrency(trust.current_value - trust.iht_value) }}</span>
            </div>
          </div>

          <div v-if="trust.iht_value > 0" class="mt-2 pt-2 border-t border-gray-200">
            <p class="text-xs text-amber-700">
              <strong>Note:</strong> {{ getTrustIHTExplanation(trust.trust_type) }}
            </p>
          </div>
          <div v-else class="mt-2 pt-2 border-t border-gray-200">
            <p class="text-xs text-green-700">
              ✓ This trust's value is completely outside your estate for IHT purposes
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters, mapActions } from 'vuex';
import SpouseExemptionNotice from './SpouseExemptionNotice.vue';
import MissingDataAlert from './MissingDataAlert.vue';
import DualGiftingTimeline from './DualGiftingTimeline.vue';
import LifeCoverRecommendations from './LifeCoverRecommendations.vue';
import estateService from '../../services/estateService';

export default {
  name: 'IHTPlanning',

  components: {
    SpouseExemptionNotice,
    MissingDataAlert,
    DualGiftingTimeline,
    LifeCoverRecommendations,
  },

  data() {
    return {
      ihtData: null,
      secondDeathData: null,
      projection: null,
      userGender: 'male',
      isMarried: false,
      hasSpouse: false,
      showSpouseExemptionNotice: false,
      loading: false,
      error: null,
    };
  },

  computed: {
    ...mapState('estate', ['analysis', 'gifts']),
    ...mapGetters('estate', ['netWorthValue', 'ihtLiability', 'ihtExemptAssets']),

    hasSpouseLinked() {
      return this.hasSpouse;
    },

    formattedIHTLiability() {
      return this.formatCurrency(this.ihtData?.iht_liability || 0);
    },

    hasGifts() {
      return this.ihtData?.gifting_details &&
             (this.ihtData.gifting_details.pet_liability?.gift_count > 0 ||
              this.ihtData.gifting_details.clt_liability?.clt_count > 0);
    },

    hasTrusts() {
      return this.ihtData?.trust_details && this.ihtData.trust_details.length > 0;
    },

    activeTrustDetails() {
      if (!this.ihtData?.trust_details) return [];
      return this.ihtData.trust_details.filter(t => t.iht_value > 0);
    },

    totalTrustValue() {
      if (!this.ihtData?.trust_details) return 0;
      return this.ihtData.trust_details.reduce((sum, t) => {
        const value = parseFloat(t.current_value) || 0;
        return sum + value;
      }, 0);
    },

    trustValueOutsideEstate() {
      if (!this.ihtData?.trust_details) return 0;
      return this.ihtData.trust_details.reduce((sum, t) => {
        const currentValue = parseFloat(t.current_value) || 0;
        const ihtValue = parseFloat(t.iht_value) || 0;
        return sum + (currentValue - ihtValue);
      }, 0);
    },

    trustEfficiencyPercent() {
      const total = this.totalTrustValue;
      if (!total || total === 0) return 0;
      const outsideEstate = this.trustValueOutsideEstate;
      return Math.round((outsideEstate / total) * 100);
    },

    hasPETGifts() {
      return this.ihtData?.gifting_details?.pet_liability?.gift_count > 0;
    },

    hasCLTGifts() {
      return this.ihtData?.gifting_details?.clt_liability?.clt_count > 0;
    },

    petGifts() {
      return this.ihtData?.gifting_details?.pet_liability?.gifts || [];
    },

    cltGifts() {
      return this.ihtData?.gifting_details?.clt_liability?.clts || [];
    },

    // Projected subtotals for second death breakdown
    userAssetsProjectedTotal() {
      if (!this.secondDeathData?.assets_breakdown?.user?.assets) return 0;
      const assets = this.secondDeathData.assets_breakdown.user.assets;
      let total = 0;

      // Sum all asset types
      Object.keys(assets).forEach(assetType => {
        if (Array.isArray(assets[assetType])) {
          assets[assetType].forEach(asset => {
            total += (asset.projected_value || asset.value || 0);
          });
        }
      });

      return total;
    },

    spouseAssetsProjectedTotal() {
      if (!this.secondDeathData?.assets_breakdown?.spouse?.assets) return 0;
      const assets = this.secondDeathData.assets_breakdown.spouse.assets;
      let total = 0;

      // Sum all asset types
      Object.keys(assets).forEach(assetType => {
        if (Array.isArray(assets[assetType])) {
          assets[assetType].forEach(asset => {
            total += (asset.projected_value || asset.value || 0);
          });
        }
      });

      return total;
    },

    userLiabilitiesProjectedTotal() {
      if (!this.secondDeathData?.liabilities_breakdown?.user?.liabilities) return 0;
      const liabilities = this.secondDeathData.liabilities_breakdown.user.liabilities;
      let total = 0;

      // Sum mortgages (use projected_balance, which may be 0 if paid off)
      if (Array.isArray(liabilities.mortgages)) {
        liabilities.mortgages.forEach(mortgage => {
          const value = mortgage.projected_balance !== undefined && mortgage.projected_balance !== null
            ? mortgage.projected_balance
            : (mortgage.outstanding_balance || 0);
          total += value;
        });
      }

      // Sum other liabilities (use projected_balance, which equals current_balance)
      if (Array.isArray(liabilities.other_liabilities)) {
        liabilities.other_liabilities.forEach(liability => {
          const value = liability.projected_balance !== undefined && liability.projected_balance !== null
            ? liability.projected_balance
            : (liability.current_balance || 0);
          total += value;
        });
      }

      return total;
    },

    spouseLiabilitiesProjectedTotal() {
      if (!this.secondDeathData?.liabilities_breakdown?.spouse?.liabilities) return 0;
      const liabilities = this.secondDeathData.liabilities_breakdown.spouse.liabilities;
      let total = 0;

      // Sum mortgages (use projected_balance, which may be 0 if paid off)
      if (Array.isArray(liabilities.mortgages)) {
        liabilities.mortgages.forEach(mortgage => {
          const value = mortgage.projected_balance !== undefined && mortgage.projected_balance !== null
            ? mortgage.projected_balance
            : (mortgage.outstanding_balance || 0);
          total += value;
        });
      }

      // Sum other liabilities (use projected_balance, which equals current_balance)
      if (Array.isArray(liabilities.other_liabilities)) {
        liabilities.other_liabilities.forEach(liability => {
          const value = liability.projected_balance !== undefined && liability.projected_balance !== null
            ? liability.projected_balance
            : (liability.current_balance || 0);
          total += value;
        });
      }

      return total;
    },

    // Total Gross Assets projected (sum of user + spouse subtotals)
    totalGrossAssetsProjected() {
      return this.userAssetsProjectedTotal + this.spouseAssetsProjectedTotal;
    },

    // Total Liabilities projected (sum of user + spouse subtotals)
    totalLiabilitiesProjected() {
      return this.userLiabilitiesProjectedTotal + this.spouseLiabilitiesProjectedTotal;
    },

    // Net Estate projected (Total Gross Assets - Total Liabilities)
    netEstateProjected() {
      return this.totalGrossAssetsProjected - this.totalLiabilitiesProjected;
    },

    // Taxable Estate projected (Net Estate - NRB - RNRB)
    taxableEstateProjected() {
      const totalNRB = this.secondDeathData?.second_death_analysis?.iht_calculation?.total_nrb || 650000;
      const rnrb = this.secondDeathData?.second_death_analysis?.iht_calculation?.rnrb || 0;
      return Math.max(0, this.netEstateProjected - totalNRB - rnrb);
    },

    // IHT Liability projected (40% of Taxable Estate)
    ihtLiabilityProjected() {
      return this.taxableEstateProjected * 0.40;
    },
  },

  mounted() {
    this.checkUserMaritalStatus();
    this.loadIHTCalculation();
  },

  watch: {
    '$route'() {
      // Reload when navigating back to this tab
      this.loadIHTCalculation();
    },
  },

  methods: {
    ...mapActions('estate', ['calculateIHT', 'calculateSecondDeathIHTPlanning']),

    checkUserMaritalStatus() {
      const user = this.$store.state.auth?.user;
      if (user) {
        this.isMarried = user.marital_status === 'married';
        this.hasSpouse = user.spouse_id !== null;
        this.userGender = user.gender || 'male';
      }
    },

    navigateToGiftingTab() {
      // Emit event to parent EstateDashboard to switch to Gifting tab
      this.$emit('switch-tab', 'gifting');
    },

    async loadIHTCalculation() {
      this.loading = true;
      this.error = null;

      try {
        if (this.isMarried) {
          // Married users: Call second death endpoint (backend handles missing spouse data gracefully)
          const response = await this.calculateSecondDeathIHTPlanning();

          if (response.success) {
            this.secondDeathData = response;
            this.showSpouseExemptionNotice = response.show_spouse_exemption_notice;

            // Extract projection data from response
            if (response.projection) {
              this.projection = response.projection;
            }

            // Handle cases where spouse not linked or missing data - use returned calculation data
            if (response.user_iht_calculation) {
              this.ihtData = response.user_iht_calculation;
            }
          }
        } else {
          // Non-married users: Standard IHT calculation
          const response = await this.calculateIHT();
          this.ihtData = response.data;
          this.projection = response.projection;
        }
      } catch (error) {
        console.error('❌ Failed to load IHT calculation:', error);
        this.error = error.message || 'Failed to calculate IHT liability';
      } finally {
        this.loading = false;
      }
    },

    getMissingDataMessage() {
      if (!this.secondDeathData?.missing_data) return '';

      const missingItems = this.secondDeathData.missing_data;
      if (missingItems.includes('spouse_account')) {
        return 'Link your spouse account to enable full second death IHT planning.';
      }
      return 'Some information is required to complete the second death IHT calculation.';
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

    formatPercent(value) {
      return `${(value * 100).toFixed(0)}%`;
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
      });
    },

    getTaperReliefRate(yearsAgo) {
      if (yearsAgo < 3) return 40;
      if (yearsAgo < 4) return 32;
      if (yearsAgo < 5) return 24;
      if (yearsAgo < 6) return 16;
      if (yearsAgo < 7) return 8;
      return 0;
    },

    getTrustTypeName(type) {
      const names = {
        bare: 'Bare Trust',
        interest_in_possession: 'Interest in Possession',
        discretionary: 'Discretionary',
        accumulation_maintenance: 'A&M Trust',
        life_insurance: 'Life Insurance',
        discounted_gift: 'Discounted Gift',
        loan: 'Loan Trust',
        mixed: 'Mixed',
        settlor_interested: 'Settlor-Interested',
      };
      return names[type] || type;
    },

    getTrustIHTExplanation(type) {
      const explanations = {
        discounted_gift: 'For a Discounted Gift Trust, the retained income value (discount) counts in your estate.',
        loan: 'For a Loan Trust, the outstanding loan balance counts in your estate. Growth is outside.',
        interest_in_possession: 'For an Interest in Possession Trust, the full value counts in the life tenant\'s estate.',
        settlor_interested: 'For a Settlor-Interested Trust, the full value remains in your estate (reservation of benefit).',
      };
      return explanations[type] || 'This trust type has specific IHT treatment rules.';
    },
  },
};
</script>
